<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    use HasFactory;

    protected $table = 'formas_pago';
    protected $guarded = [];


    public function detalles() {
        return $this->hasMany(CierreCajaDetalle::class);
    }

}
