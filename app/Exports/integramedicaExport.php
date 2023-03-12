<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\integramedica;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class integramedicaExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function infoReport()
    {
        integramedica::truncate();

        $query = DB::insert(
            "INSERT INTO consultants.integramedica
            (SELECT
            '262' as Codigo_Entidad,
            upper(a.rut) as Rut,
            upper(a.names) as Nombres,
            upper(a.lastname) as Apellidos,
            to_char(date(a.f_nac), 'dd/mm/yyyy') as Fecha_Nacimiento,
            k.description as Sexo,
            0 as Relacion,
            to_char(date(b.created_at), 'dd/mm/yyyy') as Fecha_Ingreso,
            null as Fecha_Inicio_Vigencia,
            '31/12/2023' as Fecha_Término_Vigencia,
            900001591 as Codigo_Plan,
            null as Rut_Titular,
            null as Tipo_Evento
            FROM consultants.users a 
            left JOIN consultants.solicitudes b ON a.rut = b.rut_user
            left JOIN consultants.sex k on k.id_sex = a.id_sex
            WHERE b.id_status = 6 and ((a.employee = 'f' and a.otp is null) or (a.employee = 't' and b.id is not null))
            order by b.created_at asc 
            )");

            return response()->json();
    }
    
    /*public function collection()
    {
        $this->infoReport();

        return integramedica::all();
    }*/

    public function view(): View
    {   
        $this->infoReport();

        return view('exportReport', [
            'integramedica' => integramedica::all()
        ]);
    }
}
