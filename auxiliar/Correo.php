<?php
/*
 * @author José María Rodríguez García
 */
class Correo {

    protected $datosCabecera; // datos de cabecera del correo electronico
    protected $cuerpoEmail; // cuerpo del correo electronico

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
