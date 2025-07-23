<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipanteTemporalGratuita extends Model
{
    use HasFactory;

    protected $table = 'participantes_temporal_gratuitas';
    protected $guarded = [];

    public function tipoInscripcion()
{
    return $this->belongsTo(InscripcionTipo::class, 'tipo_inscripcion_id');
}
}
