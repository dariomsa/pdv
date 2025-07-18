<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreCajaDetalle extends Model
{
    use HasFactory;

    protected $table = 'cierre_caja_detalles';
    protected $guarded = [];


    public function cierreCaja() {
        return $this->belongsTo(CierreCaja::class);
    }

    public function formaPago() {
        return $this->belongsTo(FormaPago::class);
    }

}
