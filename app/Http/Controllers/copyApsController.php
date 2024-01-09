<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class copyApsController extends Controller
{
    public function copyAPS($path)
    {
        // Nombre del archivo CSV
        //$csvFile = 'C:/temp/aps-prueba-2-CSV.csv';

        // Nombre de la tabla en la base de datos
        $tableName = 'consultants.aps';

        // Conexión a la base de datos
        $dbHost = 'lareina-database.cpwa51qkkrgf.us-east-1.rds.amazonaws.com';
        $dbName = 'lareina_dev';
        $dbUser = 'lareina';
        $dbPass = 'lareinaadmin';
        putenv("PGPASSWORD=lareinaadmin");

        // Comando COPY para copiar el contenido del archivo CSV a la tabla
        $copyCommand = sprintf("\COPY %s FROM '%s' DELIMITER ';' CSV HEADER", $tableName, $path);

        // Comando para ejecutar el comando COPY a través del shell de PHP
        $shellCommand = sprintf('psql -h %s -d %s -U %s -c "%s"', $dbHost, $dbName, $dbUser, $copyCommand);

        // Ejecutar el comando en el shell
        $output = shell_exec($shellCommand);

        $conn = pg_connect("host = $dbHost dbname = $dbName user = $dbUser password = $dbPass");
        $update = pg_query($conn, "UPDATE consultants.aps SET rut = UPPER(rut)");

        // Verificar si se ejecutó correctamente
        if ($output === null) {
            echo "El contenido del archivo CSV se ha copiado correctamente a la tabla.";
        } else {
            echo "Ha ocurrido un error al copiar el contenido del archivo CSV a la tabla.";
        }
    }
}
