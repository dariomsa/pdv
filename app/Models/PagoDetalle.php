<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoDetalle extends Model
{
    use HasFactory;

    protected $table = 'pago_detalles';
    protected $guarded = [];


    public function pago() {
        return $this->belongsTo(Pago::class);
    }

    public function participante() {
        return $this->belongsTo(Participante::class);
    }

}
