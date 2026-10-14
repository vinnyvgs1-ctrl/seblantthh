<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThTipoPermiso extends Model
{
    protected $table = 'th_tipos_permiso';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'requiere_adjunto',
        'es_remunerado',
        'descuenta_vacaciones',
        'max_dias',
        'max_horas',
        'color_hex',
        'activo',
    ];

    protected $casts = [
        'requiere_adjunto' => 'boolean',
        'es_remunerado' => 'boolean',
        'descuenta_vacaciones' => 'boolean',
        'max_dias' => 'integer',
        'max_horas' => 'integer',
        'activo' => 'boolean',
    ];

    public function solicitudes()
    {
        return $this->hasMany(ThSolicitudPermiso::class, 'tipo_permiso_id');
    }
}
