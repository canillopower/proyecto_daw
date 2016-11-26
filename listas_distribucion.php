<?php
require_once("auxiliar/GEST_CORREO.php");
require_once("auxiliar/DB.php");
require_once("auxiliar/Correo.php");
session_start();

// recuperamos los correos del usuario


$usuario = null;
$erroresAltaLista = [];
$erroresBorrarLista = [];
$erroresAltaElementoLista = [];
$erroresBajaElementoLista = [];
if (isset($_SESSION['CORREO']) && !empty($_SESSION['CORREO'])) {

    $usuario = DB::recuperarUsuarioPorCorreo($_SESSION['CORREO']);
    
    // si se quiere añadir lista
    if (isset($_POST['aniadirLista']) && !empty($_POST['aniadirLista'])) {
        
        if (isset($_POST['nombreLista'])) {
            if (!empty($_POST['nombreLista'])) {
                $resultadoOP = DB::insertarListaDistribucion($usuario->getId(), ($_POST['nombreLista']));
                if ($resultadoOP == 1) {
                    $erroresAltaLista[1] = "***Ya existe una lista con ese mismo nombre";
                } elseif ($resultadoOP == 2) {
                    $erroresAltaLista[2] = "***No se ha podido insertar la lista";
                }   
            } else {
                // si esta vacio debo monstrar error
                $erroresAltaLista[0] = "***El nombre de la lista no puede estar vacio";
            }
        }
    }
    
     if (isset($_POST['borrarLista']) && !empty($_POST['borrarLista'])) {
        
        if (isset($_POST['nombreListaBorrar']) && !empty($_POST['nombreListaBorrar'])) {
            
            $resultadoOP = DB::borrarListaDistribucion($usuario->getId(), ($_POST['nombreListaBorrar']));
            if ($resultadoOP == 1) {
                 $erroresBorrarLista[1] = "***No se han podido borrar las direcciones asociadas";
            } elseif ($resultadoOP == 2) {
                $erroresBorrarLista[2] = "***No se ha podido borrar la lista";
            }   
             
        }
    }
    
     // si se quiere añadir elemento a la lista lista
    if (isset($_POST['aniadirElementoLista']) && !empty($_POST['aniadirElementoLista'])) {
        
        if (isset($_POST['nombreElementoLista'])) {
            if (!empty($_POST['nombreElementoLista'])) {
                // valido que sea una direccion valida de correo
                if (filter_var($_POST['nombreElementoLista'], FILTER_VALIDATE_EMAIL)) {
                     $resultadoOP = DB::insertarElementoListaDistribucion($_POST['nombreElementoLista'], $_POST['nombreListaPadreAniadir']);
                    if ($resultadoOP == 1) {
                        $erroresAltaElementoLista[1] = "***Ya existe esa dirección en la lista";
                    } elseif ($resultadoOP == 2) {
                        $erroresAltaElementoLista[2] = "***No se ha podido insertar la direccion";
                    }   
                } else {
                    $erroresAltaElementoLista [3] = "*** No es una direccion valida";
                }

            } else {
                // si esta vacio debo monstrar error
                $erroresAltaLista[0] = "***El nombre de la lista no puede estar vacio";
                
            }
        }
    }
    // borramos elemento de la lista
    if (isset($_POST['borrarElementoLista']) && !empty($_POST['borrarElementoLista'])) {
        
        if (isset($_POST['elementoListaBorrar']) && !empty($_POST['elementoListaBorrar'])
                && isset($_POST['nombreListaPadreBorrar']) && !empty($_POST['nombreListaPadreBorrar'])) {
            
            $resultadoOP = DB::borrarElementoListaDistribucion($_POST['elementoListaBorrar'], $_POST['nombreListaPadreBorrar']);
            if ($resultadoOP == 2) {
                $erroresBajaElementoLista[2] = "***No se ha podido borrar la direccion";
            }   
        }
    }
    
    $usuario = DB::recuperarUsuarioPorCorreo($_SESSION['CORREO']);
}

