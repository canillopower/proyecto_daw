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

// CAMBIAR ESTADO USUARIO
if (isset($_POST['cambiarEstado']) && !empty($_POST['cambiarEstado'])) {

    // hacemos split de los valores ya que ajax nos envia un unico elemento

    $valores = split(",", $_POST['cambiarEstado']);

    if (is_array($valores) && isset($valores[0]) && count($valores) == 3) {

        $nuevoEstado = null;
        if ($valores[0] == "activada") {
            $datos["ID_ESTADO_USUARIO"] = "2"; // cambiamos de inactivo 1 a 2 activo
        } else {
            $datos["ID_ESTADO_USUARIO"] = "1"; // cambiamos de activo 2 a 1 inactivo
        }

        // actualizamos el estado en BBDD
        $datos["ID_USUARIO"] = $valores[1];

        DB::insertarOactualizarUsuario("UPDATE", $datos);

        GEST_CORREO::enviarCorreoBienvenida(
                $_SESSION['ID_USUARIO'], $_SESSION['PASSWORD'], $valores[2], "activada");

        unset($_POST['cambiarEstado']);
    }
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

        </script>
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
                        <input class="boton2" type='submit' name='filtrar' value='Filtrar' />
                    </div>

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
                            <th>Inactivo - Activo</th>    
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
            $var = 'activada,' . $usuario->getId() . ',' . $usuario->getCorreo();
            echo '<label onclick="cambiarEstado(\'' . $var . '\');" return false; id="labelCheck" name="labelCheck" class="switch" >
                                    <input type="checkbox" >
                                    <div class="slider round"></div>
                                  </label>';
        } elseif ("2" == $usuario->getEstado()) {
            $var = 'desactivar,' . $usuario->getId() . ',' . $usuario->getCorreo();
            echo '<label onclick="cambiarEstado(\'' . $var . '\');" return false; id="labelCheck" name="labelCheck" class="switch" >
                                    <input type="checkbox" checked>
                                    <div class="slider round"></div>
                                  </label>';
        }
        echo "</tr>";
        echo "</form>";
    }
}
?>
                    </table>
                    <form action='admin_usuarios.php' method='post'>
                        <input class="boton1" type='submit' name='salir' value='Salir' />
                    </form>
                </div>                   
            </fieldset>
        </form>
    </div>
</body>
</html>



