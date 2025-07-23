<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipanteCorporativaTemporal extends Model
{
    protected $table = 'participantes_corporativas_temporal';
    protected $guarded = [];

           public function tipoInscripcion()
{
   return $this->belongsTo(InscripcionTipoCorporativa::class, 'tipo_inscripcion');
}
}
