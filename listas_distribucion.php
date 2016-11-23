<?php
require_once("auxiliar/GEST_CORREO.php");
require_once("auxiliar/DB.php");
require_once("auxiliar/Correo.php");
session_start();

// recuperamos los correos del usuario


$usuario = null;
$erroresAltaLista = [];
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
                <br/>    
                <!-- debo pintar las lista de ditribución dle usuasrio-->
                <?php 
                    if ($usuario->getListaDistri() != null && count($usuario->getListaDistri()) > 0) {
                        foreach ($usuario->getListaDistri() as $nombreLista => $elementosLista) {
                            echo $nombreLista."</br>";
                           
                        }

                    }
                ?>
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