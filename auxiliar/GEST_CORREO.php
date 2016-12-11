<?php
    require_once("PHPMailer-master/class.phpmailer.php");
    require_once("PHPMailer-master/class.smtp.php");
    require_once("CONSTANTES.php");

class GEST_CORREO {
    
    public static function enviarCorreoBienvenida($de, $passUser, $para, $operacion) {
        $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->host = CONSTANTES::$HOST;
        $mail->Username = $de;
        $mail->Password = $passUser;
        
        $mail->From = "admin@kanomail.es";
        $mail->FromName = "Administración usuarios KanoMail.es";

        $mail->AddAddress($para);
        $mail->Subject = "Administración KanoMail.es";
        $mail->Body = "Buenos días, su cuenta de correo ".$para." ha sido ". $operacion;


        if ($mail->send()) {
            return 0;
            
        } else {
            echo "Se ha producido un error durante el envio de correo: " . $mail->ErrorInfo;
            return 1;
        }
    }
    
    public static function responderCorreo($de, $passUser,$nombreUser, $para, $texto, $asunto) {
        $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->host =  CONSTANTES::$HOST;
        $mail->Username = $de;
        $mail->Password = $passUser;
                      
        $mail->From = $de;
        $mail->FromName = $nombreUser;
        $mail->addReplyTo($de, "Responder");
        
        $mail->AddAddress($para);

        $mail->Subject = $asunto;
        $mail->Body = $texto;
        

        if ($mail->send()) {
             $server =  CONSTANTES::$SERVER;
             
             $body = $mail->MIMEBody;
             
             $header = $mail->MIMEHeader;
             $arrayHeader = iconv_mime_decode_headers($mail->getSentMIMEMessage());
             $message = "From: ".$arrayHeader['From']."\r\n"
                   . "To: ".$arrayHeader['To']."\r\n"
                   . "Date: ".$arrayHeader['Date']."\r\n"
                   . "Subject: ".$arrayHeader['Subject']."\r\n"
                   . "Message-ID: ".$arrayHeader['Message-ID']."\r\n"
                   . "".$body."\r\n";
             
             $imapStream = imap_open($server."Sent", $de, $passUser);
            imap_append($imapStream, $server."Sent", $message,  "\\Seen"); // antes tenia \\Seen al final
            imap_close($imapStream);

            
            return 0;
            
        } else {
            echo "Se ha producido un error durante el envio de correo: " . $mail->ErrorInfo;
            return 1;
        }
    }
    
