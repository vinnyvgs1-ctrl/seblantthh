<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThSolicitudVacacion extends Model
{
    protected $table = 'th_solicitudes_vacacion';

    protected $fillable = [
        'empleado_id',
        'fecha_desde',
        'fecha_hasta',
        'dias_solicitados',
        'motivo',
        'estado',
        'gestionado_por',
        'observaciones_resolucion',
        'gestionado_at',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'dias_solicitados' => 'integer',
        'gestionado_at' => 'datetime',
    ];

    public function empleado()
    {
        return $this->belongsTo(ThEmpleado::class, 'empleado_id');
    }

    public function gestionador()
    {
        return $this->belongsTo(User::class, 'gestionado_por');
    }
}
