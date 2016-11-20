<?php

/**
 * Description of Usuario
 *
 * @author José María Rodríguez Garcia
 */
class Usuario {

    // atributos de la clase
    protected $ID_USUARIO;
    protected $CORREO;
    protected $PASSWORD;
    protected $NOMBRE;
    protected $APE_1;
    protected $APE_2;
    protected $SEXO;
    protected $LOCALIDAD;
    protected $ID_TIPO_USUARIO;
    protected $ID_ESTADO_USUARIO;
    protected $LISTAS_DISTRIBUCCION;
    
    public function getId() { return $this->ID_USUARIO; } 
    public function getCorreo() { return $this->CORREO; } 
    public function getPassword() { return $this->PASSWORD; } 
    public function getNombre() { return $this->NOMBRE; } 
    public function getApe1() { return $this->APE_1; } 
    public function getApe2() { return $this->APE_2; } 
    public function getSexo() { return $this->SEXO; } 
    public function getLocalidad() { return $this->LOCALIDAD; } 
    public function getTipo() { return $this->ID_TIPO_USUARIO; } 
    public function getEstado() { return $this->ID_ESTADO_USUARIO; } 
    public function getListaDistri() { return $this->LISTAS_DISTRIBUCCION; } 
    
    public function setListaDistri($lista) {
        $this->LISTAS_DISTRIBUCCION = $lista;
    }

    public function __construct($datos) {
        $this->ID_USUARIO = $datos['ID_USUARIO'];
        $this->CORREO = $datos['CORREO'];
        $this->PASSWORD = $datos['PASSWORD'];
        $this->NOMBRE = $datos['NOMBRE'];
        $this->APE_1 = $datos['APE_1'];
        $this->APE_2 = $datos['APE_2'];
        $this->SEXO = $datos['SEXO'];
        $this->LOCALIDAD = $datos['LOCALIDAD'];
        $this->ID_TIPO_USUARIO = $datos['ID_TIPO_USUARIO'];
        $this->ID_ESTADO_USUARIO = $datos['ID_ESTADO_USUARIO'];
    }

}
