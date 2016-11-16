<?php

/**
 * Description of Usuario
 *
 * @author José María Rodríguez Garcia
 */
class Correo {

    protected $datosCabecera;
    protected $cuerpoEmail;
    // Seen, \Answered, \Flagged, \Deleted, y \Draft 
   
    

    public function __construct($datosCabecera, $cuerpoEmail) {
        $this->datosCabecera = $datosCabecera;
        $this->cuerpoEmail = $cuerpoEmail;
    }

    public function getDatosCabecera() {
        return $this->datosCabecera;
    }

      public function getCuerpoEmail() {
        return $this->cuerpoEmail;
    }
    

}
