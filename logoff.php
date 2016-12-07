<?php
    // Recuperamos la información de la sesión
    session_start();
    
    // Y la eliminamos
    session_unset();
    
    // redireccionamos a la ventana de login
    header("Location: login.php");
?>
