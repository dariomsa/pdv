<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InscripcionTipo;
use App\User;

class Participante extends Model
{
    use HasFactory;

    protected $table = 'participantes';
    protected $guarded = [];


    public function inscripcion() {
        return $this->belongsTo(Inscripcion::class);
    }

    public function facturacionDetalle() {
        return $this->hasOne(FacturacionDetalle::class);
    }

       public function tipoInscripcion()
{
   return $this->belongsTo(InscripcionTipo::class, 'tipo_inscripcion');
}


    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function creador()
{
    return $this->belongsTo(User::class, 'created_by_id');
}

}
