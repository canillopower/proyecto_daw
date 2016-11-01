<?php

/**
 * Description of Usuario
 *
 * @author José María Rodríguez Garcia
 */
class Correo {
    protected $ID_CORREO;
    protected $ID_USUARIO_REMITENTE;
    protected $ASUNTO;
    protected $FECHA_ENVIO;
    protected $ID_ESTADO_CORREO;
    
     public function getIdCorreo() { return $this->ID_CORREO; } 
     public function getIdUsuarioRemitente() { return $this->ID_USUARIO_REMITENTE; } 
     public function getAsunto() { return $this->ASUNTO; } 
     public function getFechaEnvio() { return $this->FECHA_ENVIO; } 
     public function getIdEstadoCorreo() { return $this->ID_ESTADO_CORREO; } 
     
     public function __construct($datos) {
        $this->ID_CORREO = $datos['ID_CORREO'];
        $this->ID_USUARIO_REMITENTE = $datos['ID_USUARIO_REMITENTE'];
        $this->ASUNTO = $datos['ASUNTO'];
        $this->FECHA_ENVIO = $datos['FECHA_ENVIO'];
        $this->ID_ESTADO_CORREO = $datos['ID_ESTADO_CORREO'];
        
     }
    
}
