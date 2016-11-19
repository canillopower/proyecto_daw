<?php
require_once("auxiliar/GEST_CORREO.php");
require_once("auxiliar/DB.php");
require_once("auxiliar/Correo.php");
session_start();

// recuperamos los correos del usuario

$correosLeidos = [];
$correosNoLeidos = [];
$correosEnviados = [];
$correosBorrados = [];
$usuario = null;
if (isset($_SESSION['CORREO']) && !empty($_SESSION['CORREO'])) {

    $usuario = DB::recuperarUsuarioPorCorreo($_SESSION['CORREO']);
    
    if (isset($_POST['responderCorreo']) 
        && !empty($_POST['responderCorreo'])
        && isset($_POST['msgno'])
        && isset($_POST['bodyCorreo'])
        && isset($_POST['quienEnvio'])
        && isset($_POST['subject'])) {
        
        // tenemos que hace run substring, de la direcciónes de correo kano2 <>

        $simbolos = array("<", ">");
        $quienEnvio = str_replace($simbolos, "", substr($_POST['quienEnvio'], strpos($_POST['quienEnvio'], "<"), strpos($_POST['quienEnvio'], ">")));
        
        GEST_CORREO::responderCorreo(
                $usuario->getCorreo(),
                $usuario->getPassword(),
                $quienEnvio,
                $_POST['bodyCorreo'],
                "RE: ".$_POST['subject']);
    }
    
    // si ha pulsado sobre borrar correo
    if (isset($_POST['borrarEmail']) 
        && !empty($_POST['borrarEmail'])
        && isset($_POST['msgno'])) {
        GEST_CORREO::moverCorreo(
                $usuario->getCorreo(),
                $usuario->getPassword(),
                $_POST['msgno'], 
                1);
        $tipoBandeja = 1;
        GEST_CORREO::borrarCorreo(
                    $usuario->getCorreo(),
                    $usuario->getPassword(),
                    $_POST['msgno'],
                    null,
                    $tipoBandeja);
        }
    // marcarComoLeido
    if (isset($_POST['marcarComoLeido']) 
        && !empty($_POST['marcarComoLeido'])
        && isset($_POST['msgno'])) {
        GEST_CORREO::marcaCorreoLeido(
                 $usuario->getCorreo(),
                $usuario->getPassword(),
                $_POST['msgno']
                );
        }
        // si ha pulsado sobre borrar correo defintiivmante
    if ((isset($_POST['borrarEmailDefinitivo3']) && !empty($_POST['borrarEmailDefinitivo3'])) ||
            ((isset($_POST['borrarEmailDefinitivo4']) && !empty($_POST['borrarEmailDefinitivo4'])))) {
        $bandeja = 0;
        if (isset($_POST['borrarEmailDefinitivo3'])) {
            $bandeja = 3;
        }

        if (isset($_POST['borrarEmailDefinitivo4'])) {
            $bandeja = 2;
        }

        if (isset($_POST['msgno']) || isset($_POST['message_id'])) {
            $msgno = null;
            $message_id = null;
            
            if (isset($_POST['msgno'])) {
                $msgno = $_POST['msgno'];
            }
            
            if (isset($_POST['message_id'])) {
                $message_id = $_POST['message_id'];
            }
            GEST_CORREO::borrarCorreo(
                    $usuario->getCorreo(),
                    $usuario->getPassword(),
                    $msgno,
                    $message_id,
                    $bandeja);
        }
    }



    // 1. Recibidos (IMBOx)
    // 2. Saliente (Sent)
    // 3. Basura (Trash)
    $bandejaEntrada =   GEST_CORREO::recuperarCorreo($usuario->getCorreo(), $usuario->getPassword(), 1);
    $bandejaSalida =    GEST_CORREO::recuperarCorreo($usuario->getCorreo(), $usuario->getPassword(), 2);
    $bandejaEliminado = GEST_CORREO::recuperarCorreo($usuario->getCorreo(), $usuario->getPassword(), 3);

    //clasificamos los correos
    // 1 LEIDO
    // 2 NO LEIDO
    // 3 BORRADO
    // 4 ENVIADO
    // 
    // Seen, \Answered, \Flagged, \Deleted, y \Draft 

    if ($bandejaEntrada != null && count($bandejaEntrada) > 0) {
        foreach ($bandejaEntrada as $correoUsuario) {

            if ($correoUsuario->getDatosCabecera()->seen == 0) {
                $correosNoLeidos[$correoUsuario->getDatosCabecera()->msgno] = $correoUsuario;
            } elseif ($correoUsuario->getDatosCabecera()->seen == 1) {
                $correosLeidos[$correoUsuario->getDatosCabecera()->msgno] = $correoUsuario;
            }
        }
    }

    if ($bandejaSalida != null && count($bandejaSalida) > 0) {
        foreach ($bandejaSalida as $correoUsuario) {
            $correosEnviados[$correoUsuario->getDatosCabecera()->msgno] = $correoUsuario;
        }
    }

    if ($bandejaEliminado != null && count($bandejaEliminado) > 0) {
        foreach ($bandejaEliminado as $correoUsuario) {
            $correosBorrados[$correoUsuario->getDatosCabecera()->msgno] = $correoUsuario;
        }
    }
}

  function montarMensaje($correoUsuario, $tipoCorreo) {
    // tipos correo
    //  NO LEIDO
    // LEIDO
    // BORRADO
    // ENVIADO

    $htmlEmail = '';
    $asunto = $correoUsuario->getDatosCabecera()->subject;
    if ($asunto == null || empty($asunto)) {
        $asunto = "No tiene asunto relacionado";
    }
    $de = $correoUsuario->getDatosCabecera()->from;
    $fecha = $correoUsuario->getDatosCabecera()->date;
    $cabecera = "Fecha: " . $fecha . ". Enviado por " . $de . ". con el asunto " . $asunto;

    echo "<form action='bandeja_entrada.php' method='post'>";
    // guardo los datos
    echo "<input type='hidden' name='msgno' value='" . $correoUsuario->getDatosCabecera()->msgno . "'>";
    
    
    echo '<label onclick="mostrar(\'' . $correoUsuario->getDatosCabecera()->message_id . '\');" return false; />' . $cabecera . '</label> </br>';
    
    echo "<div id='" . $correoUsuario->getDatosCabecera()->message_id . "' style='display:none'>";
    echo $correoUsuario->getCuerpoEmail();
    echo "</br>";
    if ($tipoCorreo == 1 || $tipoCorreo == 2) {
        echo "<input type='submit' name='borrarEmail' value='Borrar'>";
        if ($tipoCorreo == 1) {
           echo "<input type='submit' name='marcarComoLeido' value='Marcar leido'>";
        }
    } else if ($tipoCorreo == 3) {
        echo "<input type='submit' name='borrarEmailDefinitivo3' value='Borrar definitivamente'>";
    } else if ($tipoCorreo == 4) {
        echo "<input type='submit' name='borrarEmailDefinitivo4' value='Borrar definitivamente'>";
    }

    // si es  no leido o leido,  se puede contestar,
    if ($tipoCorreo == 1 || $tipoCorreo == 2) {
        echo '<label onclick="mostrar(\'responder' . $correoUsuario->getDatosCabecera()->message_id . '\'); return false" />Pulsar para responder mensaje</label> </br>';
        
        echo "<div id='responder" . $correoUsuario->getDatosCabecera()->message_id . "' style='display:none'>";
        
        echo "<input type='hidden' name='quienEnvio' value='" . $correoUsuario->getDatosCabecera()->from . "'>";
        echo "<input type='hidden' name='subject' value='" . $correoUsuario->getDatosCabecera()->subject . "'>";
        echo "<label>Para:</label> <input type='text' name='correo' id='correo' maxlength='50' value = '".$correoUsuario->getDatosCabecera()->from."'/><br/>";
        echo "<textarea rows='4' cols='50' name='bodyCorreo'> Introducir texto mensaje";
        echo "</textarea>";
        echo "<input type='submit' name='responderCorreo' value='Responder'>";
        echo "</div>";
    }


    echo "</div>";
    echo "</form>";
    echo "</br>";
    return $htmlEmail;
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

         <script type="application/javascript">
            var mostrar = function(id) {
                obj = document.getElementById(id);
                obj.style.display = (obj.style.display == 'none') ? 'block' : 'none';
            }

        </script>
    </head>

    <body>
        <div>
           


<?php
echo $usuario->getNombre() . " " . $usuario->getApe1() . " " . $usuario->getApe2();
?>

                <input type='submit' name='modificar' value='Modificar datos de usuario' />
                <input type='submit' name='salir' value='Salir' />

                <!-- insertamos la tabla con los correos entrantes-->
                <p>Estado correos (pulsar para ver bandeja)</p>
                <ul>
                    <li onclick="mostrar('correo_no_leido'); return false" >Nuevos: <?php echo count($correosNoLeidos); ?></li>
                    <li onclick="mostrar('correo_leido'); return false" >Recibidos:  <?php echo count($correosLeidos); ?> </li>
                    <li onclick="mostrar('enviados'); return false" >Enviados: <?php echo count($correosEnviados); ?></li>
                    <li onclick="mostrar('eliminados'); return false" >Eliminados: <?php echo count($correosBorrados); ?></li>
                </ul>



                <!-- Por si quiero scroll <div id="Layer1" style="width:1000px; height:1150px; overflow: scroll;"> -->
                <div id="correo_no_leido" style="display: none">
                    <label><strong>Correo no leido</strong></label></br>
                    <?php
                    // CORREO NO LEIDO
                    foreach ($correosNoLeidos as $correoUsuario) {
                      echo montarMensaje($correoUsuario, 1);
                    }
                    ?>
                </div>

                <div id="correo_leido" style="display: none">
                    <label><strong>Correo leido</strong></label></br>
                    <?php
                    // CORREO LEIDO
                    foreach ($correosLeidos as $correoUsuario) {
                            echo montarMensaje($correoUsuario, 2);
                    }
                    ?>
                </div>

                <div id="eliminados" style="display: none">
                    <label><strong>Eliminados</strong></label></br>
                    <?php
                    // CORREO ELIMINADO
                    foreach ($correosBorrados as $correoUsuario) {
                       echo montarMensaje($correoUsuario, 3);
                    }
                    ?>
                </div>

                <div id="enviados" style="display: none">
                    <label><strong>Enviados</strong></label></br>
                    <?php
                    // CORREO ENVIADO
                    foreach ($correosEnviados as $correoUsuario) {
                     echo montarMensaje($correoUsuario, 4);
                    }
                    ?>
                </div>

        </div>
    </body>
</html>