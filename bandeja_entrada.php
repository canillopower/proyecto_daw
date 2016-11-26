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
$otrosBuzones = [];
$usuario = null;
$erroresEnvioCorreo = [];
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
        
        // si se desea crea lista distribución vamos a la la pantalla de listas
    if (isset($_POST['crearListaDistri']) 
        && !empty($_POST['crearListaDistri'])) {
        header("Location: listas_distribucion.php");
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
    
    // se ha pulsado borrar sobre carpeta adicional
    if (isset($_POST['borrarEmailDefinitivo5']) && !empty($_POST['borrarEmailDefinitivo5'])
             && isset($_POST['nombreCarpetaOrigen']) && !empty($_POST['nombreCarpetaOrigen'])) {
        
           $msgno = null;
            $message_id = null;
            
            if (isset($_POST['msgno'])) {
                $msgno = $_POST['msgno'];
            }
            
            if (isset($_POST['message_id'])) {
                $message_id = $_POST['message_id'];
            }
            
         GEST_CORREO::borrarCorreoCarpetaAdicional(
                    $usuario->getCorreo(),
                    $usuario->getPassword(),
                    $msgno,
                    $message_id,
                    $_POST['nombreCarpetaOrigen']);
    }

    //  enviar correo
    if (isset($_POST['correoEnviar']) 
        && !empty($_POST['correoEnviar'])) {
        
        $para = null;
        $arrayDireccionesPara = null;
        $arrayDireccionesCC = null;
        $asunto = null;
        $texto = null;
                
        // validamos que los correos son validas
        
        // validamos los datos
        if (isset($_POST['correoPara']) 
            && !empty($_POST['correoPara'])) {
            
            // controlamos si se  ha seleccionado una lista de distribuccion
            if (isset($_POST['listas_districomboPara'])) {
                foreach ($usuario->getListaDistri() as $nombreLista => $arrayDirecciones) {
                    if ($nombreLista == $_POST['listas_districomboPara']) {
                         foreach ($arrayDirecciones as $direccion) {
                             $arrayDireccionesPara[] = $direccion."; ";
                         }
                    }
                }
            }
            
            $arrayDireccionesParaAux = explode("; ", $_POST['correoPara']);
            foreach ($arrayDireccionesParaAux as $direc) {
                $arrayDireccionesPara[] = $direc;
            }
            
            if (count($arrayDireccionesPara) > 0) {
                foreach ($arrayDireccionesPara as $direccion) {
                    if (!filter_var($direccion, FILTER_VALIDATE_EMAIL)) {
                        $erroresEnvioCorreo[1] = "La dirección ".$direccion." no cumple el formato requerido"; 
                    }
                } 
            }
        } else {
            $erroresEnvioCorreo[0] = 'El campo para no puede estar vacio';  
        }
        
        if (isset($_POST['correoCC']) 
            && !empty($_POST['correoCC'])) {
            
            // si la lista esta vacia, el para puede ir vacio
            unset($erroresEnvioCorreo[0]);
            
              // controlamos si se  ha seleccionado una lista de distribuccion
            if (isset($_POST['listas_districomboCC'])) {
                foreach ($usuario->getListaDistri() as $nombreLista => $arrayDirecciones) {
                    if ($nombreLista == $_POST['listas_districomboCC']) {
                         foreach ($arrayDirecciones as $direccion) {
                             $arrayDireccionesCC[] = $direccion."; ";
                         }
                    }
                }
            }
            
            $arrayDireccionesCCAux = explode("; ", $_POST['correoCC']);
            foreach ($arrayDireccionesCCAux as $direc) {
                $arrayDireccionesCC[] = $direc;
            }
            
            if (count($arrayDireccionesCC) > 0) {
                foreach ($arrayDireccionesCC as $direccion) {
                    if (!filter_var($direccion, FILTER_VALIDATE_EMAIL)) {
                        $erroresEnvioCorreo[3] = "La dirección ".$direccion." no cumple el formato requerido"; 
                    }
                } 
            }
        }
        
        if (isset($_POST['correoAsunto']) 
            && !empty($_POST['correoAsunto'])) {
            $asunto = $_POST['correoAsunto'];
        }
        
        if (isset($_POST['correoTexto']) 
            && !empty($_POST['correoTexto'])) {
            $texto = $_POST['correoTexto'];
        }
            
        // si no hay errores, mandamos el correo
         if (count($erroresEnvioCorreo) == 0) {
             GEST_CORREO::enviarCorreo(
                    $usuario->getCorreo(),
                    $usuario->getPassword(),
                     $arrayDireccionesPara,
                     $arrayDireccionesCC,
                     $asunto,
                     $texto);
         } 
    }

    
    if (isset($_POST['archivar'])
            && isset($_POST['nombreCarpetaOrigen']) && !empty($_POST['nombreCarpetaOrigen'])
            && isset($_POST['nombreCarpetaFinal']) && !empty($_POST['nombreCarpetaFinal'])) {
        
        if (isset($_POST['msgno']) || isset($_POST['message_id'])) {
            GEST_CORREO::moverCorreoCarpeta(
                    $usuario->getCorreo(),
                    $usuario->getPassword(),
                    $_POST['msgno'], 
                    $_POST['nombreCarpetaOrigen'],
                    $_POST['nombreCarpetaFinal']);
        }
    }

    // 1. Recibidos (IMBOx)
    // 2. Saliente (Sent)
    // 3. Basura (Trash)
    
    // recupero nediante un array asociativa todos los correos
    $todosLosCorreos = GEST_CORREO::recuperarCorreosPorBandeja($usuario->getCorreo(), $usuario->getPassword());
    
    
   /* $bandejaEntrada =   GEST_CORREO::recuperarCorreo($usuario->getCorreo(), $usuario->getPassword(), 1);
    $bandejaSalida =    GEST_CORREO::recuperarCorreo($usuario->getCorreo(), $usuario->getPassword(), 2);
    $bandejaEliminado = GEST_CORREO::recuperarCorreo($usuario->getCorreo(), $usuario->getPassword(), 3);
*/
    $bandejaEntrada =  null;
    $bandejaSalida =    null;
    $bandejaEliminado = null;
    
    foreach ($todosLosCorreos as $nombreBuzon => $correosDelBuzon) {
        // gestiono las carpetas basicas
        if ("INBOX" == $nombreBuzon) {
            $bandejaEntrada = $correosDelBuzon;
        } elseif ("Sent" == $nombreBuzon) {
            $bandejaSalida = $correosDelBuzon;
        } elseif ("Trash" == $nombreBuzon) {
            $bandejaEliminado = $correosDelBuzon;
        } else {
            $otrosBuzones[$nombreBuzon] = $correosDelBuzon;
        }
        
        
    }
    /*
    $bandejaEntrada =   GEST_CORREO::recuperarCorreo($usuario->getCorreo(), $usuario->getPassword(), 1);
    $bandejaSalida =    GEST_CORREO::recuperarCorreo($usuario->getCorreo(), $usuario->getPassword(), 2);
    $bandejaEliminado = GEST_CORREO::recuperarCorreo($usuario->getCorreo(), $usuario->getPassword(), 3);
    */
    //clasificamos los correos
    // 1 LEIDO
    // 2 NO LEIDO
    // 3 BORRADO
    // 4 ENVIADO
    // 
    // Seen, \Answered, \Flagged, \Deleted, y \Draft 

    if ($bandejaEntrada != null && is_array($bandejaEntrada) &&  count($bandejaEntrada) > 0) {
        foreach ($bandejaEntrada as $correoUsuario) {
            if ($correoUsuario != null) {
                if ($correoUsuario->getDatosCabecera()->seen == 0) {
                    $correosNoLeidos[$correoUsuario->getDatosCabecera()->msgno] = $correoUsuario;
                } elseif ($correoUsuario->getDatosCabecera()->seen == 1) {
                    $correosLeidos[$correoUsuario->getDatosCabecera()->msgno] = $correoUsuario;
                }
            }
            
        }
    }

    if ($bandejaSalida != null && is_array($bandejaSalida) && count($bandejaSalida) > 0) {

        foreach ($bandejaSalida as $correoUsuario) {
            if ($correoUsuario != null) {
                $correosEnviados[$correoUsuario->getDatosCabecera()->msgno] = $correoUsuario;
            }
        }
    }

    if ($bandejaEliminado != null && is_array($bandejaEliminado) &&  count($bandejaEliminado) > 0) {

        foreach ($bandejaEliminado as $correoUsuario) {
            if ($correoUsuario != null) {
                $correosBorrados[$correoUsuario->getDatosCabecera()->msgno] = $correoUsuario;
            }
        }
    }
}

  function montarMensaje($correoUsuario, $tipoCorreo, $nombreCarpeta) {
    // tipos correo
    //  NO LEIDO
    // LEIDO
    // BORRADO
    // ENVIADO

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
    echo "<input type='hidden' name='nombreCarpetaOrigen' value='" . $nombreCarpeta. "'>";
    
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
       
    
    echo "<input type='submit' name='archivar' value='Archivar'>";
    echo "<input type='text' name='nombreCarpetaFinal'>";
    

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
  
}

