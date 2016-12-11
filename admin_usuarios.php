<?php
// imports
require_once("auxiliar/DB.php");
require_once("auxiliar/Usuario.php");
require_once("auxiliar/GEST_CORREO.php");

// variables generales que se utilizaran en la pantalla
$error = [];
$errorOperacion = "";
$datos = [];
session_start();
$usuariosPteAlta = null;

// control acciones
// SALIR
if (isset($_POST['salir'])) {
    header("Location: logoff.php");
}

// IR CORREO
if (isset($_POST['correo'])) {
    header("Location: bandeja_entrada.php");
}

// CAMBIAR ESTADO USUARIO
/*if (isset($_POST['cambiarEstado']) && !empty($_POST['cambiarEstado']) && (!isset($_SESSION['estadoCambiado']) || $_SESSION['estadoCambiado'] == 0)) {

    // hacemos split de los valores ya que ajax nos envia un unico elemento

    $valores = split(",", $_POST['cambiarEstado']);

    if (is_array($valores) && isset($valores[0]) && count($valores) == 3) {

        $nuevoEstado = null;
        if ($valores[0] == "activada") {
            $datos["ID_ESTADO_USUARIO"] = "2"; // cambiamos de inactivo 1 a 2 activo
            $nuevoEstado = "activada";
        } else {
            $datos["ID_ESTADO_USUARIO"] = "1"; // cambiamos de activo 2 a 1 inactivo
             $nuevoEstado = "desactivada";
        }

        // actualizamos el estado en BBDD
        $datos["ID_USUARIO"] = $valores[1];

        DB::insertarOactualizarUsuario("UPDATE", $datos);

        GEST_CORREO::enviarCorreoBienvenida(
                $_SESSION['ID_USUARIO'], $_SESSION['PASSWORD'], $valores[2], $nuevoEstado);

        $_SESSION['estadoCambiado'] = 1;
        unset($_POST['cambiarEstado']);
    }
} else {
    if (isset($_SESSION['estadoCambiado'])) {
        $_SESSION['estadoCambiado'] = 0;
    }
}*/

if (isset($_POST['cambiarEstado']) && !empty($_POST['cambiarEstado'])
        && isset($_POST['correo_usuario']) && !empty($_POST['correo_usuario'])
        && isset($_POST['correo_pass']) && !empty($_POST['correo_pass'])
        && isset($_POST['id_usuario']) && !empty($_POST['id_usuario'])
        && isset($_SESSION['ID_USUARIO']) && isset($_SESSION['PASSWORD']) && isset($_SESSION['CORREO'])) {
        
        $nuevoEstado = null;
        if ($_POST['cambiarEstado'] == "Activar") {
            $datos["ID_ESTADO_USUARIO"] = "2"; // cambiamos de inactivo 1 a 2 activo
            $nuevoEstado = "activada";
        } else {
            $datos["ID_ESTADO_USUARIO"] = "1"; // cambiamos de activo 2 a 1 inactivo
             $nuevoEstado = "desactivada";
        }
       
        $datos["ID_USUARIO"] = $_POST['id_usuario'];
       
        // actualizamos los datos en la BBDD
        DB::insertarOactualizarUsuario("UPDATE", $datos);
        
        $para[] = $_POST['correo_usuario'];
        // enviamos correo de bienvenida
        GEST_CORREO::enviarCorreo(
                $_SESSION['CORREO'],
                $_SESSION['PASSWORD'], 
                "Admin users",
                $para,
                null,
                "Alta/Baja usuarios",
                "Buenos días, su cuenta de correo ha sido ".$nuevoEstado );
       
}

