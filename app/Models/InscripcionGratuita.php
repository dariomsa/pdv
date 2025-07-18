<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscripcionGratuita extends Model
{
    use HasFactory;

    protected $table = 'inscripciones_gratuitas';
    protected $guarded = [];

        public function participantes() {
        return $this->hasMany(ParticipanteGratuita::class);
    }



    

    public function participantes_temporal()
{
    return $this->hasMany(ParticipanteTemporalGratuita::class, 'inscripcion_id');
}

}
