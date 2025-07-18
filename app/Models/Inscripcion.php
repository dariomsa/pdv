<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscripciones';
    protected $guarded = [];


    public function creador() {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function cierreCaja() {
        return $this->belongsTo(CierreCaja::class, 'id_cierre_caja');
    }

    public function participantes() {
        return $this->hasMany(Participante::class);
    }

    public function pagos() {
        return $this->hasMany(Pago::class);
    }

}
