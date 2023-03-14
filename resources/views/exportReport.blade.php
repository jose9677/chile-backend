<table>
    <thead>
        <tr>
        <th>Fecha Inscripcion</th>
        <th>RUT</th>
        <th>Nombres</th>
        <th>Apellidos</th>
        <th>Fecha de Nac</th>
        <th>Sexo</th>
        <th>Edad</th>
        <th>Email</th>
        <th>Tel. fijo</th>
        <th>Tel. Móvil</th>
        <th>Calle</th>
        <th>Número Calle</th>
        <th>Casa/Dpto/Ofic</th>
        <th>Número Casa/Dpto/Ofic</th>
        <th>APS</th>
        <th>Nro. Solicitud</th>
        <th>Tipo Previsión</th>
        <th>Detalle de Previsión</th>
        <th>Seguro Complementario</th>
        <th>Cuidador</th>
        <th>Motivo</th>
        <th>Dedicación</th>
        <th>Remuneración</th>
        <th>Estado de Solicitud</th>
        <th>Descuento Corporacion</th>
        </tr>
    </thead>

    <tbody>
        @foreach($reports as $report)
        <tr>
        <td>{{ $report->fecha_inscripcion }}</td>
        <td>{{ $report->rut }}</td>
        <td>{{ $report->nombres }}</td>
        <td>{{ $report->apellidos }}</td>
        <td>{{ $report->fecha_de_nac }}</td>
        <td>{{ $report->sexo }}</td>
        <td>{{ $report->edad }}</td>
        <td>{{ $report->email }}</td>
        <td>{{ $report->tel_fijo }}</td>
        <td>{{ $report->tel_móvil }}</td>
        <td>{{ $report->calle }}</td>
        <td>{{ $report->número_calle }}</td>
        <td>{{ $report->casa_dpto_ofic }}</td>
        <td>{{ $report->número_casa_dpto_ofic }}</td>
        <td>{{ $report->aps }}</td>
        <td>{{ $report->nro_solicitud }}</td>
        <td>{{ $report->tipo_previsión }}</td>
        <td>{{ $report->detalle_de_previsión }}</td>
        <td>{{ $report->seguro_complementario }}</td>
        <td>{{ $report->cuidador }}</td>
        <td>{{ $report->motivo }}</td>
        <td>{{ $report->dedicación }}</td>
        <td>{{ $report->remuneración }}</td>
        <td>{{ $report->estado_de_solicitud }}</td>
        <td>{{ $report->descuento_corporacion }}</td>
        </tr>
        @endforeach
    </tbody>
</table>