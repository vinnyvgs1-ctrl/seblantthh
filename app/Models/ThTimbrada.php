<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThTimbrada extends Model
{
    protected $table = 'th_timbradas';

    protected $fillable = [
        'empleado_id',
        'dispositivo_id',
        'codigo_empleado',
        'fecha_hora',
        'fecha',
        'tipo_marcacion',
        'metodo_verificacion',
        'foto_path',
        'raw_payload',
        'origen',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'fecha' => 'date',
        'raw_payload' => 'array',
    ];

    public function empleado()
    {
        return $this->belongsTo(ThEmpleado::class, 'empleado_id');
    }

    public function dispositivo()
    {
        return $this->belongsTo(ThDispositivo::class, 'dispositivo_id');
    }
}
