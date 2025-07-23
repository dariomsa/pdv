<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InscripcionCorporativa extends Model
{
    use HasFactory;

    protected $table = 'inscripciones_corporativas';

    protected $guarded = [];

  

    // 🔗 Relación con los participantes temporales cargados por Excel
    public function participantesTemporales()
    {
        return $this->hasMany(ParticipanteCorporativaTemporal::class, 'inscripcion_id');
    }

    // 🔗 Relación con los participantes definitivos
    public function participantes()
    {
        return $this->hasMany(ParticipanteCorporativa::class, 'inscripcion_id');
    }

      public function creador() {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
