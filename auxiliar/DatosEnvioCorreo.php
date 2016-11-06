<?php

/**
 * Description of DatosEnvioCorreo
 *
 * @author Kano
 */
class DatosEnvioCorreo {

    protected $ID_CORREO;
    protected $CUENTA_CORR;
    protected $ID_TIPO_CUENTA;

    public function getIdCorreo() {
        return $this->ID_CORREO;
    }

    public function getCuentaCorr() {
        return $this->CUENTA_CORR;
    }

    public function getIdTipoCuenta() {
        return $this->ID_TIPO_CUENTA;
    }

    public function __construct($datos) {
        $this->ID_CORREO = $datos['ID_CORREO'];
        $this->CUENTA_CORR = $datos['CUENTA_CORR'];
        $this->ID_TIPO_CUENTA = $datos['ID_TIPO_CUENTA'];
    }

}
