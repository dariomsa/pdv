<?php

namespace App\Models;
use App\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipanteGratuita extends Model
{
    use HasFactory;

    protected $table = 'participantes_gratuitas';
    protected $guarded = [];

    public function tipoInscripcion()
{
    return $this->belongsTo(InscripcionTipo::class, 'tipo_inscripcion');
}

    public function creador()
{
    return $this->belongsTo(User::class, 'created_by_id');
}

}