//acciones
// - Modificar, reenvia al usuario a la ventana de registro y permite modificar los datos
// del usuasrio
// - Salir, redirige a la ventana de Login y borra los datos de session
if (isset($_POST['salir']) && !empty($_POST['salir'])) {
    header("Location: login.php");
    session_unset();
} else if (isset($_POST['volver']) && !empty($_POST['volver'])) {
    // guardamos en la session que el usuario quiere modificar sus datos
    header("Location: bandeja_entrada.php");
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

<?php
echo "<p>".$usuario->getNombre() . " " . $usuario->getApe1() . " " . $usuario->getApe2()."</p>";
echo "<p> Gestión listas de distribucion"
?>
            
        <form action='listas_distribucion.php' method='post'>
                <input type='submit' name='volver' value='Volver bandeja de entrada' />
                <input type='submit' name='salir' value='Salir' />
        </form>
                <br/>    
                <!-- debo pintar las lista de ditribución dle usuasrio-->
                <?php 
                    if ($usuario->getListaDistri() != null && count($usuario->getListaDistri()) > 0) {
                        foreach ($usuario->getListaDistri() as $nombreLista => $elementosLista) {
                            echo "<form action='listas_distribucion.php' method='post'>";
                            echo "<input type='hidden' name='nombreListaBorrar' value='".$nombreLista."' />";
                            echo '<label onclick="mostrar(\'elementos' . $nombreLista . '\'); return false">'.$nombreLista.'</label>';
                            echo "<input type='submit' name='borrarLista' value='Borrar lista' /></br>";
                            echo "</form>";
                            
                            if (isset($_POST['aniadirElementoLista']) 
                                    && !empty($_POST['aniadirElementoLista'])
                                    && isset($_POST['nombreListaPadreAniadir']) 
                                    && !empty($_POST['nombreListaPadreAniadir'])
                                    && $_POST['nombreListaPadreAniadir'] == $nombreLista) {
                                echo "<div id='elementos" .$nombreLista. "'   style='display:block'>";
                                
                            } elseif (isset($_POST['borrarElementoLista']) 
                                    && !empty($_POST['borrarElementoLista'])
                                    && isset($_POST['nombreListaPadreBorrar']) 
                                    && !empty($_POST['nombreListaPadreBorrar'])
                                    && $_POST['nombreListaPadreBorrar'] == $nombreLista) {
                                echo "<div id='elementos" .$nombreLista. "'   style='display:block'>";
                            } else {
                                echo "<div id='elementos" .$nombreLista. "'   style='display:none'>";
                            }
                            
                            
                            //echo "<div id='elementos" .$nombreLista. "'   style='display:none'>";
                            // mostramos los subelementos
                            if (is_array($elementosLista) && count ($elementosLista) > 0) {
                                 foreach ($elementosLista as $elementoLista) {
                                    echo "<form action='listas_distribucion.php' method='post'>";
                                    echo "<input type='hidden' name='nombreListaPadreBorrar' value='".$nombreLista."' />";
                                    echo "<input type='hidden' name='elementoListaBorrar' value='".$elementoLista."' />";
                                    echo '<label >'.$elementoLista.'</label>';
                                    echo "<input type='submit' name='borrarElementoLista' value='Borrar' /></br>";
                                    
                                    
                                    echo "</form>";
                                }
                                
                                
                            }
                           
                             echo "<form action='listas_distribucion.php' method='post'>";
                             echo "<input type='hidden' name='nombreListaPadreAniadir' value='".$nombreLista."' />";
                            
                             echo "<input type='text' name='nombreElementoLista'/>";
                           if (isset($erroresAltaElementoLista[0])) {
                                echo $erroresAltaElementoLista[0];
                            }
                            echo "<input type='submit' name='aniadirElementoLista' value='Añadir direccion correo' />";
                 
                            if (isset($erroresAltaElementoLista[1])) {
                                echo "<p>".$erroresAltaElementoLista[1]."</p>";
                            }

                            if (isset($erroresAltaElementoLista[2])) {
                                echo "<p>".$erroresAltaElementoLista[2]."</p>";
                            }    
                            
                            if (isset($erroresAltaElementoLista[3])) {
                                echo "<p>".$erroresAltaElementoLista[3]."</p>";
                            }
                            
                            if (isset($erroresBajaElementoLista[1])) {
                                  echo "<p>".$erroresBajaElementoLista[1]."</p>";
                            }

                            if (isset($erroresBajaElementoLista[2])) {
                                echo "<p>".$erroresBajaElementoLista[2]."</p>";
                            }
                    echo "</form>";
                            echo "</div>";
                        }
                    }
                    
                    if (isset($erroresBorrarLista[1])) {
                         echo "<p>".$erroresBorrarLista[1]."</p>";
                    }

                    if (isset($erroresBorrarLista[2])) {
                        echo "<p>".$erroresBorrarLista[2]."</p>";
                    }
                    
                ?>
                
                
                 <form action='listas_distribucion.php' method='post'>
                <input type='text' name='nombreLista'/>
                <?php 
                    if (isset($erroresAltaLista[0])) {
                        echo $erroresAltaLista[0];
                    }
                ?>
                <input type='submit' name='aniadirLista' value='Añadir lista' />
                 <?php 
                    if (isset($erroresAltaLista[1])) {
                        echo "<p>".$erroresAltaLista[1]."</p>";
                    }
                    
                    if (isset($erroresAltaLista[2])) {
                        echo "<p>".$erroresAltaLista[2]."</p>";
                    }
                ?>
                 </form>
         
        
    </body>
</html>