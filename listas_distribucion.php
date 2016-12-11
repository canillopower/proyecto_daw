<?php
// imports
require_once("auxiliar/GEST_CORREO.php");
require_once("auxiliar/DB.php");
require_once("auxiliar/Correo.php");
session_start();

// variables generales que se utilizaran en la pantalla
$usuario = null;
$erroresAltaLista = [];
$erroresBorrarLista = [];
$erroresAltaElementoLista = [];
$erroresBajaElementoLista = [];

// control acciones
// como trabajamos osobre la bandeja de entrada, es indipensable que el correo nos
// llegue en la session
if (isset($_SESSION['CORREO']) && !empty($_SESSION['CORREO'])) {

    $usuario = DB::recuperarUsuarioPorCorreo($_SESSION['CORREO']);

    // AÑADIR ELEMENTO A LISTA
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

    // BORRAR LISTA
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

    // AÑADIR CORREO A LISTA
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
    // BORRAR CORREO DE LA LISTA
    if (isset($_POST['borrarElementoLista']) && !empty($_POST['borrarElementoLista'])) {

        if (isset($_POST['elementoListaBorrar']) && !empty($_POST['elementoListaBorrar']) && isset($_POST['nombreListaPadreBorrar']) && !empty($_POST['nombreListaPadreBorrar'])) {

            $resultadoOP = DB::borrarElementoListaDistribucion($_POST['elementoListaBorrar'], $_POST['nombreListaPadreBorrar']);
            if ($resultadoOP == 2) {
                $erroresBajaElementoLista[2] = "***No se ha podido borrar la direccion";
            }
        }
    }
    // recuperamos los datos del usuario para mostrar por pantalla
    $usuario = DB::recuperarUsuarioPorCorreo($_SESSION['CORREO']);
}

