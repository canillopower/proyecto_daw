<?php
// imports
require_once("auxiliar/DB.php");

// variables generales que se utilizaran en la pantalla
$error = [];
$errorOperacion ="";
$datos = [];
session_start();
$usuario = null;

// control acciones
// SALIR
if (isset($_POST['salir'])) {
      header("Location: login.php");
}


// recuperamos los datos del usuario
if (isset($_SESSION['CORREO']) && !empty($_SESSION['CORREO']) && isset($_SESSION['MODIFICAR'])) {
    $usuario = DB::recuperarUsuarioPorCorreo($_SESSION['CORREO']);
}

// REGISTRARSE O MODIFICAR
if (isset($_POST['registrarse']) || isset($_POST['modificar'])) {

    // validamos que todos los campos esten informados
    
    if (isset($_POST['registrarse'])) {
        if (isset($_POST['correo']) && filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
            $datos['CORREO'] = $_POST['correo'];
        } else {
            $error[0] = "No es una dirección de correo valida";
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
<!-- Ventana: registro.php Permite al usuario logearse o registrarse en la aplicación -->
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Kanomail.es > REGISTO USUARIOS</title>
        <link href="css/comun.css" rel="stylesheet" type="text/css">
        <script>
        function checkPass() {
    //Store the password field objects into variables ...
    var pass1 = document.getElementById('password');
    var pass2 = document.getElementById('password2');
    //Store the Confimation Message Object ...
    var message = document.getElementById('confirmMessage');
    //Set the colors we will be using ...
    var goodColor = "#66cc66";
    var badColor = "#ff6666";
    //Compare the values in the password field 
    //and the confirmation field
    if(pass1.value == pass2.value){
        //The passwords match. 
        //Set the color to the good color and inform
        //the user that they have entered the correct password 
        pass2.style.backgroundColor = goodColor;
        message.style.color = goodColor;
        message.innerHTML = "Contraseña correcta!"
    }else{
        //The passwords do not match.
        //Set the color to the bad color and
        //notify the user.
        pass2.style.backgroundColor = badColor;
        message.style.color = badColor;
        message.innerHTML = "No coincide contraseña!"
    }
}  
        
        </script>
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
                                if ($usuario != null) {
                                    echo "disabled = true value = '".$usuario->getCorreo()."' ";
                                    
                                } else {
                                    echo "value =  '@kanomail.es'";
                                }
                            ?>
                               />
                        <br/>
                         <?php
                            if (isset($error[0])) {
                                echo "<div class='error'>".$error[0]."</div>";
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
                                echo "<div class='error'>".$error[1]."</div>";
                            }
                         ?>
                        
                         <label for='password' >Confirmar contraseña:</label><br/>
                        <input class='campoTexto' type='password' name='password2' id='password2' maxlength="50" onkeyup="checkPass(); return false;"
                               
                               <?php 
                                if ($usuario != null) { echo " value = '".$usuario->getPassword()."' ";} 
                            ?>
                               /><br/>
                        <span id="confirmMessage" class="confirmMessage"></span>
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
                                echo "<div class='error'>".$error[2]."</div>";
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
                                echo "<div class='error'>".$error[3]."</div>";
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
                                echo "<div class='error'>".$error[4]."</div>";
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
                                echo "<div class='error'>".$error[5]."</div>";
                            }
                         ?>
                    </div>
                     <div >
                        <label><?php echo $errorOperacion; ?></label><br/>
                    </div> 
                    <div>
                        <?php 
                      
                            if ($usuario != null) {
                                echo "<input class='boton1' type='submit' name='modificar' value='Modificar' /> <br/>";
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



