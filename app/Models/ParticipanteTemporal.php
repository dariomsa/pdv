<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipanteTemporal extends Model
{
    use HasFactory;

    protected $table = 'participantes_temporal';
   protected $fillable = [
    'inscripcion_id',
    'created_by_id',
    'tipo_inscripcion',
    'tipo_documento',
    'numero_documento',
    'nombres',
    'apellidos',
    'nacionalidad',
    'genero',
    'fecha_nacimiento',
    'categoria',
    'talla',
    'celular',
    'email',
    'direccion',
    'provincia',
    'ciudad',
    'parroquia',
    'corral',
    'tercera_edad',
    'discapacidad',
    'emergencia_nombre',
    'emergencia_celular',

];


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
