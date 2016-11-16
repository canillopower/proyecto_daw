<?php

require_once("auxiliar/DB.php");
session_start();

// recuperamos los correos del usuario

$correosLeidos = [];
$correosNoLeidos = [];
$correosEnviados = [];
$correosBorrador = [];
$usuario = null;
if (isset($_SESSION['CORREO']) && !empty($_SESSION['CORREO'])) {
    $usuario = DB::recuperarUsuarioPorCorreo($_SESSION['CORREO']);
    
    $correos = DB::recuperarCorreosPorCorreo($_SESSION['CORREO']);
    
    if ($correos != null && count($correos) > 0) {
        foreach ($correos as $correoUsuario) {
            
            //clasificamos los correos
            // 1 LEIDO
            // 2 NO LEIDO
            // 3 BORRADO
            // 4 ENVIADO
            
            if ($correoUsuario->getIdEstadoCorreo() == 1) {
                $correosLeidos[$correoUsuario->getIdCorreo()] =  $correoUsuario;
            } elseif ($correoUsuario->getIdEstadoCorreo() == 2) {
                $correosNoLeidos[$correoUsuario->getIdCorreo()] =  $correoUsuario;
            } elseif ($correoUsuario->getIdEstadoCorreo() == 3) {
                $correosBorrador[$correoUsuario->getIdCorreo()] =  $correoUsuario;
            } elseif ($correoUsuario->getIdEstadoCorreo() == 4) {
                $correosEnviados[$correoUsuario->getIdCorreo()] =  $correoUsuario;
            }
        }
    }
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
         <script>
        function mostrar(id) {
          obj = document.getElementById(id);
         obj.style.display = (obj.style.display == 'none') ? 'block' : 'none';
        }

        </script>
    </head>

    <body>
        <div>
            <form action='bandeja_entrada.php' method='post'>
                

                <?php
                echo $usuario->getNombre() . " " . $usuario->getApe1() . " " . $usuario->getApe2();
                ?>
                
                <input type='submit' name='modificar' value='Modificar datos de usuario' />
                <input type='submit' name='salir' value='Salir' />

                <!-- insertamos la tabla con los correos entrantes-->
                <p>Estado correos (pulsar para ver bandeja)</p>
                <ul>
                    <li onclick="mostrar('correo_leido'); return false" >Nuevos:  <?php echo count($correosLeidos); ?> </li>
                    <li onclick="mostrar('correo_no_leido'); return false" >Recibidos: <?php echo count($correosNoLeidos); ?></li>
                    <li onclick="mostrar('enviados'); return false" >Enviados: <?php echo count($correosBorrador); ?></li>
                    <li onclick="mostrar('borradores'); return false" >Borradores: <?php echo count($correosEnviados); ?></li>
                </ul>
            
       
           
           <!-- Por si quiero scroll <div id="Layer1" style="width:1000px; height:1150px; overflow: scroll;"> -->
           <div id="correo_no_leido" style="display: none">
               <label><strong>Correo no leido</strong></label></br>
            <?php   
                // CORREO no LEIDO
                foreach ($correosNoLeidos  as $correoLeido) {
                    echo '<label onclick="mostrar(\''.$correoLeido->getIdCorreo().'\'); return false" />'.$correoLeido->getAsunto().'</label>';
                    echo '<label>'.$correoLeido->getFechaEnvio().'</label>';
                    //echo $correoLeido->getAsunto();
                    echo "<div id='".$correoLeido->getIdCorreo()."' style='display:none'>";
                    echo $correoLeido->getContenido();
                    // hay que añadir le boton de enviar
                    echo "</div>";
                    echo "</br>";   
                }
            ?>
            </div>
           
           <div id="correo_leido" style="display: none">
               <label><strong>Correo leido</strong></label></br>
            <?php   
                // CORREO LEIDO
                foreach ($correosLeidos as $correoLeido) {
                    echo '<label  onclick="mostrar(\''.$correoLeido->getIdCorreo().'\'); return false" />'.$correoLeido->getAsunto().'</label>';
                    echo '<label>              '.$correoLeido->getFechaEnvio().'</label>';
                    //echo $correoLeido->getAsunto();
                    echo "<div id='".$correoLeido->getIdCorreo()."' style='display:none'>";
                    echo $correoLeido->getContenido();
                    // hay que añadir le boton de enviar
                    echo "</div>";
                    echo "</br>";   
                }
            ?>
            </div>
           
           <div id="borradores" style="display: none">
               <label><strong>Borradores</strong></label></br>
            <?php   
                // CORREO LEIDO
                foreach ($correosBorrador  as $correoLeido) {
                    echo '<label onclick="mostrar(\''.$correoLeido->getIdCorreo().'\'); return false" />'.$correoLeido->getAsunto().'</label>';
                    echo '<label>'.$correoLeido->getFechaEnvio().'</label>';
                    //echo $correoLeido->getAsunto();
                    echo "<div id='".$correoLeido->getIdCorreo()."' style='display:none'>";
                    echo $correoLeido->getContenido();
                    // hay que añadir le boton de enviar
                    echo "</div>";
                    echo "</br>";   
                }
            ?>
            </div>
           
           <div id="enviados" style="display: none">
               <label><strong>Enviados</strong></label></br>
            <?php   
                // CORREO LEIDO
                foreach ($correosEnviados  as $correoLeido) {
                    echo '<label onclick="mostrar(\''.$correoLeido->getIdCorreo().'\'); return false" />'.$correoLeido->getAsunto().'</label>';
                    echo '<label>'.$correoLeido->getFechaEnvio().'</label>';
                    //echo $correoLeido->getAsunto();
                    echo "<div id='".$correoLeido->getIdCorreo()."' style='display:none'>";
                    echo $correoLeido->getContenido();
                    // hay que añadir le boton de enviar
                    echo "</div>";
                    echo "</br>";   
                }
            ?>
            </div>
           
                </form>
        </div>
    </body>
</html>