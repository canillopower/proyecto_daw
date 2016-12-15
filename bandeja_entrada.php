<?php
// imports
require_once("auxiliar/GEST_CORREO.php");
require_once("auxiliar/DB.php");
require_once("auxiliar/Correo.php");
session_start();

// variables generales que se utilizaran en la pantalla
$correosLeidos = [];
$correosNoLeidos = [];
$correosEnviados = [];
$correosBorrados = [];
$otrosBuzones = [];
$usuario = null;
$erroresEnvioCorreo = [];

// control acciones
// como trabajamos osobre la bandeja de entrada, es indipensable que el correo nos
// llegue en la session
if (isset($_SESSION['CORREO']) && !empty($_SESSION['CORREO'])) {

    // recuperamos los datos del usuario por su correo
    $usuario = DB::recuperarUsuarioPorCorreo($_SESSION['CORREO']);
    
    
    // RESPONDER A UN CORREO
    if (isset($_POST['responderCorreo']) 
        && !empty($_POST['responderCorreo'])
        && isset($_POST['msgno'])
        && isset($_POST['bodyCorreo'])
        && isset($_POST['quienEnvio'])
        && isset($_POST['subject'])) {
        
        // tenemos que hace run substring, de la direcciónes de correo kano2 <>
        $simbolos = array("<", ">");
        $quienEnvio = str_replace($simbolos, "", substr($_POST['quienEnvio'], strpos($_POST['quienEnvio'], "<"), strpos($_POST['quienEnvio'], ">")));
        
        // respondo al correo 
        GEST_CORREO::responderCorreo(
                $usuario->getCorreo(),
                $usuario->getPassword(),
                $usuario->getNombre()." ".$usuario->getApe1(),
                $quienEnvio,
                $_POST['bodyCorreo'],
                "RE: ".$_POST['subject']);
    }
    
    // BORRAR CORREO
    if (isset($_POST['borrarEmail']) 
        && !empty($_POST['borrarEmail'])
        && isset($_POST['msgno'])) {
        
        // como no se borran literalmente, lo movemos a la carpeta de Thrash
        GEST_CORREO::moverCorreo(
                $usuario->getCorreo(),
                $usuario->getPassword(),
                $_POST['msgno'], 
                1);
        
        $tipoBandeja = 1;
        // borramos el correo de la carpeta origen
        GEST_CORREO::borrarCorreo(
                    $usuario->getCorreo(),
                    $usuario->getPassword(),
                    $_POST['msgno'],
                    null,
                    $tipoBandeja);
        }
        
    // MARCAR CORREO COMO LEIDO
    if (isset($_POST['marcarComoLeido']) 
        && !empty($_POST['marcarComoLeido'])
        && isset($_POST['msgno'])) {
        // marcamos el correo como leido
        GEST_CORREO::marcaCorreoLeido(
                 $usuario->getCorreo(),
                $usuario->getPassword(),
                $_POST['msgno']
                );
        }
        
    // CREAMOS LISTA DE DISTRIBUCIÓN ASOCIADA A UN USUARIO
    if (isset($_POST['crearListaDistri']) 
        && !empty($_POST['crearListaDistri'])) {
        // redireccionamos a la ventana de gestion de listas de distribucion
        header("Location: listas_distribucion.php");
    }    

    // BORRAMOS CORREOS DEFINITIVAMENTE
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
    
    // BORRAR SOBRE CARPETAS ADICIONALES
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

    // ENVIAR CORREO
    if (isset($_POST['correoEnviar']) && !empty($_POST['correoEnviar'])) {
        
        $para = null;
        $arrayDireccionesPara = null;
        $arrayDireccionesCC = null;
        $asunto = null;
        $texto = null;                
        // validamos que los correos son validas
        if (isset($_POST['correoPara']) 
                ||
                isset($_POST['listas_districomboPara'])) {
            
            // si viene para lo guardamos
            if (isset($_POST['correoPara']) && !empty($_POST['correoPara'])) {
                $arrayDireccionesPara[]  = $_POST['correoPara'];
            }
            
            // controlamos si se  ha seleccionado una lista de distribuccion
            if (isset($_POST['listas_districomboPara'])) {
                foreach ($usuario->getListaDistri() as $nombreLista => $arrayDirecciones) {
                    if ($nombreLista == $_POST['listas_districomboPara']) {
                         foreach ($arrayDirecciones as $direccion) {
                             $arrayDireccionesPara[] = $direccion;
                         }
                    }
                }
            } /*else {
                  $erroresEnvioCorreo[0] = "Debe seleccionar al menos una lista distribución"; 
            }*/
            
            //$arrayDireccionesParaAux = explode(";", $_POST['correoPara']);
            /*foreach ($arrayDireccionesParaAux as $direc) {
                $arrayDireccionesPara[] = $direc;
            }*/
            
            // validamos las direcciones de correo
            if (!isset($erroresEnvioCorreo[0]) && isset($_POST['listas_districomboPara']) && count($arrayDireccionesPara) > 0) {
                foreach ($arrayDireccionesPara as $direccion) {
                    if (!empty($direccion) && !filter_var($direccion, FILTER_VALIDATE_EMAIL)) {
                        $erroresEnvioCorreo[1] = "La dirección ".$direccion." no cumple el formato requerido"; 
                    }
                } 
            }
        } else {
            $erroresEnvioCorreo[0] = 'El campo para no puede estar vacio';  
        }
        if ($arrayDireccionesPara == null || empty($arrayDireccionesPara)) {
            $erroresEnvioCorreo[0] = 'Es necesario informar el campo para o seleccionar una lista distribucción';
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
                    $usuario->getNombre()." ".$usuario->getApe1(), 
                     $arrayDireccionesPara,
                     $arrayDireccionesCC,
                     $asunto,
                     $texto);
         } 
    }

    // ARCHIVAAR UN CORREO
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

    if (isset($_POST['deleteCarpeta']) &&
            isset($_POST['bandejaDelete'])) {
        
        $seen = null;
        if (isset($_POST['seen'])) {
            $seen = $_POST['seen'];
        }
        GEST_CORREO::borrarTodosCorreosPorBandeja(
                $usuario->getCorreo(),
                $usuario->getPassword(),
                $_POST['bandejaDelete'],
                $seen);    
        
    }
    
    // 1. Recibidos (IMBOx)
    // 2. Saliente (Sent)
    // 3. Basura (Trash)
    
    // RECUPERAR CORREOS
    //recupero nediante un array asociativa todos los correos
    $todosLosCorreos = 
            GEST_CORREO::recuperarCorreosPorBandeja(
                    $usuario->getCorreo(),
                    $usuario->getPassword());;
    
    // FILTRO
     if (isset($_POST['buscar']) 
        && !empty($_POST['buscar'])
        && !empty($_POST['check_list'])
        && isset($_POST['filtro_busqueda'])
        && !empty($_POST['filtro_busqueda'])) {
        unset($todosLosCorreos);
        $todosLosCorreos = GEST_CORREO::recuperarCorreosPorBandejaFiltrada(
                $usuario->getCorreo(),
                $usuario->getPassword(),
                $_POST['check_list'],
                $_POST['filtro_busqueda']);
    } 
  
    $bandejaEntrada =  null;
    $bandejaSalida =    null;
    $bandejaEliminado = null;
    
    // clasificamos los correos por bandeja
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

    if ($bandejaEntrada != null 
            && is_array($bandejaEntrada) 
            &&  count($bandejaEntrada) > 0) {
        
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

    if ($bandejaSalida != null 
            && is_array($bandejaSalida) 
            && count($bandejaSalida) > 0) {

        foreach ($bandejaSalida as $correoUsuario) {
            if ($correoUsuario != null) {
                $correosEnviados[$correoUsuario->getDatosCabecera()->msgno] = $correoUsuario;
            }
        }
    }

    if ($bandejaEliminado != null 
            && is_array($bandejaEliminado) 
            &&  count($bandejaEliminado) > 0) {

        foreach ($bandejaEliminado as $correoUsuario) {
            if ($correoUsuario != null) {
                $correosBorrados[$correoUsuario->getDatosCabecera()->msgno] = $correoUsuario;
            }
        }
    }
}

