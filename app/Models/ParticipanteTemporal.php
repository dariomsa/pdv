<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipanteTemporal extends Model
{
    use HasFactory;

    protected $table = 'participantes_temporal';
    protected $guarded = [];


    public function inscripcion() {
        return $this->belongsTo(Inscripcion::class);
    }

        public function tipoInscripcion()
{
   return $this->belongsTo(InscripcionTipo::class, 'tipo_inscripcion');
}


    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

}