     public static function recuperarCorreosPorBandeja($de, $passUser) {
        $server = CONSTANTES::$SERVER;
        $mbox = imap_open($server, $de, $passUser) or die('No se ha podido conectar a su cuenta de correo: ' . imap_last_error());
        $list = imap_getmailboxes($mbox, $server, "*");
        
         // creamos las carpetas basicas
        $lista = imap_list($mbox, $server, "*");
        if (array_search($server . "Sent", $lista) === false) {
            imap_createmailbox($mbox, imap_utf7_encode($server . "Sent"));
        }

        if (array_search($server . "Trash", $lista) === false) {
            imap_createmailbox($mbox, imap_utf7_encode($server . "Trash"));
        }
        
        $todoDatos = array();

        if (is_array($list)) {

            foreach ($list as $clave => $valor) {
                $nombre = str_replace($server, "", imap_utf7_decode($valor->name));
                $todoDatos[$nombre] = null;
                //un vez obtenido el nombre del buzon, compruebo si existe carpeta, y me traigo sus correos
                imap_reopen($mbox, $server . $nombre);

                $MC = imap_check($mbox);
                $MN = $MC->Nmsgs;
                if ($MN > 0) {
                    $overview = imap_fetch_overview($mbox, "1:$MN", 0);
                    unset($correosDelBuzon);
                    $correosDelBuzon[] = null;
                    foreach ($overview as $claveArray => $datosCorreo) {
                        
                        $body = imap_body($mbox, $datosCorreo->msgno, FT_PEEK);
                        $correo = new Correo($datosCorreo, $body);
                        $correosDelBuzon[$datosCorreo->msgno] = $correo;
                    }
                    $correosDelBuzon = array_reverse($correosDelBuzon);
                    
                    $todoDatos[$nombre] = $correosDelBuzon;
                }
            }
        }

        imap_close($mbox);
        return $todoDatos;
    }
    
    
     public static function borrarTodosCorreosPorBandeja($de, $passUser, $bandeja, $seen) {
        $server = CONSTANTES::$SERVER;
        $mbox = imap_open($server, $de, $passUser) or die('No se ha podido conectar a su cuenta de correo: ' . imap_last_error());
        $list = imap_getmailboxes($mbox, $server, "*");

        if (is_array($list)) {

            foreach ($list as $clave => $valor) {
                
                $nombre = str_replace($server, "", imap_utf7_decode($valor->name));
                
                if ($nombre == $bandeja) {
                          //un vez obtenido el nombre del buzon, compruebo si existe carpeta, y me traigo sus correos
                    imap_reopen($mbox, $server . $nombre);

                    $MC = imap_check($mbox);
                    $MN = $MC->Nmsgs;
                    if ($MN > 0) {
                        $overview = imap_fetch_overview($mbox, "1:$MN", 0);
   
                        foreach ($overview as $claveArray => $datosCorreo) {
                            if ("INBOX" == $bandeja && $seen != null) {
                                // borramos los no leidos o no leidos dependiendo del valor de seen
                                if ($datosCorreo->seen == $seen) {
                                    imap_delete($mbox, $datosCorreo->msgno);
                                }
                            } else {
                                imap_delete($mbox, $datosCorreo->msgno);
                            }
                            
                        }
                    }
                }
            }
        }
        imap_expunge($mbox);
        imap_close($mbox);
    }
    
    
    public static function recuperarCorreosPorBandejaFiltrada($de, $passUser, $filtros, $filtro) {
        
        /* DE, > FROM 
         * PARA,  > TO
         * CC,> CC
         * ASUNTO, > SUBJECT 
         * TEXTO > TEXT 
                   
         */
        
        $server = CONSTANTES::$SERVER;
        $mbox = imap_open($server, $de, $passUser) or die('No se ha podido conectar a su cuenta de correo: ' . imap_last_error());
        $list = imap_getmailboxes($mbox, $server, "*");
        
         // creamos las carpetas basicas
        $lista = imap_list($mbox, $server, "*");
        if (array_search($server . "Sent", $lista) === false) {
            imap_createmailbox($mbox, imap_utf7_encode($server . "Sent"));
        }

        if (array_search($server . "Trash", $lista) === false) {
            imap_createmailbox($mbox, imap_utf7_encode($server . "Sent"));
        }
        
        $todoDatos = array();

        if (is_array($list)) {

            foreach ($list as $clave => $valor) {
                $nombre = str_replace($server, "", imap_utf7_decode($valor->name));
                $todoDatos[$nombre] = null;
                //un vez obtenido el nombre del buzon, compruebo si existe carpeta, y me traigo sus correos
                imap_reopen($mbox, $server . $nombre);

                // una vez que estoy en la carpeta aplico los criterios de busqueda
                $criterio = "";
                if (count($filtros) == 1) {
                    // si solo tiene un siltro
                    if ($filtros[0] == 'checkDE') {
                        $criterio = "FROM ".'"'.$filtro.'"';
                    } elseif ($filtros[0] == 'checkPARA') {
                        $criterio = "TO ".'"'.$filtro.'"';
                    } elseif ($filtros[0] == 'checkCC') {
                        $criterio = "CC ".'"'.$filtro.'"';
                    } elseif ($filtros[0] == 'checkASUNTO') {
                        $criterio = "SUBJECT ".'"'.$filtro.'"';
                    } elseif ($filtros[0] == 'checkTEXTO') {
                        $criterio = "TEXT ".'"'.$filtro.'"';
                    }
                    $numEmails= imap_search($mbox, $criterio);
                
                if (is_array($numEmails) && count($numEmails) > 0) {
                    
                    unset($correosDelBuzon);
                    $correosDelBuzon[] = null;
                    
                    foreach ($numEmails as $numMsg) {
                        
                        //$head = imap_fetchheader($mbox, $numMsg);
                        //$head2 = imap_headerinfo($mbox, $numMsg);
                        $overview = imap_fetch_overview($mbox, $numMsg, 0);
                        $body = imap_body($mbox, $numMsg, FT_PEEK);
                        $correo = new Correo($overview[0], $body);
                        $numCorr = $overview[0]->msgno;
                        $correosDelBuzon[$numCorr] = $correo;

                    } 
                     $correosDelBuzon = array_reverse($correosDelBuzon);
                    $todoDatos[$nombre] = $correosDelBuzon;
                }
                } 
                
            }
        }

        imap_close($mbox);
        return $todoDatos;
    }

