<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreCaja extends Model
{
    use HasFactory;

    protected $table = 'cierre_caja';
    protected $guarded = [];


    public function creador() {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function detalles() {
        return $this->hasMany(CierreCajaDetalle::class);
    }

}
