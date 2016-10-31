<?php
// añadimos los require necesario
require_once("auxiliar/DB.php");
require_once("auxiliar/Usuario.php");
require_once("auxiliar/CORREO.php");

$error = [];
$errorOperacion ="";
$datos = [];
session_start();

if (isset($_POST['salir'])) {
      header("Location: login.php");
}

if (isset($_POST['activar']) && isset($_POST['id_usuario']) && isset($_POST['correo_usuario'])) {
    // si la operacion es activar, tenemos que cambiar el etado del usuario en BBDD
    $datos["ID_USUARIO"] = $_POST['id_usuario'];
    $datos["ID_ESTADO_USUARIO"] = "2"; // cambiamos de inactivo 1 a 2 activo
    DB::insertarOactualizarUsuario("UPDATE", $datos);
/*
    CORREO::enviarCorreoBienvenida(
            $_SESSION['ID_USUARIO'],
            $_SESSION['PASSWORD'],
            $_POST['correo_usuario'],
            "activada");
    */
    unset($_POST['activar']);
    unset($_POST['id_usuario']);
    unset($_POST['correo_usuario']);

} else if (isset($_POST['desactivar']) && isset($_POST['id_usuario']) && isset($_POST['correo_usuario'])){
        // si la operacion es activar, tenemos que cambiar el etado del usuario en BBDD
    $datos["ID_USUARIO"] = $_POST['id_usuario'];
    $datos["ID_ESTADO_USUARIO"] = "1"; // cambiamos de inactivo 1 a 2 activo
    DB::insertarOactualizarUsuario("UPDATE", $datos);
  /*
     CORREO::enviarCorreoBienvenida(
            $_SESSION['ID_USUARIO'],
            $_SESSION['PASSWORD'],
            $_POST['correo_usuario'],
            "desactivada");
     */
    unset($_POST['desactivar']);
    unset($_POST['id_usuario']);
    unset($_POST['correo_usuario']);
}




// cargamos los usuarios de BBDD pendientes de alta
$usuariosPteAlta = null;
if (isset($_POST['filtrar']) 
        && ((isset($_POST['nombre']) && !empty($_POST['nombre']) )
                || 
            (isset($_POST['estado']) && !empty($_POST['estado'])))
           ) {
    $usuariosPteAlta = DB::recuperarUsuarioPorNombreEstado($_POST['nombre'], $_POST['estado']);
    
} else  {
    $usuariosPteAlta = DB::recuperarTodosUsuarios();
}

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
            <form action='admin_usuarios.php' method='post'>
                <fieldset>
                    <div>  
                    <legend>Adminitracion usuarios</legend>
                   
                    <fieldset>
                        
                    <legend>Filtrar usuario</legend>

                    <div>
                        
                        <label for='nombre' >Nombre de usuario:</label><br/>
                        <input type='text' name='nombre' id='nombre' maxlength="50" /><br/>
                        
                        Estado:
                        <input type="radio" name="estado" <?php if (isset($estado) && $estado == "activo") echo "checked"; ?> value="2">Activo
                        
                        <input type="radio" name="estado" <?php if (isset($estado) && $estado == "inactivo") echo "checked"; ?> value="1">Inactivo
                        </br>
                        <input type='submit' name='filtrar' value='Filtrar' />
                    </div>

                </fieldset>
                     </form>
                    <table>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th> 
                            <th>Activar/Desactivar</th>    
                        </tr>
                   
                    <?php 
                        if (isset($usuariosPteAlta) && count($usuariosPteAlta) >= 0) {
                            foreach ($usuariosPteAlta as $usuario) {

                                $nombre = $usuario->getNombre()." ".$usuario->getApe1()." ".$usuario->getApe2();
                                echo "<tr>";
                                echo "<td>".$nombre."</td>";
                                echo "<td>".$usuario->getCorreo()."</td>";
                                // si estado inactivo solo puedo 
                               
                                echo "<input type='hidden' name='id_usuario' value = '".$usuario->getId()."'/>";
                                echo "<input type='hidden' name='correo_usuario' value = '".$usuario->getCorreo()."'/>";
                                if ("1" == $usuario->getEstado()) {    
                                    echo "<td><input type='submit' name='activar' value='Activar' /></td>";
                                    echo "<td><input disabled ='true' type='submit' name='desactivar' value='Desactivar' /></td>";
                                } else if ("2" == $usuario->getEstado()) {
                                    echo "<td><input disabled ='true' type='submit' name='activar' value='Activar' /></td>";
                                    echo "<td><input type='submit' name='desactivar' value='Desactivar' /></td>";
                                }
                    
                                echo "</tr>";
                            }
                        }
                    ?>
                    </table>
            
                        <input type='submit' name='salir' value='Salir' />
                    </div>                   
                </fieldset>
            </form>
        </div>
    </body>
</html>



