<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}
