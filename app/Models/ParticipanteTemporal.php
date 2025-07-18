<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipanteTemporal extends Model
{
    use HasFactory;

    protected $table = 'participantes_temporal';
    protected $guarded = [];


    public function inscripcion() {
        return $this->belongsTo(Inscripcion::class);
    }

}
