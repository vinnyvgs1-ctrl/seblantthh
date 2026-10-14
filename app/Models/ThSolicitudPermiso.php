<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThSolicitudPermiso extends Model
{
    protected $table = 'th_solicitudes_permisos';
    public $timestamps = false;

    protected $fillable = [
        'empleado_id',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'adjunto_url',
        'descuenta_vacaciones',
        'estado',
        'aprobado_por',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'descuenta_vacaciones' => 'boolean',
    ];

    public function empleado()
    {
        return $this->belongsTo(ThEmpleado::class, 'empleado_id', 'identificacion');
    }
}
