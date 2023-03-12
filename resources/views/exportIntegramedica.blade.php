<table>
    <thead>
        <tr>
        <th>codigo_entidad</th>
        <th>rut</th>
        <th>nombres</th>
        <th>apellidos</th>
        <th>fecha_nacimiento</th>
        <th>sexo</th>
        <th>relacion</th>
        <th>fecha_ingreso</th>
        <th>fecha_inicio_vigencia</th>
        <th>fecha_término_vigencia</th>
        <th>codigo_plan</th>
        <th>rut_titular</th>
        <th>tipo_evento</th>
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