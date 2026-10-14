<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThAsistenciaDiaria extends Model
{
    protected $table = 'th_asistencia_diaria';

    protected $fillable = [
        'empleado_id',
        'fecha',
        'horario_planificado_id',
        'estado_asistencia_id',
        'primera_entrada',
        'inicio_almuerzo',
        'fin_almuerzo',
        'ultima_salida',
        'horas_trabajadas',
        'minutos_atraso',
        'horas_recargo_25',
        'horas_suplementarias_50',
        'horas_extraordinarias_100',
        'bloqueado_para_nomina',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'horas_trabajadas' => 'decimal:2',
        'minutos_atraso' => 'integer',
        'horas_recargo_25' => 'decimal:2',
        'horas_suplementarias_50' => 'decimal:2',
        'horas_extraordinarias_100' => 'decimal:2',
        'bloqueado_para_nomina' => 'boolean',
    ];

    public function empleado()
    {
        return $this->belongsTo(ThEmpleado::class, 'empleado_id');
    }

    public function horarioPlanificado()
    {
        return $this->belongsTo(ThHorario::class, 'horario_planificado_id');
    }

    public function estadoAsistencia()
    {
        return $this->belongsTo(ThEstadoAsistencia::class, 'estado_asistencia_id');
    }

    public function horasExtras()
    {
        return $this->hasMany(ThHoraExtra::class, 'asistencia_diaria_id');
    }
}
