<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\UmbralesCcaa; // <--- Aquí llamamos al modelo correcto

class SituacionEmergencia extends Model
{
    use HasFactory;

    protected $table = 'situacion_emergencia';

    protected $fillable = [
        'ccaa_id',
        'provincia',
        'nivel',
        'fecha',
        'hora',
        'descripcion',
        'ruta_pdf',
        'usuario_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ccaa()
    {
        // Ahora Laravel sabe exactamente qué archivo usar
        return $this->belongsTo(UmbralesCcaa::class, 'ccaa_id', 'c_id');
    }
}
