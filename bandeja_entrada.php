<?php

require_once("auxiliar/DB.php");
session_start();

$usuario = null;
if (isset($_SESSION['CORREO']) && !empty($_SESSION['CORREO'])) {
    $usuario = DB::recuperarUsuarioPorCorreo($_SESSION['CORREO']);
}

//acciones
// - Modificar, reenvia al usuario a la ventana de registro y permite modificar los datos
// del usuasrio
// - Salir, redirige a la ventana de Login y borra los datos de session
if (isset($_POST['salir']) && !empty($_POST['salir'])) {
    header("Location: login.php");
    session_unset();
} else if (isset($_POST['modificar']) && !empty($_POST['modificar'])) {
    // guardamos en la session que el usuario quiere modificar sus datos
    $_SESSION['MODIFICAR'] = "1";
    header("Location: registro.php");
}


?>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Kanomail.es > LOGIN</title>
        <link href="tienda.css" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div>
            <form action='bandeja_entrada.php' method='post'>
                
            
            <?php 
                echo $usuario->getNombre()." ".$usuario->getApe1()." ".$usuario->getApe2();
            ?>
                <input type='submit' name='modificar' value='Modificar datos de usuario' />
                <input type='submit' name='salir' value='Salir' />
            </form>
        </div>
    </body>
</html>