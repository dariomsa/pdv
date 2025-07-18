<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscripcionTipo extends Model
{
    use HasFactory;

    protected $table = 'inscripcion_tipo';
    protected $guarded = [];


    public function participantes() {
        return $this->hasMany(Participante::class, 'tipo_inscripcion');
    }
    

    

}
