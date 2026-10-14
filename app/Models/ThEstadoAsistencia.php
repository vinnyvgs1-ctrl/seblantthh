<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThEstadoAsistencia extends Model
{
    protected $table = 'th_estados_asistencia';

    protected $fillable = [
        'codigo',
        'nombre',
        'color_hex',
        'requiere_justificacion',
        'es_remunerado',
        'activo',
    ];

    protected $casts = [
        'requiere_justificacion' => 'boolean',
        'es_remunerado' => 'boolean',
        'activo' => 'boolean',
    ];
}
