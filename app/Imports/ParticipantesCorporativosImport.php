<?php

namespace App\Imports;

use App\Models\ParticipanteCorporativaTemporal;
use App\Models\InventarioTotal;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ParticipantesCorporativosImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $inscripcion_id;

    public function __construct($inscripcion_id)
    {
        $this->inscripcion_id = $inscripcion_id;
    }

    public function model(array $row)
    {


$inscripcionId = $row['carrera'] ?? null;


$genero = strtoupper(trim($row['genero'] ?? ''));
$talla  = strtoupper(trim($row['talla'] ?? ''));

// Mapear inscripción -> carrera_id (según tu regla)
$mapCarreras = [
    1 => 1, // inscripcion 1 = 15K (carrera_id 1)
    5 => 2, // inscripcion 5 = 21K (carrera_id 2)
	10 => 3, // inscripcion 5 = 21K (carrera_id 2)
];

if (!isset($mapCarreras[$inscripcionId])) {
    throw new \Exception("Inscripción no válida para inventario: {$inscripcionId}");
}

$carreraId = $mapCarreras[$inscripcionId];

// Validar stock disponible (talla + genero + carrera)
$inventario = InventarioTotal::where('talla', $talla)
    ->where('genero', $genero)
    ->where('carrera_id', $carreraId)
    ->first();

if (!$inventario) {
    throw new \Exception("No existe inventario para talla {$talla}, género {$genero}, carrera {$carreraId}");
}

if ((int)$inventario->stock_restante <= 0) {
    throw new \Exception("Sin stock disponible para talla {$talla}, género {$genero}, carrera {$carreraId}");
}

//dd('Esta correcto pasó la validación');
        
        return new ParticipanteCorporativaTemporal([
            'inscripcion_id'     => $this->inscripcion_id,
            'created_by_id'      => Auth::id(),
            'tipo_documento'     => 'CÉDULA',
			'numero_documento'   => $row['cedula'] ?? null,
            'tipo_inscripcion'   => $row['carrera'] ?? null,
            'nombres'            => $row['nombres'] ?? '',
            'apellidos'          => $row['apellidos'] ?? '',
            'genero'             => $row['genero'] ?? '',
            'fecha_nacimiento'=>\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_nac'])->format('Y-m-d'),
            'categoria'          => 'Sin categoría',
            'talla'              => $row['talla'] ?? '',
            'celular'            => $row['celular'] ?? '',
            'email'              => $row['mail'] ?? '',
            'direccion'          => $row['direccion'] ?? '',
            'provincia'          => $row['provincia'] ?? '',
            'ciudad'             => $row['ciudad'] ?? '',
            'parroquia'          => $row['parroquia'] ?? '',
            'discapacidad'       => $row['discapacidad'] ?? '',
        ]);
    }

    public function rules(): array
    {
        return [
    
        ];
    }
}
