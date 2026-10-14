<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThJustificacion extends Model
{
    protected $table = 'th_justificaciones';

    protected $fillable = [
        'empleado_id',
        'fecha_desde',
        'fecha_hasta',
        'tipo',
        'motivo',
        'documento_adjunto',
        'estado',
        'aprobado_por',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
    ];

    public function empleado()
    {
        return $this->belongsTo(ThEmpleado::class, 'empleado_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}
