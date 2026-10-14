<?php

namespace App\Http\Controllers\Api\Th;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ThDispositivo;
use App\Models\ThEmpleado;
use App\Models\ThTimbrada;
use App\Models\ThPlanificacionHorario;
use App\Models\ThAsistenciaDiaria;
use App\Models\ThEstadoAsistencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HikvisionWebhookController extends Controller
{
    /**
     * Recibe eventos de timbrada en tiempo real desde terminales Hikvision (DS-K1T323MBFWX-E1).
     * Hikvision envía un payload multipart/form-data o application/json con el evento 'AccessControllerEvent'.
     */
    public function receiveEvent(Request $request)
    {
        try {
            $rawContent = $request->getContent();
            $eventData = null;
            $photoFile = null;

            // 1. Detección de formato multipart/form-data (Hikvision suele enviar 'AccessControllerEvent' o 'event_log' JSON)
            if ($request->has('AccessControllerEvent')) {
                $rawJson = $request->input('AccessControllerEvent');
                $eventData = is_string($rawJson) ? json_decode($rawJson, true) : $rawJson;
            } elseif ($request->has('event_log')) {
                $rawJson = $request->input('event_log');
                $eventData = is_string($rawJson) ? json_decode($rawJson, true) : $rawJson;
            } elseif ($request->isJson()) {
                $eventData = $request->json()->all();
            } else {
                // Parseo manual si viene con boundary multipart crudo
                if (preg_match('/\{.*"AccessControllerEvent".*\}/s', $rawContent, $matches)) {
                    $eventData = json_decode($matches[0], true);
                }
            }

            if (!$eventData) {
                // Si no se pudo parsear como evento de acceso específico, registrar para diagnóstico
                Log::channel('single')->info('Hikvision webhook payload sin evento de acceso reconocido', [
                    'ip' => $request->ip(),
                    'inputs' => array_keys($request->all()),
                    'sample' => substr($rawContent, 0, 500)
                ]);

                return response()->json(['status' => 'ignored', 'message' => 'No access event found'], 200);
            }

            // 2. Extraer datos del evento de acceso
            $acsEvent = $eventData['AccessControllerEvent'] ?? $eventData;
            $employeeNo = $acsEvent['employeeNoString'] ?? ($acsEvent['employeeNo'] ?? ($eventData['employeeNoString'] ?? ($eventData['employeeNo'] ?? null)));
            $eventTimeStr = $acsEvent['time'] ?? ($eventData['dateTime'] ?? ($eventData['time'] ?? now()->toIso8601String()));
            $minorType = $acsEvent['minor'] ?? ($acsEvent['subEventType'] ?? ($eventData['minor'] ?? null));
            $cardNo = $acsEvent['cardNo'] ?? ($eventData['cardNo'] ?? null);
            $deviceName = $acsEvent['deviceName'] ?? ($eventData['deviceName'] ?? ($eventData['name'] ?? null));
            $serialNo = $eventData['shortSerialNumber'] ?? ($eventData['serialNo'] ?? ($acsEvent['serialNo'] ?? null));

            // Fecha y hora del evento
            try {
                $eventDateTime = Carbon::parse($eventTimeStr);
            } catch (\Exception $e) {
                $eventDateTime = now();
            }

            // 3. Identificación y Auto-Actualización Inteligente de Dispositivo
            // NUNCA crear duplicados no autorizados. Si el administrador ya registró el equipo,
            // vinculamos y actualizamos automáticamente sus parámetros reales (serial corto, modelo, etc.)
            $deviceIp = $request->ip();
            $dispositivo = null;

            // A. Buscar por coincidencia de Serial (exacto, contenido o que contenga el serial recibido)
            if ($serialNo) {
                $cleanSerial = trim((string)$serialNo);
                $dispositivo = ThDispositivo::where('serial_number', $cleanSerial)
                    ->orWhere('serial_number', 'LIKE', "%{$cleanSerial}%")
                    ->orWhereRaw("? LIKE CONCAT('%', serial_number, '%')", [$cleanSerial])
                    ->first();
            }

            // B. Si no coincidió por serial, buscar por IP WAN o IP Local
            if (!$dispositivo && $deviceIp) {
                $dispositivo = ThDispositivo::where('ip', $deviceIp)
                    ->orWhere('ip_local', $deviceIp)
                    ->first();
            }

            // C. Si viene con deviceName y coincide con el nombre dado por el admin
            if (!$dispositivo && $deviceName) {
                $dispositivo = ThDispositivo::where('nombre', 'LIKE', trim($deviceName) . '%')->first();
            }

            // D. Fallback seguro: Si no se encuentra ninguno nuevo pero solo hay dispositivos administrados,
            // asociar al dispositivo que tenga el modelo o estado activo para no perder la marcación
            if (!$dispositivo) {
                // Si el serial contiene parte del serial_number de algún equipo existente
                $todos = ThDispositivo::all();
                foreach ($todos as $d) {
                    if (!empty($d->serial_number) && !empty($serialNo)) {
                        if (str_contains($d->serial_number, $serialNo) || str_contains($serialNo, $d->serial_number)) {
                            $dispositivo = $d;
                            break;
                        }
                    }
                }
            }

            // E. Si después de todo aún no existe, tomar el primer dispositivo activo en lugar de crear basura en DB
            if (!$dispositivo) {
                $dispositivo = ThDispositivo::where('estado', 'activo')->orderBy('id', 'asc')->first();
            }

            // F. Actualizar automáticamente los parámetros del dispositivo identificado
            if ($dispositivo) {
                $updates = [];
                // Si el dispositivo no tenía serial o tenía un serial largo de firmware, sincronizar el serial real reportado por el hardware
                if ($serialNo && ($dispositivo->serial_number !== $serialNo || empty($dispositivo->serial_number))) {
                    $updates['serial_number'] = $serialNo;
                }
                // Si viene modelo o nombre del hardware y no lo teníamos
                if (!empty($deviceName) && empty($dispositivo->modelo)) {
                    $updates['modelo'] = $deviceName;
                }
                // Actualizar último latido si pasaron más de 30 segundos
                if (!$dispositivo->ultimo_contacto_at || $dispositivo->ultimo_contacto_at->diffInSeconds(now()) > 30) {
                    $updates['ultimo_contacto_at'] = now();
                }

                if (!empty($updates)) {
                    $dispositivo->update($updates);
                }
            } else {
                Log::warning("Hikvision Webhook: Evento recibido pero no hay ningún terminal registrado en el sistema.", [
                    'ip' => $deviceIp,
                    'serial' => $serialNo,
                    'name' => $deviceName
                ]);
                return response()->json(['status' => 'ignored', 'message' => 'No active device configured'], 200);
            }

            // Si el evento no tiene número de empleado/tarjeta, es solo un keep-alive/latido del equipo
            if (!$employeeNo && !$cardNo) {
                return response()->json(['status' => 'success', 'message' => 'Heartbeat received'], 200);
            }

            $searchCode = trim((string)($employeeNo ?: $cardNo));
            Log::info("✅ TIMBRADA RECIBIDA DE HIKVISION: Empleado {$searchCode} en {$dispositivo->nombre}");

            // 4. Buscar al empleado en th_empleados tolerando pérdida de cero inicial
            $empleado = ThEmpleado::findByCodigoOCedula($searchCode);

            // 5. Guardar foto si el dispositivo adjuntó imagen
            $photoPath = null;
            if ($request->hasFile('picture') || $request->hasFile('Picture')) {
                $file = $request->file('picture') ?: $request->file('Picture');
                if ($file->isValid()) {
                    $fileName = 'th_' . time() . '_' . ($empleado ? $empleado->identificacion : 'anon') . '.jpg';
                    $photoPath = $file->storeAs('public/th_marcaciones/' . $eventDateTime->format('Y-m'), $fileName);
                }
            }

            // 6. Determinar método de verificación por código minor o verifyMode de Hikvision
            $verifyMode = strtolower((string)($acsEvent['currentVerifyMode'] ?? ($acsEvent['verifyMode'] ?? ($eventData['currentVerifyMode'] ?? ''))));
            $minorStr = strtolower((string)$minorType);

            // Terminales DS-K1T323MBFWX toman foto de verificación tanto en Huella como en Rostro.
            // Por lo tanto, se prioriza la detección de la huella, tarjeta o PIN.
            if (
                str_contains($verifyMode, 'finger') || 
                in_array($minorType, [2, 38, 39, 76, 77, 85, 86, 0x2, 0x26, 0x27, 0x4c, 0x4d, 0x55, 0x56]) ||
                in_array($minorStr, ['2', '38', '39', '76', '77', '85', '86', '0x2', '0x26', '0x27', '0x4c', '0x4d', '0x55', '0x56'])
            ) {
                $metodo = 'huella';
            } elseif (
                str_contains($verifyMode, 'face') || 
                in_array($minorType, [75, 87, 0x4b, 0x57]) ||
                in_array($minorStr, ['75', '87', '0x4b', '0x57'])
            ) {
                $metodo = 'facial';
            } elseif (
                str_contains($verifyMode, 'card') || 
                in_array($minorType, [1, 3, 0x1, 0x3]) ||
                in_array($minorStr, ['1', '3', '0x1', '0x3'])
            ) {
                $metodo = 'tarjeta';
            } elseif (
                str_contains($verifyMode, 'password') || 
                str_contains($verifyMode, 'pin') || 
                in_array($minorType, [71, 0x47]) ||
                in_array($minorStr, ['71', '0x47'])
            ) {
                $metodo = 'pin';
            } else {
                // Por defecto para este tipo de terminal, o si no se especificó otro
                $metodo = 'huella';
            }

            // 7. Registrar marcación cruda en th_timbradas
            $timbrada = ThTimbrada::create([
                'empleado_id' => $empleado ? $empleado->identificacion : null,
                'dispositivo_id' => $dispositivo->id,
                'codigo_empleado' => $searchCode,
                'fecha_hora' => $eventDateTime,
                'fecha' => $eventDateTime->toDateString(),
                'tipo_marcacion' => 'auto',
                'metodo_verificacion' => $metodo,
                'foto_path' => $photoPath,
                'raw_payload' => $eventData,
                'origen' => 'webhook',
            ]);

            // 8. Consolidar asistencia diaria para el empleado si está registrado
            if ($empleado) {
                self::consolidarAsistenciaDiaria($empleado, $eventDateTime->toDateString());
            }

            return response()->json([
                'status' => 'success',
                'timbrada_id' => $timbrada->id,
                'empleado' => $empleado ? $empleado->nombre_completo : null,
            ], 200);

        } catch (\Exception $e) {
            if (app()->environment('testing')) {
                throw $e;
            }
            Log::error('Error procesando webhook de Hikvision: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Responder 200 para que Hikvision no sature de reintentos continuos si hay un error puntual de parsing
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }

    /**
     * Consolida las timbradas de un empleado en un día específico:
     * Calcula primera entrada, descansos, última salida, atrasos y horas trabajadas.
     */
    public static function consolidarAsistenciaDiaria(ThEmpleado $empleado, string $fecha)
    {
        $asistencia = ThAsistenciaDiaria::where('empleado_id', $empleado->identificacion)
            ->whereDate('fecha', $fecha)
            ->first();

        if (!$asistencia) {
            $asistencia = new ThAsistenciaDiaria();
            $asistencia->empleado_id = $empleado->identificacion;
            $asistencia->fecha = $fecha;
        }

        if ($asistencia->bloqueado_para_nomina) {
            return $asistencia;
        }

        // Obtener todas las timbradas del empleado en ese día ordenadas
        $timbradas = ThTimbrada::where('empleado_id', $empleado->identificacion)
            ->whereDate('fecha', $fecha)
            ->orderBy('fecha_hora', 'asc')
            ->get();

        if ($timbradas->isEmpty()) {
            return $asistencia;
        }

        $primera = $timbradas->first();
        $ultima = $timbradas->last();

        $asistencia->primera_entrada = Carbon::parse($primera->fecha_hora)->toTimeString();
        if ($timbradas->count() > 1) {
            $asistencia->ultima_salida = Carbon::parse($ultima->fecha_hora)->toTimeString();
        }

        // Buscar si tiene turno planificado para ese día
        $plan = ThPlanificacionHorario::where('empleado_id', $empleado->identificacion)
            ->whereDate('fecha', $fecha)
            ->with('horario')
            ->first();

        $horario = $plan ? $plan->horario : null;
        $esDiaLibre = $plan ? $plan->es_dia_libre : false;

        if ($horario) {
            $asistencia->horario_planificado_id = $horario->id;
            
            // Cálculo de atraso respecto a la hora de entrada del horario
            $horaEntradaEsperada = Carbon::parse($fecha . ' ' . $horario->hora_entrada);
            $horaEntradaReal = Carbon::parse($primera->fecha_hora);

            $minutosDiferencia = $horaEntradaReal->diffInMinutes($horaEntradaEsperada, false);
            
            // Si llegó después de la hora esperada + tolerancia
            if ($minutosDiferencia < 0) {
                $minutosAtraso = abs($minutosDiferencia);
                if ($minutosAtraso > $horario->tolerancia_entrada_min) {
                    $asistencia->minutos_atraso = $minutosAtraso;
                    $asistencia->estado_asistencia_id = ThEstadoAsistencia::where('codigo', 'ATRASO')->value('id');
                } else {
                    $asistencia->minutos_atraso = 0;
                    $asistencia->estado_asistencia_id = ThEstadoAsistencia::where('codigo', 'ASISTIO')->value('id');
                }
            } else {
                $asistencia->minutos_atraso = 0;
                $asistencia->estado_asistencia_id = ThEstadoAsistencia::where('codigo', 'ASISTIO')->value('id');
            }
        } elseif ($esDiaLibre) {
            $asistencia->estado_asistencia_id = ThEstadoAsistencia::where('codigo', 'DESCANSO_LIBRE')->value('id');
        } else {
            // Asistencia sin horario estricto planificado
            $asistencia->estado_asistencia_id = ThEstadoAsistencia::where('codigo', 'ASISTIO')->value('id');
        }

        // Cálculo de horas trabajadas si hay al menos entrada y salida
        if ($asistencia->primera_entrada && $asistencia->ultima_salida) {
            $inicio = Carbon::parse($fecha . ' ' . $asistencia->primera_entrada);
            $fin = Carbon::parse($fecha . ' ' . $asistencia->ultima_salida);
            if ($fin->greaterThan($inicio)) {
                $totalMinutos = $inicio->diffInMinutes($fin);
                // Si el horario tiene tiempo de almuerzo contemplado y laboró más de 5 horas
                $tiempoAlmuerzo = ($horario && $totalMinutos > 300) ? $horario->tiempo_almuerzo_min : 0;
                $minutosEfectivos = max(0, $totalMinutos - $tiempoAlmuerzo);
                $asistencia->horas_trabajadas = round($minutosEfectivos / 60, 2);
            }
        }

        $asistencia->save();
        return $asistencia;
    }
}
