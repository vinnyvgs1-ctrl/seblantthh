<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThHorario extends Model
{
    protected $table = 'th_horarios';
    public $timestamps = false; // La tabla en PostgreSQL solo tiene created_at

    protected $fillable = [
        'empresa_id',
        'nombre',
        'codigo',
        'hora_entrada',
        'hora_salida',
        'tolerancia_minutos',
        'tolerancia_entrada_min',
        'minutos_almuerzo',
        'tiempo_almuerzo_min',
        'es_nocturno',
        'cruza_medianoche',
        'color_hex',
        'activo',
    ];

    protected $casts = [
        'tolerancia_minutos' => 'integer',
        'minutos_almuerzo' => 'integer',
        'es_nocturno' => 'boolean',
        'activo' => 'boolean',
    ];

    public function planificaciones()
    {
        return $this->hasMany(ThPlanificacionHorario::class, 'horario_id');
    }
}