/**
 * Metodo encargado de montar la estructura HTML asociada a un correo dependiendo
 * de la bandeja a la que pertenece
 * @param type $correoUsuario Correo del usuario logeado en la aplicacion
 * @param type $tipoCorreo Tipo de correo que marca los botones que se mostraran en el contenedor
 * @param type $nombreCarpeta Nombre de la carpeta a la que pertenece el correo
 * @param type $tipoStilo Tipo de stilo a aplicar a cada correo para diferenciarlo
 */
  function montarMensaje($correoUsuario, $tipoCorreo, $nombreCarpeta, $tipoStilo) {
    // tipos correo
    //  NO LEIDO
    // LEIDO
    // BORRADO
    // ENVIADO
     if ($tipoStilo == 1) {
         echo "<div class ='divFormInterno1'>";
     } else {
         echo "<div class ='divFormInterno2'>";
     }

    $asunto = $correoUsuario->getDatosCabecera()->subject;
    if ($asunto == null || empty($asunto)) {
        $asunto = "No tiene asunto relacionado";
    }
    $de = $correoUsuario->getDatosCabecera()->from;
      
    $unixTimestamp=strtotime($correoUsuario->getDatosCabecera()->date);
    $fecha = date("Y-m-d H:i:s", $unixTimestamp);
    
    
    $cabecera = "<strong>Fecha:</strong> " . $fecha . ".<br/> <strong>De:</strong> " . $de . ".<br/> <strong>Asunto:</strong> " . $asunto;

    echo "<form class = 'formInterno' action='bandeja_entrada.php' method='post'>";
    // guardo los datos
    echo "<input type='hidden' name='msgno' value='" . $correoUsuario->getDatosCabecera()->msgno . "'>";
    echo "<input type='hidden' name='nombreCarpetaOrigen' value='" . $nombreCarpeta. "'>";
    
    echo '<label onclick="mostrar(\''.$nombreCarpeta . $correoUsuario->getDatosCabecera()->message_id . '\');" return false; />' . $cabecera . '</label> </br>';
    
    echo "<div id='".$nombreCarpeta . $correoUsuario->getDatosCabecera()->message_id . "' style='display:none'>";
    
     if ($tipoStilo == 1) {
         echo "<div class ='cuerpoEmail2'><p><strong>Cuerpo: </strong> ".$correoUsuario->getCuerpoEmail()."</p></div>";
     } else {
         echo "<div class ='cuerpoEmail1'><p><strong>Cuerpo: </strong> ".$correoUsuario->getCuerpoEmail()."</p></div>";
     }
    
    
    
    echo "<input class ='campoTextoMover' type='text' name='nombreCarpetaFinal'>";
    echo "<input class ='botonArc' type='submit' name='archivar' value='Archivar'>";
    
    if ($tipoCorreo == 1 || $tipoCorreo == 2) {
        echo "<input <input class ='botonDelete' type='submit' name='borrarEmail' value='Borrar'>";
        if ($tipoCorreo == 1) {
           echo "<input class ='botonLeido' type='submit' name='marcarComoLeido' value='Marcar leido'>";
        }
    } else if ($tipoCorreo == 3) {
        echo "<input class ='botonDelete2' type='submit' name='borrarEmailDefinitivo3' value='Borrar definitivamente'>";
    } else if ($tipoCorreo == 4) {
        echo "<input <input class ='botonDelete2'type='submit' name='borrarEmailDefinitivo4' value='Borrar definitivamente'>";
    } 
       
   
   
    echo "<br/>";

    // si es  no leido o leido,  se puede contestar,
    if ($tipoCorreo == 1 || $tipoCorreo == 2) {
        echo "<br/>";
        echo '<a onclick="mostrar(\'responder' . $correoUsuario->getDatosCabecera()->message_id . '\'); return false" />Pulsar para responder mensaje</a> </br>';
        echo "<br/>";
        echo "<div id='responder" . $correoUsuario->getDatosCabecera()->message_id . "' style='display:none'>";
        
        echo "<input type='hidden' name='quienEnvio' value='" . $correoUsuario->getDatosCabecera()->from . "'>";
        echo "<input type='hidden' name='subject' value='" . $correoUsuario->getDatosCabecera()->subject . "'>";
        echo "<label>Para:</label> <input class = 'campoTexto' type='text' name='correo' id='correo' maxlength='50' value = '".$correoUsuario->getDatosCabecera()->from."'/><br/>";
        echo "<br/>";
        echo "<textarea rows='4' cols='50' name='bodyCorreo'> Introducir texto mensaje...</textarea>";
        echo "<br/>";
        echo "<br/>";
        echo "<input class='boton1' type='submit' name='responderCorreo' value='Responder'>";
        echo "</div>";
    }


    echo "</div>";
    echo "</form>";
    echo "</br>";
    echo "</div>";
  
}

