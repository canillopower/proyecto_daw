<?php
// añadimos los require necesario
require_once("auxiliar/DB.php");

// control acciones
if (isset($_POST['enviar'])) {
    // si se envia el formulario, debemos comprobar si los datos del usuario son correctos
    if (isset($_POST['correo']) && isset($_POST['password'])) {
        $usuario = DB::recuperarUsuario(
                        $_POST['correo'],
                        $_POST['password']);

        // si le encontramos en la BBDD vamos si
        // - Rol 1(USER)  y estado 2 (ACTIVO) > ir bandeja de entrada
        // - Rol 1(USER)  y estado 1 (INACTIVO) > mostrar popup pendiente alta
        // - Rol 2(USER)   > ir ventana gestión
        // si no lo encontramos, mostrar alert de REGISTRO
        if ($usuario != null) {
            if ("1" == $usuario->getTipo()) {
                $error = "Lo sentimos, pero su petición de alta esta siendo procesada en estos momentos.";
            } else if ("2" == $usuario->getTipo()) {
                // guardamos los datos en session y vamos a la ventana de correo
                session_start();
                $_SESSION['ID_USUARIO'] = $usuario->getId();
                $_SESSION['CORREO'] = $usuario->getCorreo();
                header("Location: bandeja_entrada.php");
            }
        } else {
            $error = "Lo sentimos, pero su petición esta siendo procesada en estos momentos.";
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!-- Desarrollo Web en Entorno Servidor -->
<!-- Autor: José María Rodríguez García -->
<!-- Proyecto : Gestor de correo electrónico -->
<!-- Ventna: login.php Permite al usuario logearse o registrarse en la aplicación -->
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Ejemplo Tema 5: Login Tienda Web</title>
        <link href="tienda.css" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div id='login'>
            <form action='login.php' method='post'>
                <fieldset >
                    <legend>Login</legend>

                    <div>
                        <label for='correo' >Correo:</label><br/>
                        <input type='text' name='correo' id='correo' maxlength="50" /><br/>
                    </div>
                    <div>
                        <label for='password' >Contraseña:</label><br/>
                        <input type='password' name='password' id='password' maxlength="50" /><br/>
                    </div>
                    <div>
                        <a href="registro.php" >Registrarse</a>
                    </div>
                    <div>
                        <input type='submit' name='enviar' value='Enviar' />
                    </div>
                    <div >
                        <label><?php echo $error; ?></label><br/>
                    </div>  
                </fieldset>
            </form>
        </div>
    </body>
</html>



