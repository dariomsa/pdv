<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Participante;
use Illuminate\Http\Request;

class ParticipanteController extends Controller
{
    public function destroy($id)
    {
        $participante = Participante::findOrFail($id);
        $participante->delete();

        return redirect()->route('admin.inscripciones.create')->with('success', 'Participante eliminado correctamente.');
    }

    public function index()
    {
        echo 'entro';
    }
}