/**
 * Metodo encargado de montar el html asociado a los mensajes de la 
 * @param type $correoUsuario Correo del usuario logeado en la aplicacion
 * @param type $tipoCorreo Tipo de correo que marca los botones que se mostraran en el contenedor
 * @param type $tipoStilo Tipo de stilo a aplicar a cada correo para diferenciarlo
 */
function montarMensajeCarpetaAdiciona($correoUsuario, $nombreCarpeta, $tipoStilo) {
    // tipos correo
    //  NO LEIDO
    // LEIDO
    // BORRADO
    // ENVIADO
    if ($tipoStilo == 1) {
         echo "<div class ='divFormInterno1'>";
     } else {
         echo "<div class ='divFormInterno2'>";
     }
    $asunto = $correoUsuario->getDatosCabecera()->subject;
    if ($asunto == null || empty($asunto)) {
        $asunto = "No tiene asunto relacionado";
    }
    $de = $correoUsuario->getDatosCabecera()->from;
    
    $unixTimestamp=strtotime($correoUsuario->getDatosCabecera()->date);
    $fecha = date("Y-m-d H:i:s", $unixTimestamp);
    
    $cabecera = "<strong>Fecha:</strong> " . $fecha . ".<br/> <strong>De:</strong> " . $de . ".<br/> <strong>Asunto:</strong> " . $asunto;

    echo "<form class='formInterno' action='bandeja_entrada.php' method='post'>";
    // guardo los datos
    echo '<label>' . $cabecera . '</label> </br>';
    echo "<input type='hidden' name='msgno' value='" . $correoUsuario->getDatosCabecera()->msgno . "'>";
    echo "<input type='hidden' name='nombreCarpetaOrigen' value='" . $nombreCarpeta. "'>";
    
    echo "<input class ='campoTextoMover' type='text' name='nombreCarpetaFinal'>";
    echo "<input class ='botonArc' type='submit' name='archivar' value='Archivar'>";
    echo "<input <input class ='botonDelete2' type='submit' name='borrarEmailDefinitivo5' value='Borrar definitivamente'>";
    
    echo "</form>";
    echo "</br>";
    echo "</div>";
  
}