function montarMensajeCarpetaAdiciona($correoUsuario, $nombreCarpeta) {
    // tipos correo
    //  NO LEIDO
    // LEIDO
    // BORRADO
    // ENVIADO

    $asunto = $correoUsuario->getDatosCabecera()->subject;
    if ($asunto == null || empty($asunto)) {
        $asunto = "No tiene asunto relacionado";
    }
    $de = $correoUsuario->getDatosCabecera()->from;
    $fecha = $correoUsuario->getDatosCabecera()->date;
    $cabecera = "Fecha: " . $fecha . ". Enviado por " . $de . ". con el asunto " . $asunto;

    echo "<form action='bandeja_entrada.php' method='post'>";
    // guardo los datos
    echo '<label>' . $cabecera . '</label> </br>';
    echo "<input type='hidden' name='msgno' value='" . $correoUsuario->getDatosCabecera()->msgno . "'>";
    echo "<input type='hidden' name='nombreCarpetaOrigen' value='" . $nombreCarpeta. "'>";
    
    echo "<input type='submit' name='borrarEmailDefinitivo5' value='Borrar definitivamente'>";
    
    echo "<input type='submit' name='archivar' value='Archivar'>";
    echo "<input type='text' name='nombreCarpetaFinal'>";
 
    echo "</form>";
    echo "</br>";
  
}


