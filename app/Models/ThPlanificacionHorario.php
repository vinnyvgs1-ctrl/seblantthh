<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThPlanificacionHorario extends Model
{
    protected $table = 'th_planificacion_horarios';

    protected $fillable = [
        'empleado_id',
        'horario_id',
        'fecha',
        'es_dia_libre',
        'motivo_cambio',
        'asignado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'es_dia_libre' => 'boolean',
    ];

    public function empleado()
    {
        return $this->belongsTo(ThEmpleado::class, 'empleado_id');
    }

    public function horario()
    {
        return $this->belongsTo(ThHorario::class, 'horario_id');
    }

    public function asignador()
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }
}
