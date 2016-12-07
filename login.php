<?php
// imports
require_once("auxiliar/DB.php");

// variables generales que se utilizaran en la pantalla
$errorOperacion = '';
$errores = [];

// control acciones
// ENVIAR
if (isset($_POST['enviar'])) {
    // si se envia el formulario, debemos comprobar si los datos del usuario son correctos
    if (isset($_POST['correo']) && isset($_POST['password'])) {

        // validaciones de los campos
        if (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[0] = 'El formato de la dirección de correo no es correcto';
        }

        if ($_POST['password'] == '') {
            $errores[1] = 'La contraseña no puede estar vacia';
        }

        // si no se han producido errrores continuamos con la operación
        if (count($errores) == 0) {

            $usuario = DB::recuperarUsuario(
                            $_POST['correo'], $_POST['password']);

            // si le encontramos en la BBDD vamos si
            // - Rol 2(USER)  y estado 2 (ACTIVO) > ir bandeja de entrada
            // - Rol 2(USER)  y estado 1 (INACTIVO) > mostrar popup pendiente alta
            // - Rol 1(ADMIN)   > ir ventana gestión
            // si no lo encontramos, mostrar alert de REGISTRO
            if ($usuario != null) {
                // guardamos en la session los datos del usuario
                session_start();
                $_SESSION['ID_USUARIO'] = $usuario->getId();
                $_SESSION['CORREO'] = $usuario->getCorreo();
                $_SESSION['PASSWORD'] = $usuario->getPassword();
                if ("2" == $usuario->getTipo()) {
                    if ("1" == $usuario->getEstado()) {
                        $errorOperacion = "Lo sentimos, pero su petición de alta esta siendo procesada en estos momentos.";
                    } else if ("2" == $usuario->getEstado()) {
                        // guardamos los datos en session y vamos a la ventana de correo
                        header("Location: bandeja_entrada.php");
                        //header("Location: listas_distribucion.php");
                    }
                } else if ("1" == $usuario->getTipo()) {
                    header("Location: admin_usuarios.php");
                }
            } else {
                $errorOperacion = "El usuario no existe en nuestra base de datos";
            }
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!-- Desarrollo Web en Entorno Servidor -->
<!-- Autor: José María Rodríguez García -->
<!-- Proyecto : Gestor de correo electrónico -->
<!-- Ventana: login.php Permite al usuario logearse o registrarse en la aplicación -->
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Kanomail.es > LOGIN</title>
        <link href="css/comun.css" rel="stylesheet" type="text/css">

    </head>

    <body>
        <div id='login'>
            <form action='login.php' method='post'>
                <fieldset>
                    <legend>Login</legend>

                    <div>
                        <label for='correo' >Correo:</label><br/>
                        <input class="campoTexto" type='text' name='correo' id='correo' maxlength="50" /><br/>
<?php
if (isset($errores[0])) {
    echo "<label>***" . $errores[0] . "</label>";
}
?>
                    </div>
                    <br/>
                    <div>
                        <label for='password' >Contraseña:</label><br/>
                        <input class="campoTexto" type='password' name='password' id='password' maxlength="50" /><br/>
<?php
if (isset($errores[1])) {
    echo "<label>***" . $errores[1] . "</label>";
}
?>
                    </div>
                    <div>
                        <p><a href="registro.php" >Registrarse</a></p>
                    </div>
                    <div>
                        <input class="boton1" type='submit' name='enviar' value='Acceder' />
                    </div>
                    <div >
                        <label><?php echo $errorOperacion; ?></label><br/>
                    </div>  
                </fieldset>
            </form>
        </div>
    </body>
</html>



