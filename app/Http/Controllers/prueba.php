<?php

if (isset($_POST['url'])) {
    $nombre = uniqid(). ".jpg";
    $dir = "C:\Users\Jose\Desktop\imagenes";

    $imagen = file_get_contents($_POST['url']);
    file_put_contents($dir. $nombre, $imagen);
}

?>