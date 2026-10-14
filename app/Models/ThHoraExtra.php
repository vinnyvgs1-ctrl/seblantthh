<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThHoraExtra extends Model
{
    protected $table = 'th_horas_extras';

    protected $fillable = [
        'empleado_id',
        'asistencia_diaria_id',
        'fecha',
        'tipo_recargo',
        'hora_desde',
        'hora_hasta',
        'cantidad_horas',
        'estado',
        'autorizado_por',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad_horas' => 'decimal:2',
    ];

    public function empleado()
    {
        return $this->belongsTo(ThEmpleado::class, 'empleado_id');
    }

    public function asistencia()
    {
        return $this->belongsTo(ThAsistenciaDiaria::class, 'asistencia_diaria_id');
    }

    public function autorizador()
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }
}
