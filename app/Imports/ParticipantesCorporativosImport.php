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

         $talla = $row['talla_camiseta'] ?? '';
    
    // Validar stock disponible
        $inventario = InventarioTotal::where('talla', $talla)->first();

        if (!$inventario) {
        throw new \Exception("No existe inventario para la talla: $talla");
         }

         if ($inventario->stock_restante <= 0) {
        throw new \Exception("Sin stock disponible para la talla: $talla");
         }
        
        return new ParticipanteCorporativaTemporal([
            'inscripcion_id'     => $this->inscripcion_id,
            'created_by_id'      => Auth::id(),
            'tipo_documento'     => 'CÉDULA',
            'numero_documento'   => $row['cedula'] ?? null,
            'nombres'            => $row['nombres'] ?? '',
            'apellidos'          => $row['apellidos'] ?? '',
            'genero'             => $row['genero'] ?? '',
            'fecha_nacimiento'=>\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_nac'])->format('Y-m-d'),
            'categoria'          => 'Sin categoría',
            'talla'              => $row['talla_camiseta'] ?? '',
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
