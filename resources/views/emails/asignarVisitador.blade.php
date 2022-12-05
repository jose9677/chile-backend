<!DOCTYPE html>
<html lang="en">
    <head>
        <title></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="css/style.css" rel="stylesheet">
    </head>
    <body>
    <h5>Asunto: {{$names}} {{$lastname}} te ha asignado la solicitud Nro. {{$id}}</h5>

    <div>
        Hola {{$names}} {{$lastname}},<br><br>
        Te informamos que la solicitud identificada con el Nro. {{$id}} te fue asignada para realizar el proceso de verificación y aprobación.<br><br>
        Por favor ingresa al sistema y programa una cita con el vecino para realizar la visita correspondiente.<br><br>

        Saludos cordiales.
        <br><br>
    </div>
    </body>
</html>