/**
 * Metodo encargado de mostrar el DIV con las listas de distribución del usuario logeado
 * @param type $usuario Usuario logeado
 * @param type $divId Identificado del div para ocultarlo o no
 */
function montarListaDistri($usuario, $divId) {
    echo "<div id='".$divId."' style='display: none'>";
     echo "<br/>";
    if ($usuario->getListaDistri() != null 
            && is_array($usuario->getListaDistri()) 
            && count($usuario->getListaDistri()) > 0) {
        // muestro el combo
        echo "<select class ='combo' name='listas_distri".$divId."' id='listas_distri".$divId."'>";
        echo "<option selected='selected' value = '0'>Seleccionar una lista...</option>";
        foreach ($usuario->getListaDistri() as $nombreLista => $arrayDirecciones) {
            echo "<option value = '" . $nombreLista . "'>" . $nombreLista . "</option>";
        }

        echo "</select> ";
        
    } else {
        echo "*** No tiene lista de distribución creada";
    }
    echo "<br/>";
    echo "<br/>";
    echo "<input class='boton1' type='submit' name='crearListaDistri' value='Gestionar listas distrubución'>";
    echo "</div>";
}
// SALIR ó MODIFICAR
if (isset($_POST['salir']) && !empty($_POST['salir'])) {
    header("Location: login.php");
    session_unset();
} else if (isset($_POST['modificar']) && !empty($_POST['modificar'])) {
    // guardamos en la session que el usuario quiere modificar sus datos
    $_SESSION['MODIFICAR'] = "1";
    header("Location: registro.php");
}

