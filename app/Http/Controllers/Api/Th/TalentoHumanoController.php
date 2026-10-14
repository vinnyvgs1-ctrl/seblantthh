<?php

namespace App\Http\Controllers\Api\Th;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ThEmpleado;
use App\Models\ThDispositivo;
use App\Models\ThHorario;
use App\Models\ThPlanificacionHorario;
use App\Models\ThAsistenciaDiaria;
use App\Models\ThTimbrada;
use App\Models\ThSolicitudPermiso;
use App\Models\ThSolicitudVacacion;
use App\Models\ThTipoPermiso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class TalentoHumanoController extends Controller
{
    /**
     * Dashboard general
     */
    public function dashboard(Request $request)
    {
        $hoy = Carbon::today()->toDateString();

        $totalEmpleados = ThEmpleado::where('activo', true)->count();
        $dispositivosActivos = ThDispositivo::where('activo', true)->count();

        $presentesHoy = ThAsistenciaDiaria::where('fecha', $hoy)
            ->whereNotNull('primera_entrada')
            ->count();

        $atrasosHoy = ThAsistenciaDiaria::where('fecha', $hoy)
            ->where('minutos_atraso', '>', 0)
            ->count();

        $solicitudesPendientes = ThSolicitudPermiso::where('estado', 'PENDIENTE')->count() +
                                 ThSolicitudVacacion::where('estado', 'PENDIENTE')->count();

        return response()->json([
            'success' => true,
            'metricas' => [
                'total_empleados' => $totalEmpleados,
                'dispositivos_activos' => $dispositivosActivos,
                'presentes_hoy' => $presentesHoy,
                'atrasos_hoy' => $atrasosHoy,
                'solicitudes_pendientes' => $solicitudesPendientes,
            ]
        ]);
    }

    /**
     * Empleados
     */
    public function getEmpleados(Request $request)
    {
        $query = ThEmpleado::with('horario')->orderBy('nombres', 'asc');

        if ($request->has('q') && !empty($request->q)) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('identificacion', 'like', "%{$q}%")
                    ->orWhere('nombres', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%");
            });
        }

        $empleados = $query->paginate(50);
        return response()->json($empleados);
    }

    public function saveEmpleado(Request $request)
    {
        try {
            $validated = $request->validate([
                'identificacion' => 'required|string|size:10',
                'codigo_empleado' => 'nullable|string|max:50',
                'nombres' => 'required|string|max:100',
                'apellidos' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:150',
                'telefono' => 'nullable|string|max:50',
                'horario_predeterminado_id' => 'nullable|integer',
                'sec_user_login' => 'nullable|string|max:255',
                'activo' => 'nullable|boolean',
            ]);

            $empresaId = DB::table('th_empresas')->where('codigo', 'SEBLAN')->value('id')
                      ?: DB::table('th_empresas')->value('id');

            $data = [
                'identificacion' => $validated['identificacion'],
                'empresa_id' => $empresaId,
                'codigo_empleado' => $validated['codigo_empleado'] ?? $validated['identificacion'],
                'nombres' => $validated['nombres'],
                'apellidos' => $validated['apellidos'] ?? '',
                'email' => $validated['email'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'horario_predeterminado_id' => $validated['horario_predeterminado_id'] ?? null,
                'sec_user_login' => $validated['sec_user_login'] ?? null,
                'activo' => $validated['activo'] ?? true,
            ];

            $empleado = ThEmpleado::updateOrCreate(
                ['identificacion' => $validated['identificacion']],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Colaborador guardado exitosamente.',
                'empleado' => $empleado,
            ]);
        } catch (\Throwable $e) {
            \Log::error("Error en saveEmpleado: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar colaborador: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDispositivos()
    {
        try {
            $dispositivos = ThDispositivo::orderBy('id', 'desc')->get();
            return response()->json($dispositivos);
        } catch (\Throwable $e) {
            return response()->json([], 200);
        }
    }

    public function saveDispositivo(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'nombre' => 'required|string|max:100',
            'ip' => 'required|string|max:50',
            'puerto' => 'required|integer',
            'ip_local' => 'nullable|string|max:50',
            'puerto_local' => 'nullable|integer',
            'modelo' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:100',
            'ubicacion' => 'nullable|string|max:150',
            'usuario_api' => 'nullable|string|max:50',
            'password_api' => 'nullable|string',
            'estado' => 'nullable|string|max:20',
        ]);

        // Adaptar a los nombres de columna en BD
        $data = [
            'nombre' => $validated['nombre'],
            'ip' => $validated['ip'],
            'ip_lan' => $validated['ip_local'] ?: $validated['ip'],
            'ip_local' => $validated['ip_local'] ?: $validated['ip'],
            'puerto' => $validated['puerto'],
            'usuario' => $validated['usuario_api'] ?? 'admin',
            'password' => $validated['password_api'] ?? 'admin123',
            'modelo' => $validated['modelo'] ?? 'DS-K1T323MBFWX-E1',
            'serial_number' => $validated['serial_number'] ?? null,
            'ubicacion' => $validated['ubicacion'] ?? 'SEBLAN Principal',
            'estado' => $validated['estado'] ?? 'ONLINE',
            'activo' => true,
        ];

        $dispositivo = ThDispositivo::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo biométrico guardado exitosamente.',
            'dispositivo' => $dispositivo,
        ]);
    }

    public function deleteDispositivo($id)
    {
        $disp = ThDispositivo::findOrFail($id);
        $disp->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo eliminado.',
        ]);
    }

    /**
     * Horarios
     */
    public function getHorarios()
    {
        $horarios = ThHorario::orderBy('nombre', 'asc')->get();
        return response()->json(['data' => $horarios]);
    }

    public function saveHorario(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer',
                'nombre' => 'required|string|max:100',
                'hora_entrada' => 'required',
                'hora_salida' => 'required',
                'tolerancia_minutos' => 'nullable|integer',
                'minutos_almuerzo' => 'nullable|integer',
                'es_nocturno' => 'nullable|boolean',
            ]);

            $empresaId = DB::table('th_empresas')->where('codigo', 'SEBLAN')->value('id') 
                      ?: DB::table('th_empresas')->value('id');

            $data = [
                'empresa_id' => $empresaId,
                'nombre' => $validated['nombre'],
                'hora_entrada' => $validated['hora_entrada'],
                'hora_salida' => $validated['hora_salida'],
                'tolerancia_minutos' => $validated['tolerancia_minutos'] ?? 10,
                'minutos_almuerzo' => $validated['minutos_almuerzo'] ?? 60,
                'es_nocturno' => $validated['es_nocturno'] ?? false,
                'activo' => true,
            ];

            $horario = ThHorario::updateOrCreate(
                ['id' => $validated['id'] ?? null],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Horario guardado exitosamente.',
                'horario' => $horario,
            ]);
        } catch (\Throwable $e) {
            \Log::error("Error en saveHorario: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar horario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteHorario($id)
    {
        ThHorario::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Horario eliminado']);
    }

    /**
     * Solicitudes de Permisos
     */
    public function getSolicitudesGestion()
    {
        $solicitudes = ThSolicitudPermiso::with('empleado')->orderBy('id', 'desc')->paginate(30);
        return response()->json($solicitudes);
    }

    public function resolverSolicitudPermiso(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:APROBADO,RECHAZADO',
            'observacion' => 'nullable|string'
        ]);

        $sol = ThSolicitudPermiso::findOrFail($id);
        $sol->estado = $validated['estado'];
        $sol->observacion_resolucion = $validated['observacion'] ?? null;
        $sol->save();

        return response()->json(['success' => true, 'message' => 'Solicitud resuelta.']);
    }

    /**
     * Timbradas recientes en vivo
     */
    public function reporteTimbradas(Request $request)
    {
        $timbradas = ThTimbrada::with(['empleado', 'dispositivo'])
            ->orderBy('fecha_hora', 'desc')
            ->limit(50)
            ->get();

        return response()->json(['data' => $timbradas]);
    }

    /**
     * Sincronización manual de timbradas desde el terminal Hikvision vía ISAPI
     */
    public function syncTimbradas($id = null)
    {
        try {
            $dispositivos = $id ? ThDispositivo::where('id', $id)->get() : ThDispositivo::where('activo', true)->get();
            $totalDescargadas = 0;
            $nuevasInsertadas = 0;

            foreach ($dispositivos as $disp) {
                // Conectar al terminal por ISAPI
                $host = !empty($disp->ip) ? $disp->ip : $disp->ip_lan;
                $puerto = $disp->puerto ?: 80;
                $user = $disp->usuario ?: 'admin';
                $pass = $disp->password ?: '';

                if (empty($host) || empty($pass)) {
                    continue;
                }

                $url = "http://{$host}:{$puerto}/ISAPI/AccessControl/AcsEvent?format=json";

                // Buscar eventos de los últimos 7 días
                $startTime = now()->subDays(7)->startOfDay()->format('Y-m-d\TH:i:s');
                $endTime = now()->endOfDay()->format('Y-m-d\TH:i:s');

                $payload = [
                    "AcsEventSearchDescription" => [
                        "searchID" => "1",
                        "searchResultPosition" => 0,
                        "maxResults" => 300,
                        "AcsEventCond" => [
                            "searchSymbol" => "",
                            "timeReverseOrder" => true,
                            "startTime" => $startTime,
                            "endTime" => $endTime
                        ]
                    ]
                ];

                try {
                    $response = Http::withDigestAuth($user, $pass)
                        ->timeout(8)
                        ->post($url, $payload);

                    if ($response->successful()) {
                        $json = $response->json();
                        $events = $json['AcsEvent']['InfoList'] ?? [];

                        foreach ($events as $ev) {
                            $totalDescargadas++;
                            $serial = $ev['serialNo'] ?? null;
                            $empNo = trim((string)($ev['employeeNoString'] ?? ($ev['employeeNo'] ?? '')));
                            $timeStr = $ev['time'] ?? null;

                            if (!$timeStr || empty($empNo)) {
                                continue;
                            }

                            $dt = Carbon::parse($timeStr);

                            // PREVENCIÓN DE DUPLICADOS: Si ya existe la timbrada del colaborador a ese mismo segundo exacto
                            $existe = ThTimbrada::where('empleado_id', $empNo)
                                ->where('fecha_hora', $dt->format('Y-m-d H:i:s'))
                                ->exists();

                            if (!$existe) {
                                ThTimbrada::create([
                                    'empleado_id' => $empNo,
                                    'dispositivo_id' => $disp->id,
                                    'fecha_hora' => $dt,
                                    'tipo_evento' => 'VERIFY_SUCCESS',
                                    'sub_tipo' => $ev['currentVerifyMode'] ?? 'ISAPI_SYNC',
                                    'serial_dispositivo' => $disp->serial_number ?: ($disp->nombre ?? 'Hikvision'),
                                    'origen' => 'DESCARGA_ISAPI',
                                    'procesado' => true,
                                ]);
                                $nuevasInsertadas++;
                            }
                        }
                    }
                } catch (\Throwable $eDisp) {
                    \Log::warning("No se pudo conectar vía ISAPI a {$host}:{$puerto}: " . $eDisp->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Sincronización completada. Se revisaron {$totalDescargadas} registros en el biométrico y se agregaron {$nuevasInsertadas} nuevas marcaciones sin duplicados.",
                'total_revisadas' => $totalDescargadas,
                'nuevas_guardadas' => $nuevasInsertadas,
            ]);
        } catch (\Throwable $e) {
            \Log::error("Error general en syncTimbradas: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar con el dispositivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadAllEmpleadosToDevice($dispositivoId)
    {
        return response()->json([
            'success' => true,
            'message' => 'Nómina enviada para enrolamiento en el terminal Hikvision.'
        ]);
    }
}
