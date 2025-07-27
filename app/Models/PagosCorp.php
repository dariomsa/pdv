<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagosCorp extends Model
{
    protected $table = 'pagos_corporativas';

    protected $fillable = [
        'inscripcion_id',
        'facturacion_id',
        'pago_id',
        'monto',
        'referencia',
        'estado',
    ];

  
    /**
     * Relación con InscripcionCorporativa
     */
    

    /**
     * Relación con FacturacionCorporativa
     */
  

    /**
     * Relación con Forma de Pago (si la quieres)
     */
   
}