function montarListaDistri($usuario, $divId) {
    echo "<div id='".$divId."' style='display: none'>";
    if ($usuario->getListaDistri() != null 
            && is_array($usuario->getListaDistri()) 
            && count($usuario->getListaDistri()) > 0) {
        // muestro el combo
        echo "<select name='listas_distri".$divId."' id='listas_distri".$divId."'>";
        echo "<option selected='selected' value = '0'>Seleccionar una lista...</option>";
        foreach ($usuario->getListaDistri() as $nombreLista => $arrayDirecciones) {
            echo "<option value = '" . $nombreLista . "'>" . $nombreLista . "</option>";
        }

        echo "</select> ";
        
    } else {
        echo "*** No tiene lista de distribución creada";
    }
    echo "<input type='submit' name='crearListaDistri' value='Gestionar listas distrubución'>";
    echo "</div>";
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
            
            <form action='bandeja_entrada.php' method='post'>
                <input type='submit' name='modificar' value='Modificar datos de usuario' />
                <input type='submit' name='salir' value='Salir' />
                <br/>    
                <label onclick="mostrar('ventana_envio'); return false" >Redactar mensaje </label>
                
                    <?php if ($erroresEnvioCorreo != null && count($erroresEnvioCorreo) > 0) {
                        echo "<div id='ventana_envio' style='display: block'>";
                        echo " *** Se han producido errores de validación al intentar enviarlo, revise los campos";
                        echo "</br>";
                    } else {
                        echo "<div id='ventana_envio' style='display: none'>";
                    }
                    ?>
                 
                     <label>De:  </label>
                     <input type='text' name='correoDe' id='correoDe' maxlength="50" disabled = "true" value=" <?php echo $usuario->getCorreo()?>"/>
                      <br/>
                     <label>Para:</label>
                     <input type='text' name='correoPara' id='correoPara' maxlength="50"  <?php 
                        if (isset($_POST['correoPara'])) {
                            echo "value = '".$_POST['correoPara']."'";
                        }
                     ?> />
                      <?php
                            if (isset($erroresEnvioCorreo[0])) {
                                echo "<label>***" . $erroresEnvioCorreo[0] . "</label>";
                            }
                            if (isset($erroresEnvioCorreo[1])) {
                                echo "<label>***" . $erroresEnvioCorreo[1] . "</label>";
                            }
                            ?>
                     
                     <!-- OCULTAMOS LE DIV DEL COMBO DE DIRECCIONES -->
                     <label onclick="mostrar('comboPara'); return false" > + </label>
                     
                     <?php 
                        echo montarListaDistri($usuario, "comboPara");
                     ?>
                      <br/>
                     <label>CC:</label>
                     <input type='text' name='correoCC' id='correoCC' maxlength="50"  />
                     <!-- OCULTAMOS LE DIV DEL COMBO DE DIRECCIONES -->
                     <label onclick="mostrar('comboCC'); return false" > + </label>
                     <?php 
                        echo montarListaDistri($usuario, "comboCC");
                     ?>
                     
                      <?php
                            if (isset($erroresEnvioCorreo[3])) {
                                echo "<label>***" . $erroresEnvioCorreo[3] . "</label>";
                            }

                            ?>
                      <br/>
                     <label>Asunto:</label>
                     <input type='text' name='correoAsunto' id='correoAsunto' maxlength="50"  />
                       <br/>
                       <textarea rows='4' cols='50' name='correoTexto'>Introducir texto mensaje </textarea>
                     <br/>
                     <input type='submit' name='correoEnviar' value='Enviar'>
                </div>

            </form>     
                <!-- insertamos la tabla con los correos entrantes-->
                <p>Estado correos (pulsar para ver bandeja)</p>
                <ul>
                    
                    <li onclick="mostrar('correo_no_leido'); return false" >Nuevos: <?php echo count($correosNoLeidos); ?></li>
                    <li onclick="mostrar('correo_leido'); return false" >Recibidos:  <?php echo count($correosLeidos); ?> </li>
                    <li onclick="mostrar('enviados'); return false" >Enviados: <?php echo count($correosEnviados); ?></li>
                    <li onclick="mostrar('eliminados'); return false" >Eliminados: <?php echo count($correosBorrados); ?></li>
                    <?php 
                        // mostramos la carpetas de manera dinamica
                        if (is_array($otrosBuzones) && count($otrosBuzones)) {
                            foreach ($otrosBuzones as $nombreBuzon => $correosDelBuzon) {
                                $elementosBuzon = count($correosDelBuzon) - 1;
                                echo '<li onclick="mostrar(\'buzon_' . $nombreBuzon . '\'); return false" >'.$nombreBuzon.' : '.$elementosBuzon.'</li>';
                            }
                        }
                    
                    ?>
                </ul>



                <!-- Por si quiero scroll <div id="Layer1" style="width:1000px; height:1150px; overflow: scroll;"> -->
                <div id="correo_no_leido" style="display: none">
                    <label><strong>Correo no leido</strong></label></br>
                    <?php
                    // CORREO NO LEIDO
                    foreach ($correosNoLeidos as $correoUsuario) {
                      echo montarMensaje($correoUsuario, 1, "INBOX");
                    }
                    ?>
                </div>

                <div id="correo_leido" style="display: none">
                    <label><strong>Correo leido</strong></label></br>
                    <?php
                    // CORREO LEIDO
                    foreach ($correosLeidos as $correoUsuario) {
                            echo montarMensaje($correoUsuario, 2, "INBOX");
                    }
                    ?>
                </div>

                <div id="eliminados" style="display: none">
                    <label><strong>Eliminados</strong></label></br>
                    <?php
                    // CORREO ELIMINADO
                    foreach ($correosBorrados as $correoUsuario) {
                       echo montarMensaje($correoUsuario, 3, "Trash");
                    }
                    ?>
                </div>

                <div id="enviados" style="display: none">
                    <label><strong>Enviados</strong></label></br>
                    <?php
                    // CORREO ENVIADO
                    foreach ($correosEnviados as $correoUsuario) {
                     echo montarMensaje($correoUsuario, 4, "Sent");
                    }
                    ?>
                </div>
                
                 <!--resto de buzones -->
                    <?php
                    // CORREO ENVIADO
                    if (is_array($otrosBuzones) && count($otrosBuzones)) {
                            foreach ($otrosBuzones as $nombreBuzon => $correosDelBuzon) {
                                echo ' <div id="buzon_'.$nombreBuzon.'" style="display: none">';
                                echo ' <label><strong>'.$nombreBuzon.'</strong></label></br>';
                               
                                foreach ($correosDelBuzon as $correoUsuario) {
                                    if ($correoUsuario != null) {
                                        echo montarMensajeCarpetaAdiciona($correoUsuario, $nombreBuzon);
                                    }
                                    
                                }
                                echo ' </div>';
                                
                            }
                        }
                    ?>

        </div>
    </body>
</html>