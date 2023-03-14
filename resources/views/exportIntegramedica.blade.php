<table>
    <thead>
        <tr>
        <th>Código Entidad</th>
        <th>Rut</th>
        <th>Nombres</th>
        <th>Apellidos</th>
        <th>Fecha Nacimiento</th>
        <th>Sexo</th>
        <th>Relación</th>
        <th>Fecha Ingreso</th>
        <th>Fecha Inicio Vigencia</th>
        <th>Fecha Término Vigencia</th>
        <th>Código Plan</th>
        <th>Rut Titular</th>
        <th>Tipo Evento</th>
        </tr>
    </thead>

    <tbody>
        @foreach($integramedica as $integra)
        <tr>
        <td>{{ $integra->codigo_entidad }}</td>
        <td>{{ $integra->rut }}</td>
        <td>{{ $integra->nombres }}</td>
        <td>{{ $integra->apellidos }}</td>
        <td>{{ $integra->fecha_nacimiento }}</td>
        <td>{{ $integra->sexo }}</td>
        <td>{{ $integra->relacion }}</td>
        <td>{{ $integra->fecha_ingreso }}</td>
        <td>{{ $integra->fecha_inicio_vigencia }}</td>
        <td>{{ $integra->fecha_término_vigencia }}</td>
        <td>{{ $integra->codigo_plan }}</td>
        <td>{{ $integra->rut_titular }}</td>
        <td>{{ $integra->tipo_evento }}</td>
        </tr>
        @endforeach
    </tbody>
</table>