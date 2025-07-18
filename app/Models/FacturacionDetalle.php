<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturacionDetalle extends Model
{
    use HasFactory;

    protected $table = 'facturacion_detalles';
    protected $guarded = [];


    public function facturacion() {
        return $this->belongsTo(Facturacion::class);
    }

    public function participante() {
        return $this->belongsTo(Participante::class);
    }

}
