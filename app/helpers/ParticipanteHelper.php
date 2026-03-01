<?php

namespace App\helpers;

use App\Models\Participante;
use App\Models\ParticipanteGratuita;
use App\Models\ParticipanteCorporativa;

class ParticipanteHelper
{
    public static function yaInscrito( $numeroDocumento)
    {
        return
            Participante::where('numero_documento', $numeroDocumento)
                ->exists() ||

            ParticipanteGratuita::where('numero_documento', $numeroDocumento)
                ->exists() ||

            ParticipanteCorporativa::where('numero_documento', $numeroDocumento)
                ->exists();
    }
}
