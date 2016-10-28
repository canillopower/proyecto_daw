<?php
// añadimos los require necesario
require_once("auxiliar/DB.php");
require_once("auxiliar/Usuario.php");

$error = [];
$errorOperacion ="";
$datos = [];

// cargamos los usuarios de BBDD pendientes de alta

$usuariosPteAlta = DB::recuperarUsuariosPedienteAlta();

// control acciones

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!-- Desarrollo Web en Entorno Servidor -->
<!-- Autor: José María Rodríguez García -->
<!-- Proyecto : Gestor de correo electrónico -->
<!-- Ventna: login.php Permite al usuario logearse o registrarse en la aplicación -->
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Kanomail.es > ADMIN USUARIOS</title>
        <link href="tienda.css" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div id='registro'>
            <form action='registro.php' method='post'>
                <fieldset>
                    <div>  
                    <legend>Adminitracion usuarios</legend>
                    
                    <table>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido_1</th> 
                            <th>Apellido_2</th>    
                        </tr>
                   
                    <?php 
                        if (isset($usuariosPteAlta) && count($usuariosPteAlta) >= 0) {
                            foreach ($usuariosPteAlta as $usuario) {
                                echo "<tr>";
                                echo "<td>".$usuario->getNombre()."</td>";
                                echo "<td>".$usuario->getApe1()."</td>";
                                echo "<td>".$usuario->getApe2()."</td>";
                                echo "</tr>";
                            }
                        }
                    ?>
                    </table>
                    </div>                   
                </fieldset>
            </form>
        </div>
    </body>
</html>



