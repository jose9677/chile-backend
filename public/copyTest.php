<?php
    // Nombre del archivo CSV
    $csvFile = 'C:/temp/aps-prueba-2-CSV.csv';

    // Nombre de la tabla en la base de datos
    $tableName = 'consultants.aps';

    // Conexión a la base de datos
    $dbHost = 'lareina-database.cpwa51qkkrgf.us-east-1.rds.amazonaws.com';
    $dbName = 'lareina_dev';
    $dbUser = 'lareina';
    putenv("PGPASSWORD=lareinaadmin");

    // Comando COPY para copiar el contenido del archivo CSV a la tabla
    $copyCommand = sprintf("\COPY %s FROM '%s' DELIMITER ';' CSV HEADER", $tableName, $csvFile);

    // Comando para ejecutar el comando COPY a través del shell de PHP
    $shellCommand = sprintf('psql -h %s -d %s -U %s -c "%s"', $dbHost, $dbName, $dbUser, $copyCommand);

    // Ejecutar el comando en el shell
    $output = shell_exec($shellCommand);

    // Verificar si se ejecutó correctamente
    if ($output === null) {
        echo "El contenido del archivo CSV se ha copiado correctamente a la tabla.";
    } else {
        echo "Ha ocurrido un error al copiar el contenido del archivo CSV a la tabla.";
    }
?>