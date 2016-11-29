<?php
// añadimos los require necesario
require_once("auxiliar/DB.php");

$error = [];
$errorOperacion ="";
$datos = [];
session_start();



if (isset($_POST['salir'])) {
      header("Location: login.php");
}

$usuario = null;
if (isset($_SESSION['CORREO']) && !empty($_SESSION['CORREO']) && isset($_SESSION['MODIFICAR'])) {
    $usuario = DB::recuperarUsuarioPorCorreo($_SESSION['CORREO']);
}


// control acciones
if (isset($_POST['registrarse']) || isset($_POST['modificar'])) {

    // validamos que todos los campos esten informados
    
    // correo
    //if (isset($_POST['correo']) && $_POST['correo'] != '') {
    if (isset($_POST['registrarse'])) {
        if (isset($_POST['correo']) && filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
            $datos['CORREO'] = $_POST['correo'];
        } else {
            $error[0] = "El campo correo no puede estar vacio";
        }
    }
    
    
    // password
    if (isset($_POST['password']) && $_POST['password'] != '') {
        $datos['PASSWORD'] = $_POST['password'];
    } else {
        $error[1] = "El campo password no puede estar vacio";
    }
    // nombre
    if (isset($_POST['nombre']) && $_POST['nombre'] != '') {
        $datos['NOMBRE'] = $_POST['nombre'];
    } else {
        $error[2] = "El campo nombre no puede estar vacio";
    }
    
     // ape_1
    if (isset($_POST['ape_1']) && $_POST['ape_1'] != '') {
        $datos['APE_1'] = $_POST['ape_1'];
    } else {
        $error[3] = "El campo apellido no puede estar vacio";
    }

    // ape_2
    if (isset($_POST['ape_2']) && $_POST['ape_2'] != '') {
        $datos['APE_2'] = $_POST['ape_2'];
    } else {
        $error[4] = "El campo apellido no puede estar vacio";
    }
    // localidad

    if (isset($_POST['localidad']) && $_POST['localidad'] != '') {
        $datos['LOCALIDAD'] = $_POST['localidad'];
    } else {
        $error[5] = "El campo localidad no puede estar vacio";
    }
    
     $datos['SEXO'] = $_POST['sexo'];
    
    // si no encontramos rerrores. podemos registrar
    if (count($error) == 0) {
        
        
        if (isset($_POST['registrarse'])) {
            $datos['ID_TIPO_USUARIO'] = 2;
            $datos['ID_ESTADO_USUARIO'] = 1;
            // 0 > correcto
            // 1 > la cuenta de correo ya existe
            // 2 > error durante la actualización o inserción del registro 
            
            $resultado_insert = DB::insertarOactualizarUsuario("INSERT", $datos);
            if ($resultado_insert == 0) {
                // insercion correcta y redirigimos a la ventana de loging hasta
                // que el admin de de alta las peticiones
                echo "<script>
                    alert('Se ha dado de alta en el sistema correctamente');
                    window.location.href='login.php';
                </script>";
                // header("Location: login.php");
            } else if ($resultado_insert == 1) {
                // error inserción porque el correo ya existe
                $errorOperacion = "Lo sentimos, pero la dirección de correo ya existe";
                $error[0] = "La dirección ya existe";
            } else if ($resultado_insert == 2) {
                // error inserción
                $errorOperacion = "Lo sentimos, se ha producido un error durante la inserción";
            }
        } else if (isset($_POST['modificar'])) {
            $datos['ID_USUARIO'] = $usuario->getId();
             $resultado_insert = DB::insertarOactualizarUsuario("UPDATE", $datos);
            if ($resultado_insert == 0) {
                // insercion correcta y redirigimos a la ventana de loging hasta
                // que el admin de de alta las peticiones
                echo "<script>
                    alert('Se han modificado sus datos de manera correctamente');
                    window.location.href='login.php';
                </script>";
                // header("Location: login.php");
            } else if ($resultado_insert == 2) {
                // error inserción
                $errorOperacion = "Lo sentimos, se ha producido un error durante la actualización";
            }
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!-- Desarrollo Web en Entorno Servidor -->
<!-- Autor: José María Rodríguez García -->
<!-- Proyecto : Gestor de correo electrónico -->
<!-- Ventna: login.php Permite al usuario logearse o registrarse en la aplicación -->
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Kanomail.es > REGISTO USUARIOS</title>
        <link href="css/comun.css" rel="stylesheet" type="text/css">
        <link href="css/login.css" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div id='registro'>
            <form action='registro.php' method='post'>
                <fieldset>
                    <legend>Registro</legend>
                    
                    <div>
                        <label for='correo' >Correo:</label><br/>
                        <input class='campoTexto' type='text' name='correo' id='correo' maxlength="50"  
                            <?php 
                                if ($usuario != null) { echo "disabled = true value = '".$usuario->getCorreo()."' ";} 
                            ?>
                               />
                        <br/>
                         <?php
                            if (isset($error[0])) {
                                echo "<label>***".$error[0]."</label>";
                            }
                         ?>
                    </div>
                    
                    <div>
                        <label for='password' >Contraseña:</label><br/>
                        <input class='campoTexto' type='password' name='password' id='password' maxlength="50" 
                           <?php 
                                if ($usuario != null) { echo " value = '".$usuario->getPassword()."' ";} 
                            ?>
                               /><br/>
                         <?php
                            if (isset($error[1])) {
                                echo "<label>***".$error[1]."</label>";
                            }
                         ?>
                    </div>

                    <div>
                        <label for='nombre' >Nombre:</label><br/>
                        <input class='campoTexto' type='text' name='nombre' id='nombre' maxlength="50" 
                               <?php 
                                if ($usuario != null) { echo " value = '".$usuario->getNombre()."' ";} 
                            ?>
                               /><br/>
                         <?php
                         
                            if (isset($error[2])) {
                                echo "<label>***".$error[2]."</label>";
                            }
                         ?>
                    </div>
                    
                    <div>
                        <label for='ape_1' >Primer apellido:</label><br/>
                        <input class='campoTexto' type='text' name='ape_1' id='ape_1' maxlength="50" 
                               <?php 
                                if ($usuario != null) { echo " value = '".$usuario->getApe1()."' ";} 
                            ?>
                               
                               /><br/>
                        <?php
                            if (isset($error[3])) {
                                echo "<label>***".$error[3]."</label>";
                            }
                         ?>
                    </div>

                    <div>
                        <label for='ape_2' >Segundo apellido:</label><br/>
                        <input class='campoTexto' type='text' name='ape_2' id='ape_2' maxlength="50" 
                               <?php 
                                if ($usuario != null) { echo " value = '".$usuario->getApe2()."' ";} 
                            ?>
                               
                               /><br/>
                        <?php
                            if (isset($error[4])) {
                                echo "<label>***".$error[4]."</label>";
                            }
                         ?>
                    </div>
                    
                    <div>
                        <label for='sexo' >Sexo:</label><br/>
                        <select class='combo' name='sexo' id ='sexo'>
                            <option value='1'
                                    <?php 
                                if ($usuario != null && $usuario->getSexo() == 1) { echo " selected = true ";} 
                            ?>
                               
                                    
                                    >Hombre</option>
                            <option value='2'>Mujer</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for='localidad' >Localidad:</label><br/>
                        <input class='campoTexto' type='text' name='localidad' id='localidad' maxlength="50" 
                               <?php 
                                if ($usuario != null) { echo " value = '".$usuario->getLocalidad()."' ";} 
                            ?>
                               
                               /><br/>
                        <?php
                            if (isset($error[5])) {
                                echo "<label>***".$error[5]."</label>";
                            }
                         ?>
                    </div>
                     <div >
                        <label><?php echo $errorOperacion; ?></label><br/>
                    </div> 
                    <div>
                        <?php 
                      
                            if ($usuario != null) {
                                echo "<input class='boton1' type='submit' name='modificar' value='Modificar' />";
                            } else {
                                echo "<input class='boton1' type='submit' name='registrarse' value='Registrarse' />";
                            }
                        ?>
                        
                    </div>                   
                    <br/>
                    <input class="boton1" type='submit' name='salir' value='Salir' />
                </fieldset>
            </form>
        </div>
    </body>
</html>