// SALIR O MODIFICAR
if (isset($_POST['salir']) && !empty($_POST['salir'])) {
    header("Location: login.php");
    session_unset();
} else if (isset($_POST['volver']) && !empty($_POST['volver'])) {
    // guardamos en la session que el usuario quiere modificar sus datos
    header("Location: bandeja_entrada.php");
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!-- Desarrollo Web en Entorno Servidor -->
<!-- Autor: José María Rodríguez García -->
<!-- Proyecto : Gestor de correo electrónico -->
<!-- Ventana: listas_distribucion.php Permite al usuario gestionar sus listas de distribucion -->
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Kanomail.es > LISTAS DISTRIBUCION</title>
        <link href="css/listas.css" rel="stylesheet" type="text/css">
        <script type="application/javascript">
            var mostrar = function(id) {
            obj = document.getElementById(id);
            obj.style.display = (obj.style.display == 'none') ? 'block' : 'none';
            }

        </script>
    </head>

    <body>
        <fieldset> 
            <legend>Gestión listas de distribucion</legend>
<?php
echo "<h3>" . $usuario->getNombre() . " " . $usuario->getApe1() . " " . $usuario->getApe2() . "</h3>";
?>
  
            <!-- debo pintar las lista de ditribución dle usuasrio-->
            
               <form action='listas_distribucion.php' method='post'>
                <label>Crear Lista</label>
                <input type='text' name='nombreLista'/>
               
                <input class = 'botonAniadir' type='submit' name='aniadirLista' />
                <br/><br/>
            <?php
            if (isset($erroresAltaLista[0])) {
                echo "<p>".$erroresAltaLista[0]."</p>";
            }
            
            if (isset($erroresAltaLista[1])) {
                echo "<p>" . $erroresAltaLista[1] . "</p>";
            }

            if (isset($erroresAltaLista[2])) {
                echo "<p>" . $erroresAltaLista[2] . "</p>";
            }
            ?>
            </form>
            
            <label>Listas del usuario:</label>
            <?php
            if ($usuario->getListaDistri() != null && count($usuario->getListaDistri()) > 0) {
                foreach ($usuario->getListaDistri() as $nombreLista => $elementosLista) {
                    echo "<form action='listas_distribucion.php' method='post'>";
                    echo "<input type='hidden' name='nombreListaBorrar' value='" . $nombreLista . "' />";
                    echo '<label onclick="mostrar(\'elementos' . $nombreLista . '\'); return false">' . $nombreLista . '   </label>';
                    echo "<input class = 'botonDelete' type='submit' name='borrarLista'  /></br>";
                    echo "</form>";

                    if (isset($_POST['aniadirElementoLista']) && !empty($_POST['aniadirElementoLista']) && isset($_POST['nombreListaPadreAniadir']) && !empty($_POST['nombreListaPadreAniadir']) && $_POST['nombreListaPadreAniadir'] == $nombreLista) {
                        echo "<div id='elementos" . $nombreLista . "'   style='display:block'>";
                    } elseif (isset($_POST['borrarElementoLista']) && !empty($_POST['borrarElementoLista']) && isset($_POST['nombreListaPadreBorrar']) && !empty($_POST['nombreListaPadreBorrar']) && $_POST['nombreListaPadreBorrar'] == $nombreLista) {
                        echo "<div id='elementos" . $nombreLista . "'   style='display:block'>";
                    } else {
                        echo "<div id='elementos" . $nombreLista . "'   style='display:none'>";
                    }


                    //echo "<div id='elementos" .$nombreLista. "'   style='display:none'>";
                    // mostramos los subelementos
                    if (is_array($elementosLista) && count($elementosLista) > 0) {
                        foreach ($elementosLista as $elementoLista) {
                            echo "<form action='listas_distribucion.php' method='post'>";
                            echo "<input type='hidden' name='nombreListaPadreBorrar' value='" . $nombreLista . "  ' />";
                            echo "<input type='hidden' name='elementoListaBorrar' value='" . $elementoLista . "  ' />";
                            echo '<label >' . $elementoLista . '  </label>';
                            echo "<input class = 'botonDelete2' type='submit' name='borrarElementoLista' /></br>";


                            echo "</form>";
                        }
                    }
                    echo "<br/>";
                    echo "<form action='listas_distribucion.php' method='post'>";
                    echo "<input type='hidden' name='nombreListaPadreAniadir' value='" . $nombreLista . "' />";

                    //echo " <label>Dirección </label>";
                    echo "<input type='text' name='nombreElementoLista'/> ";
                    if (isset($erroresAltaElementoLista[0])) {
                        echo $erroresAltaElementoLista[0];
                    }
                    
                    echo "<input class = 'botonAniadir' type='submit' name='aniadirElementoLista' />";

                    if (isset($erroresAltaElementoLista[1])) {
                        echo "<p>" . $erroresAltaElementoLista[1] . "</p>";
                    }

                    if (isset($erroresAltaElementoLista[2])) {
                        echo "<p>" . $erroresAltaElementoLista[2] . "</p>";
                    }

                    if (isset($erroresAltaElementoLista[3])) {
                        echo "<p>" . $erroresAltaElementoLista[3] . "</p>";
                    }

                    if (isset($erroresBajaElementoLista[1])) {
                        echo "<p>" . $erroresBajaElementoLista[1] . "</p>";
                    }

                    if (isset($erroresBajaElementoLista[2])) {
                        echo "<p>" . $erroresBajaElementoLista[2] . "</p>";
                    }
                    echo "</form>";
                    echo "</div>";
                }
            }

            if (isset($erroresBorrarLista[1])) {
                echo "<p>" . $erroresBorrarLista[1] . "</p>";
            }

            if (isset($erroresBorrarLista[2])) {
                echo "<p>" . $erroresBorrarLista[2] . "</p>";
            }
            ?>

            <form action='listas_distribucion.php' method='post'>
           <br/>
                <input class="boton1" type='submit' name='volver' value='Volver bandeja de entrada' />
                <br/>
                <br/>
                <input class="boton1" type='submit' name='salir' value='Salir' />
            </form>
        </fieldset>
    </body>
</html>