<?php

namespace App\Imports;

use App\Models\ParticipanteCorporativaTemporal;
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

        //  dd('ss');
        
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
        ]);
    }

    public function rules(): array
    {
        return [
    
        ];
    }
}
