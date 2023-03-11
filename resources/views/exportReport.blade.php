<table>
    <thead>
        <tr>
        <th>fecha_inscripcion</th>
        <th>rut</th>
        <th>nombres</th>
        <th>apellidos</th>
        <th>fecha_de_nac</th>
        <th>sexo</th>
        <th>email</th>
        <th>tel_fijo</th>
        <th>tel_móvil</th>
        <th>calle</th>
        <th>número_calle</th>
        <th>casa_dpto_ofic</th>
        <th>número_casa_dpto_ofic</th>
        <th>aps</th>
        <th>nro_solicitud</th>
        <th>tipo_previsión</th>
        <th>detalle_de_previsión</th>
        <th>seguro_complementario</th>
        <th>cuidador</th>
        <th>motivo</th>
        <th>dedicación</th>
        <th>remuneración</th>
        <th>estado_de_solicitud</th>
        <th>descuento_corporacion</th>
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