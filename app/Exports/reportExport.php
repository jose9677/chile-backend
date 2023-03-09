<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use App\Models\reports;
use Maatwebsite\Excel\Concerns\FromCollection;

class reportExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function infoReport()
    {
        reports::truncate();

        $query = DB::insert(
            "INSERT INTO consultants.reports 
            (SELECT 
            b.created_at as Fecha_Inscripcion,
            a.rut as RUT,
            a.names as Nombres,
            a.lastname as Apellidos,
            a.f_nac as Fecha_de_Nac,
            k.description as Sexo,
            a.email as Email,
            a.landline as Tel_Fijo,
            a.mobile_phone as Tel_Móvil,
            e.street as Calle,
            b.way_number as Número_Calle,
            f.description as Casa_Dpto_Ofic,
            b.property_number as Número_Casa_Dpto_Ofic,
            CASE WHEN a.aps = 't' THEN 'SI' ELSE 'NO' END AS APS,
            b.id as Nro_Solicitud,
            d.prevention_type as Tipo_Previsión,
            c.prevention_detail as Detalle_de_Previsión,
            CASE WHEN b.assurance = 't' THEN 'SI' ELSE 'NO' END AS Seguro_Complementario,
            CASE WHEN b.carer = 't' THEN 'SI' ELSE 'NO' END AS Cuidador,
            h.description as Motivo,
            i.description as Dedicación,
            j.description as Remuneración,
            g.status as Estado_de_Solicitud,
            a.percent as Descuento_Corporacion
            FROM consultants.users a 
            left JOIN consultants.solicitudes b ON a.rut = b.rut_user
            left JOIN consultants.preventions_details c ON b.id_prevention_type_detail = c.id
            left JOIN consultants.preventions_types d ON d.id = c.id_prevention_type
            left JOIN consultants.locations e ON e.id = b.id_location
            left JOIN consultants.property_types f ON f.id = b.id_property_type
            left JOIN consultants.status g ON g.id = b.id_status
            left JOIN consultants.motives h ON h.id_motive = b.id_motive
            left JOIN consultants.dedications i ON i.id_dedication = b.id_dedication
            left JOIN consultants.remunerations j ON j.id_remuneration = b.id_remuneration
            left JOIN consultants.sex k on k.id_sex = a.id_sex
            WHERE a.employee = 'f' and a.otp is null
            order by b.created_at asc)"
            );
    }
    
    public function collection()
    {
        $this->infoReport();

        return reports::all();
    }
}
