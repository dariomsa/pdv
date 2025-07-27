<?php

namespace App\Models;
use App\User;

use Illuminate\Database\Eloquent\Model;

class ParticipanteCorporativa extends Model
{
    protected $table = 'participantes_corporativas';
    protected $guarded = [];

       public function tipoInscripcion()
{
   return $this->belongsTo(InscripcionTipoCorporativa::class, 'tipo_inscripcion');
}




    public function creador()
{
    return $this->belongsTo(User::class, 'created_by_id');
}

}
