<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturacionCorporativa extends Model
{
    protected $table = 'facturacion_corporativas';
    protected $guarded = [];

    public function formaPago()
{
    return $this->belongsTo(FormaPago::class, 'forma_pago_id', 'id');
}

}