// cargamos los usuarios de BBDD pendientes de alta
// FILTRAR USUARIOS
if (isset($_POST['filtrar']) && ((isset($_POST['nombre']) && !empty($_POST['nombre']) ) ||
        (isset($_POST['estado']) && !empty($_POST['estado'])))
) {
    $nombre = null;
    $estado = null;
    if (isset($_POST['nombre']) && !empty($_POST['nombre'])) {
        $nombre = $_POST['nombre'];
    }

    if (isset($_POST['estado']) && !empty($_POST['estado'])) {
        $estado = $_POST['estado'];
    }
    $usuariosPteAlta = DB::recuperarUsuarioPorNombreEstado($nombre, $estado);
} else {
    // si no se han incluido filtro, recuperamos todos los usuarios
    $usuariosPteAlta = DB::recuperarTodosUsuarios();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!-- Desarrollo Web en Entorno Servidor -->
<!-- Autor: José María Rodríguez García -->
<!-- Proyecto : Gestor de correo electrónico -->
<!-- Ventana: admin_usuarios.php Permite al administrador gestionar los usuarios -->
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Kanomail.es > ADMIN USUARIOS</title>
        <link href="css/comun.css" rel="stylesheet" type="text/css">
        <link href="css/admin.css" rel="stylesheet" type="text/css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<!--
        <script type="application/javascript">


            var cambiarEstado = function(valores) {
            jQuery.ajax({
            type: "POST",
            url: 'admin_usuarios.php',
            data: {"cambiarEstado": valores}, 
            success:function(data) {

            }
            });

            }

        </script>-->
    </head>

    <body>
        <div id='registro'>
            <form  action='admin_usuarios.php' method='post'>
                <fieldset>
                    <legend>Filtrar usuario</legend>

                    <div>

                        <label for='nombre' >Nombre de usuario:</label><br/>
                        <input class="campoTexto" type='text' name='nombre' id='nombre' maxlength="50"  value = "
                        <?php
                        if (isset($_POST['nombre']) && !empty($_POST['nombre'])) {
                            echo $_POST['nombre'];
                        }
                        ?>"/><br/>

                        Estado:
                        <input type="radio" name="estado" 
                        <?php
                        if (isset($estado) && $estado == 2)
                            echo "checked";
                        ?> value="2">Activo</imput>

                        <input type="radio" name="estado" 
                            <?php 
                            if (isset($estado) && $estado == 1) echo "checked"; 
                            ?> value="1">Inactivo</imput>
                        </br>
                        <input class="botonBuscar" type='submit' name='filtrar' value='Filtrar' />
                    </div>
                    <br/>
                    <input class="boton1" type='submit' name='correo' value='Ir buzón correo' />
                    <br/><br/>
                        <input class="boton1" type='submit' name='salir' value='Salir' />
                        
                </fieldset>
                        
            </form>
            <fieldset >
                <legend>Adminitracion usuarios</legend>
                <div>  

                    <table>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th> 
                            <th>Password</th> 
                            <th>Activar - Desactivar</th>    
                        </tr>

<?php
if (isset($usuariosPteAlta) && count($usuariosPteAlta) >= 0) {
    foreach ($usuariosPteAlta as $usuario) {
        echo "<form action='admin_usuarios.php' method='post'>";
        $nombre = $usuario->getNombre() . " " . $usuario->getApe1() . " " . $usuario->getApe2();
        echo "<tr>";
        echo "<td>" . $nombre . "</td>";
        echo "<td>" . $usuario->getCorreo() . "</td>";
        echo "<td>" . $usuario->getPassword() . "</td>";
        // si estado inactivo solo puedo 

        echo "<input type='hidden' name='id_usuario' value = '" . $usuario->getId() . "'/>";
        echo "<input type='hidden' name='correo_usuario' value = '" . $usuario->getCorreo() . "'/>";
        echo "<input type='hidden' name='correo_pass' value = '" . $usuario->getPassword() . "'/>";

        echo "<td>";

        if ("1" == $usuario->getEstado()) {
            
            echo "<input class='boton1' type='submit' name='cambiarEstado' value='Activar' />";
            /*$var = 'activada,' . $usuario->getId() . ',' . $usuario->getCorreo();
            echo '<label onclick="cambiarEstado(\'' . $var . '\');" return false; id="labelCheck" name="labelCheck" class="switch" >
                                    <input type="checkbox" >
                                    <div class="slider round"></div>
                                  </label>';*/
        } elseif ("2" == $usuario->getEstado()) {
            
            echo "<input class='boton1' type='submit' name='cambiarEstado' value='Desactivar' />";
           /* $var = 'desactivar,' . $usuario->getId() . ',' . $usuario->getCorreo();
            echo '<label onclick="cambiarEstado(\'' . $var . '\');" return false; id="labelCheck" name="labelCheck" class="switch" >
                                    <input type="checkbox" checked>
                                    <div class="slider round"></div>
                                  </label>';*/
        }
        echo "</tr>";
        echo "</form>";
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



