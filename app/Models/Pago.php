<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $guarded = [];


    public function inscripcion() {
        return $this->belongsTo(Inscripcion::class);
    }

    public function facturacion() {
        return $this->belongsTo(Facturacion::class);
    }

    public function detalles() {
        return $this->hasMany(PagoDetalle::class);
    }

}