// ADMIN USUARIPS
if (isset($_POST['admin']) && !empty($_POST['admin'])) {
    header("Location: admin_usuarios.php");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!-- Desarrollo Web en Entorno Servidor -->
<!-- Autor: José María Rodríguez García -->
<!-- Proyecto : Gestor de correo electrónico -->
<!-- Ventana: bandeja_entrada.php Permite al usuario recuperar sus correos -->
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Kanomail.es > BANDEJA DE ENTRADA</title>
        <link href="css/comun.css" rel="stylesheet" type="text/css">
        <link href="css/correo.css" rel="stylesheet" type="text/css">
        <script type="application/javascript">
            var mostrar = function(id) {
            obj = document.getElementById(id);
            obj.style.display = (obj.style.display == 'none') ? 'block' : 'none';
            }

        </script>
    </head>

    <body>


        <div>
            <fieldset>
                <legend>Bandeja de entrada</legend>


                <?php
                echo "<h3>" . $usuario->getNombre() . " " . $usuario->getApe1() . " " . $usuario->getApe2() . "</h3>";
                ?>

                <form class ="formInterno" action='bandeja_entrada.php' method='post'>
                    <input class="boton1" type='submit' name='modificar' value='Modificar datos de usuario' />
                    <br/>    
                    <br/>    
                    <input class="boton1" type='submit' name='salir' value='Salir' />
                    <br/>    
                    <br/>
                       <?php
                // si es administador, puede volver a la bandeja de administracion
                if ("1" == $usuario->getTipo()) {
                    echo '<input class="boton1" type="submit" name="admin" value="Admin. usuarios" /> <br/> <br/>';
                }
                ?>
                    
                    <hr>
                    <label onclick="mostrar('ventana_envio'); return false" ><strong>Redactar mensaje</strong> </label><br/>    

                    <!-- ventana de envio de mensajes -->
                    <?php
                    if ($erroresEnvioCorreo != null && count($erroresEnvioCorreo) > 0) {
                        echo "<div id='ventana_envio' style='display: block'>";
                        echo "  <div class='error'> Se han producido errores de validación al intentar enviarlo, revise los campos </div>";
                        echo "</br>";
                    } else {
                        echo "<div id='ventana_envio' style='display: none'>";
                    }
                    ?>
                    <br/>
                    <table>
                        <tr>
                            <td> <label>De:  </label></td>
                            <td><input class ="campoTexto" type='text' name='correoDe' id='correoDe' maxlength="50" disabled = "true" value=" <?php echo $usuario->getCorreo() ?>"/></td>
                            
                        </tr>
                        <tr>
                            <td> <label>Para:</label></td>
                            <td> <input class ="campoTexto" type='text' name='correoPara' id='correoPara' maxlength="50"  <?php
                    if (isset($_POST['correoPara'])) {
                        echo "value = '" . $_POST['correoPara'] . "'";
                    }
                    ?> />
                         

                    <!-- OCULTAMOS LE DIV DEL COMBO DE DIRECCIONES -->
                    <label onclick="mostrar('comboPara'); return false" > + </label> <br/>
                      <?php
                           if (isset($erroresEnvioCorreo[0])) {
                               echo "<div class='error'>" . $erroresEnvioCorreo[0] . "</div>";
                           }
                           if (isset($erroresEnvioCorreo[1])) {
                               echo "<div class='error'>" . $erroresEnvioCorreo[1] . "</div>";
                           }
                           ?>
                   
                    <?php
                    echo montarListaDistri($usuario, "comboPara");
                    ?></td>
                        </tr>
                        
                        <tr>
                            <td> <label>CC:</label></td>
                            <td> <input class ="campoTexto" type='text' name='correoCC' id='correoCC' maxlength="50"  />
                    <!-- OCULTAMOS LE DIV DEL COMBO DE DIRECCIONES -->

                    <label onclick="mostrar('comboCC'); return false" > + </label>
                    <br/>
                    <?php
                    echo montarListaDistri($usuario, "comboCC");
                    ?>

                    <?php
                    if (isset($erroresEnvioCorreo[3])) {
                        echo "<div class='error'>" . $erroresEnvioCorreo[3] . "</div>";
                    }
                    ?></td>
                        </tr>
                        <tr>
                            <td><label>Asunto:</label></td>
                            <td> <input class ="campoTexto" type='text' name='correoAsunto' id='correoAsunto' maxlength="50"  /></td>
                        </tr>
                    </table>
                    <br/>
    
                    <textarea rows='4' cols='50' name='correoTexto'>Introducir texto mensaje </textarea>
                    <br/><br/>

                    <input class ="boton1" type='submit' name='correoEnviar' value='Enviar'>
                    <br/>
                    </div>
                    <hr>
                    <!-- mostramos panel de busquqeda -->
                    <!-- insertamos la tabla con los correos entrantes-->
                    <label onclick="mostrar('ventana_buscar'); return false" ><strong>Buscar mensajes</strong></label>
                    <div id='ventana_buscar' style='display: none'>
                        <table class="tablaFiltros">
                            <tr>
                                <td><input type="radio" name="check_list[]" value="checkDE"><label>DE</label></td>
                                <td><input type="radio" name="check_list[]" value="checkPARA"><label>PARA</label></td>
                            
                            </tr>
                            <tr>
                                <td><input type="radio" name="check_list[]" value="checkASUNTO"><label>ASUNTO</label><br/></td>
                                <td><input type="radio" name="check_list[]" value="checkTEXTO"><label>TEXTO</label><br/></td>
                            </tr>
                            <tr>
                                <td> <input type="radio" name="check_list[]" value="checkCC"><label>CC</label><br/></td>
                            </tr>
                        </table>

                        <input class ="campoTexto" type='text' name='filtro_busqueda'>
                        
                        <input class ="botonBuscar" type='submit' name='buscar' value='Buscar'>
                    </div>
                </form>     

                <hr>

                <p><strong>Bandeja de entrada</strong></p>
                <ul>

                    <li class ="lista" onclick="mostrar('correo_no_leido'); return false" >Nuevos: <?php echo count($correosNoLeidos); ?></li>
                    <li class ="lista" onclick="mostrar('correo_leido'); return false" >Recibidos:  <?php echo count($correosLeidos); ?> </li>
                    <li class ="lista" onclick="mostrar('enviados'); return false" >Enviados: <?php echo count($correosEnviados); ?></li>
                    <li class ="lista" onclick="mostrar('eliminados'); return false" >Eliminados: <?php echo count($correosBorrados); ?></li>
                    <?php
                    // mostramos la carpetas de manera dinamica
                    if (is_array($otrosBuzones) && count($otrosBuzones)) {
                        foreach ($otrosBuzones as $nombreBuzon => $correosDelBuzon) {
                            $elementosBuzon = count($correosDelBuzon) - 1;
                            if ($elementosBuzon == -1) {
                                $elementosBuzon = 0;
                            }
                            echo '<li class =\'lista\'  onclick="mostrar(\'buzon_' . $nombreBuzon . '\'); return false" >' . $nombreBuzon . ': ' . $elementosBuzon . '</li>';
                        }
                    }
                    ?>
                </ul>

                <!-- Por si quiero scroll <div id="Layer1" style="width:1000px; height:1150px; overflow: scroll;"> -->
                <div class="divNivel1" id="correo_no_leido" style="display: none">
                    <hr>
                    <form class ="formInterno" action='bandeja_entrada.php' method='post'>
                    <label><strong>Correo no leido</strong></label>
                    <input class="botonDeleteCarpeta" type='submit' name='deleteCarpeta' value='Acceder' />
                    <input type='hidden' name='bandejaDelete' value='INBOX' />
                    <input type='hidden' name='seen' value='0' /></br>
                    </form>
                    <?php
                    // CORREO NO LEIDO
                    $tipoStilo = 2;
                    foreach ($correosNoLeidos as $correoUsuario) {

                        echo montarMensaje($correoUsuario, 1, "INBOX", $tipoStilo);
                        if ($tipoStilo == 2) {
                            $tipoStilo = 1;
                        } else {
                            $tipoStilo = 2;
                        }
                    }
                    ?>
                </div>

                <div class="divNivel1" id="correo_leido"  style="display: none">
                    <hr>
                    <form class ="formInterno" action='bandeja_entrada.php' method='post'>
                    <label><strong>Correo leido</strong></label>
                    <input class="botonDeleteCarpeta" type='submit' name='deleteCarpeta' value='Acceder' />
                    <input type='hidden' name='bandejaDelete' value='INBOX' />
                    <input type='hidden' name='seen' value='1' /></br>
                    </form>
<?php
// CORREO LEIDO
$tipoStilo = 2;
foreach ($correosLeidos as $correoUsuario) {

    echo montarMensaje($correoUsuario, 2, "INBOX", $tipoStilo);
    if ($tipoStilo == 2) {
        $tipoStilo = 1;
    } else {
        $tipoStilo = 2;
    }
}
?>
                </div>

                <div class="divNivel1" id="eliminados" style="display: none">
                    <hr>
                    <form class ="formInterno" action='bandeja_entrada.php' method='post'>
                    <label><strong>Eliminados</strong></label>
                    <input class="botonDeleteCarpeta" type='submit' name='deleteCarpeta' value='Acceder' />
                    <input type='hidden' name='bandejaDelete' value='Trash' /></br>
<?php
// CORREO ELIMINADO
$tipoStilo = 2;
foreach ($correosBorrados as $correoUsuario) {

    echo montarMensaje($correoUsuario, 3, "Trash", $tipoStilo);
    if ($tipoStilo == 2) {
        $tipoStilo = 1;
    } else {
        $tipoStilo = 2;
    }
}
?>
                </div>

                <div class="divNivel1" id="enviados" style="display: none">
                    <hr>
                    <form class ="formInterno" action='bandeja_entrada.php' method='post'>
                    <label><strong>Enviados</strong></label>
                    <input class="botonDeleteCarpeta" type='submit' name='deleteCarpeta' value='Acceder' />
                    <input type='hidden' name='bandejaDelete' value='Sent' /></br>
<?php
// CORREO ENVIADO
$tipoStilo = 2;
foreach ($correosEnviados as $correoUsuario) {

    echo montarMensaje($correoUsuario, 4, "Sent", $tipoStilo);
    if ($tipoStilo == 2) {
        $tipoStilo = 1;
    } else {
        $tipoStilo = 2;
    }
}
?>
                </div>

                <!--resto de buzones -->
                    <?php

                    if ($otrosBuzones != null && is_array($otrosBuzones) && count($otrosBuzones)) {
                        foreach ($otrosBuzones as $nombreBuzon => $correosDelBuzon) {
                            echo ' <div class=\'divNivel1\' id="buzon_' . $nombreBuzon . '" style="display: none">';
                            echo ' <hr>';
                            
                            echo "<form class ='formInterno' action='bandeja_entrada.php' method='post'>"
                                ."<label><strong>".$nombreBuzon."  </strong></label>"
                                ."<input class='botonDeleteCarpeta' type='submit' name='deleteCarpeta' value='Acceder' />"
                                ."<input type='hidden' name='bandejaDelete' value='".$nombreBuzon."' /></br>";

                            //echo ' <label><strong>' . $nombreBuzon . '</strong></label>';
                            if (count($correosDelBuzon) > 0) {
                                $tipoStilo == 2;
                                foreach ($correosDelBuzon as $correoUsuario) {
                                    if ($correoUsuario != null) {
                                        if ($tipoStilo == 2) {
                                            $tipoStilo = 1;
                                        } else {
                                            $tipoStilo = 2;
                                        }
                                        echo montarMensajeCarpetaAdiciona($correoUsuario, $nombreBuzon, $tipoStilo);
                                        //echo montarMensaje($correoUsuario, 4, $nombreBuzon, $tipoStilo);
                                    }
                                }
                            }
                            echo ' </div>';
                        }
                    }
                    ?>
            </fieldset>
        </div>
    </body>
</html>