<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<p>Favor generar su clave de acceso para poder ingresar al sistema</p><br>
<p>Usuario Empleado :  {{$rut}} </p>

<div>
    Para configurar una nueva contraseña por favor ingrese al siguiente enlace:<br><br>
    
    <span style="color: #0eadb0;"><a href="{{$app_url}}/auth/cambiar-contrasena?api_token={{$api_token}}">{{$app_url}}/auth/cambiar-contrasena?api_token={{$api_token}}</a></span><br><br>
    
    Saludos cordiales.<br><br>
    <span style="font-weight: bold;"> Esta es una cuenta de correo electrónico no monitoreada, por favor no responda este mensaje.</span>
</div> 

</body>
</html>