    public static function recuperarCorreo($de, $passUser, $tipoBandeja) {
        //tipo Bandeja
        // 1. Recibidos (IMBOx)
        // 2. Saliente (Sent)
        // 3. Enviados (Trash)
        
        $server = CONSTANTES::$SERVER;;
        $mbox = imap_open($server, $de, $passUser) or die('No se ha podido conectar a su cuenta de correo: ' . imap_last_error());
        
        
        $list = imap_list($mbox, $server, "*");
        $todoDatos = array();
        
        if ($tipoBandeja == 1) {
            
            if (array_search($server."INBOX", $list) === false) {
                imap_close($mbox);
                return $todoDatos;
            }

            imap_reopen($mbox, $server.'INBOX');
        }
        if ($tipoBandeja == 2) {
            if (array_search($server."Sent", $list) === false) {
                imap_createmailbox($mbox, imap_utf7_encode($server."Sent"));
                imap_close($mbox);
                return $todoDatos;
            }
            imap_reopen($mbox, $server.'Sent');
        }
        if ($tipoBandeja == 3) {
            if (array_search($server."Trash", $list) === false) {
                imap_createmailbox($mbox, imap_utf7_encode($server."Trash"));
                imap_close($mbox);
                return $todoDatos;
            }
            imap_reopen($mbox, $server.'Trash');
        }
      
        $MC = imap_check($mbox);
        $MN = $MC->Nmsgs;
        if ($MN == 0) {
            imap_close($mbox);
            return $todoDatos;
        }
        $overview = imap_fetch_overview($mbox, "1:$MN", 0);
        $size = sizeof($overview);
        
        for ($i = $size - 1; $i >= 0; $i--) {
            $datosCabecera = $overview[$i];
            $body = imap_body($mbox, $datosCabecera->msgno, FT_PEEK);
            $correo =  new Correo($datosCabecera, $body);
            $todoDatos[$datosCabecera->msgno] = $correo;
        }
        imap_close($mbox);
        return $todoDatos;
    }

    
    public static function borrarCorreo($de, $passUser, $msgno,$message_id, $tipoBandeja) {
        //tipo Bandeja
        // 1. Recibidos (IMBOx)
        // 2. Saliente (Sent)
        // 3. Enviados (Trash)
        
        $server = CONSTANTES::$SERVER;
        $mbox = imap_open($server, $de, $passUser) or die('No se ha podido conectar a su cuenta de correo: ' . imap_last_error());
        
        if ($tipoBandeja == 1) {
            imap_reopen($mbox, $server.'INBOX');
        }
        if ($tipoBandeja == 2) {
            imap_reopen($mbox, $server.'Sent');
        }
        if ($tipoBandeja == 3) {
            imap_reopen($mbox, $server.'Trash');
        }
       // imap_delete($mbox, $message_id, FT_UID);
        if ($message_id != null && $msgno == null) {
            imap_delete($mbox, $message_id, FT_UID);
        } else if ($message_id == null && $msgno != null) {
            imap_delete($mbox, $msgno);
        }
        
        
        
        imap_expunge($mbox);

        imap_close($mbox);
       
    }
    
    public static function borrarCorreoCarpetaAdicional($de, $passUser, $msgno,$message_id, $carpetaOrigen) {
        //tipo Bandeja
        // 1. Recibidos (IMBOx)
        // 2. Saliente (Sent)
        // 3. Enviados (Trash)
        
        $server = CONSTANTES::$SERVER;
        $mbox = imap_open($server, $de, $passUser) or die('No se ha podido conectar a su cuenta de correo: ' . imap_last_error());

        imap_reopen($mbox, $server.$carpetaOrigen);

       // imap_delete($mbox, $message_id, FT_UID);
        if ($message_id != null && $msgno == null) {
            imap_delete($mbox, $message_id, FT_UID);
        } else if ($message_id == null && $msgno != null) {
            imap_delete($mbox, $msgno);
        }

        imap_expunge($mbox);

        imap_close($mbox);
       
    }
    
    
    public static function marcaCorreoLeido($de, $passUser, $msgno) {
        $server = CONSTANTES::$SERVER;
        $mbox = imap_open($server, $de, $passUser) or die('No se ha podido conectar a su cuenta de correo: ' . imap_last_error());
        imap_reopen($mbox, $server.'INBOX');
        imap_setflag_full($mbox, $msgno, '\\Seen' );
        imap_close($mbox);
        
    }
    public static function moverCorreo($de, $passUser, $msgno, $tipoBandeja) {
        //tipo Bandeja
        // 1. Recibidos (IMBOx)
        // 2. Saliente (Sent)
        // 3. Enviados (Trash)
        
        $server = CONSTANTES::$SERVER;
        $mbox = imap_open($server, $de, $passUser) or die('No se ha podido conectar a su cuenta de correo: ' . imap_last_error());
        
        if ($tipoBandeja == 1) {
            imap_reopen($mbox, $server.'INBOX');
        }
        if ($tipoBandeja == 2) {
            imap_reopen($mbox, $server.'Sent');
        }
        if ($tipoBandeja == 3) {
            imap_reopen($mbox, $server.'Trash');
        }
        imap_mail_move($mbox, $msgno, "Trash");
        
        imap_close($mbox);
       
    }
    
