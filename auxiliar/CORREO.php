<?php
    require_once "PHPMailer-master/class.phpmailer.php";
    require_once "PHPMailer-master/class.smtp.php";

class CORREO {
    
    public static function enviarCorreoBienvenida($de, $passUser, $para, $operacion) {
          $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->host = "kanomail.es";
        $mail->Username = $de;
        $mail->Password = $passUser;
        $mail->AddAddress($para);

        $mail->Body = "Buenos días, su cuenta de correo ".$para." ha sido ". $operacion;


        if ($mail->send()) {
            echo "Message has been sent successfully";
            return 0;
            
        } else {
            echo "Mailer Error: " . $mail->ErrorInfo;
            return 1;
        }
    }
}  


      
 ?>