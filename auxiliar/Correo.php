<?php

/**
 * Description of Usuario
 *
 * @author José María Rodríguez Garcia
 */
class Correo {
    protected $ID_CORREO;
    protected $ID_USUARIO;
    protected $ASUNTO;
    protected $CONTENIDO;
    protected $FECHA_ENVIO;
    protected $ID_ESTADO_CORREO;
    protected $DATOS_ENVIO_CORREO;
    
     public function getIdCorreo() { return $this->ID_CORREO; } 
     public function getIdUsuario() { return $this->ID_USUARIO; } 
     public function getAsunto() { return $this->ASUNTO; } 
     public function getContenido() { return $this->CONTENIDO; } 
     public function getFechaEnvio() { return $this->FECHA_ENVIO; } 
     public function getIdEstadoCorreo() { return $this->ID_ESTADO_CORREO; } 
     public function getDatosEnvioCorreo() { return $this->DATOS_ENVIO_CORREO; }
     
     public function __construct($datos, $datosEnvio) {
        $this->ID_CORREO = $datos['ID_CORREO'];
        $this->ID_USUARIO = $datos['ID_USUARIO'];
        $this->ASUNTO = $datos['ASUNTO'];
        $this->CONTENIDO = $datos['CONTENIDO'];
        $this->FECHA_ENVIO = $datos['FECHA_ENVIO'];
        $this->ID_ESTADO_CORREO = $datos['ID_ESTADO_CORREO'];
        $this->DATOS_ENVIO_CORREO = $datosEnvio;
     }
    
}