    public static function moverCorreoCarpeta($de, $passUser, $msgno, $carpetaOrigen, $carpetaFinal) {
        //tipo Bandeja
        // 1. Recibidos (IMBOx)
        // 2. Saliente (Sent)
        // 3. Enviados (Trash)
        
        if (strtoupper($carpetaFinal) == "NUEVOS" 
                || strtoupper($carpetaFinal) == "RECIBIDOS") {
            $carpetaFinal = "INBOX";
        }
        
        if (strtoupper($carpetaFinal) == "ENVIADOS") {
            $carpetaFinal = "Sent";
        }
        if (strtoupper($carpetaFinal) == "ELIMINADOS") {
            $carpetaFinal = "Trash";
        }
        
        
        $server = CONSTANTES::$SERVER;
        $mbox = imap_open($server, $de, $passUser) or die('No se ha podido conectar a su cuenta de correo: ' . imap_last_error());

        // comprobamos si existe la carpeta
        $list = imap_list($mbox, $server, "*");
        if (array_search($server . $carpetaFinal, $list) === false) {
            imap_createmailbox($mbox, imap_utf7_encode($server . $carpetaFinal));
        }
 
        imap_reopen($mbox, $server.$carpetaOrigen);
       
        imap_mail_move($mbox, $msgno, $carpetaFinal);
        
        imap_close($mbox);
        
        GEST_CORREO::borrarCorreoCarpetaAdicional($de, $passUser, $msgno, null, $carpetaOrigen);
       
    }
    
    public static function enviarCorreo($de, $passUser, $nombreUser, $para, $paraCC, $asunto, $texto) {
        $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->host =  CONSTANTES::$HOST;
        $mail->Username = $de;
        $mail->Password = $passUser;
        

        $mail->From = $de;
        $mail->FromName = $nombreUser;
        $mail->addReplyTo($de);
        
        if ($para != null && count($para) > 0) {
            
            foreach ($para as $direccion) {
                $mail->AddAddress($direccion);
            }
        }
        
        
        if ($paraCC != null && count($paraCC) > 0) {
            
            foreach ($paraCC as $direccion) {
                $mail->AddAddress($direccion);
                $mail->addCC($direccion);
            }
        }

        $mail->Subject = $asunto;
        $mail->Body = $texto;
        

        if ($mail->send()) {
             $server =  CONSTANTES::$SERVER;
             
             $header = $mail->MIMEHeader;
             $body = $mail->MIMEBody;
             
           
             $arrayHeader = iconv_mime_decode_headers($mail->getSentMIMEMessage());
             $message = "From: ".$arrayHeader['From']."\r\n"
                   . "To: ".$arrayHeader['To']."\r\n"
                   . "Date: ".$arrayHeader['Date']."\r\n"
                   . "Subject: ".$arrayHeader['Subject']."\r\n"
                   . "MIME-Version: 1.0\r\n" 
                   . "Message-ID: ".$arrayHeader['Message-ID']."\r\n"
                   . "Content-Type: text/plain; charset=\"utf-8\"\r\n"
                   . "Content-Transfer-Encoding: quoted-printable\r\n"
                . "\r\n\r\n"
                . $body."\r\n"
                . "\r\n\r\n"
                     
                     ;
                   //. "Content-Type: ".$arrayHeader['Content-Type']."\r\n"
                   //. $body."\r\n\r\n";
             
            $imapStream = imap_open($server."Sent", $de, $passUser);
            imap_append($imapStream, $server."Sent", $message, "\\Seen");
            imap_close($imapStream);

            return 0;
            
        } else {
           echo "Mailer Error: " . $mail->ErrorInfo;
            return 1;
        }
    }
    /*
    public static function borrarCarpetaAdicional($de, $passUser, $nombreCarpeta) {
           //tipo Bandeja
        // 1. Recibidos (IMBOx)
        // 2. Saliente (Sent)
        // 3. Enviados (Trash)
        echo "-".$nombreCarpeta."-";
        $server = CONSTANTES::$SERVER;
        $mbox = imap_open($server, $de, $passUser) or die('No se ha podido conectar a su cuenta de correo: ' . imap_last_error());
        
        
        //imap_reopen($mbox, $server.$nombreCarpeta);
        
        imap_deletemailbox($mbox, 'INBOX.'.$nombreCarpeta);

        imap_expunge($mbox);

        imap_close($mbox);
    }
    */
}  


      
 ?>