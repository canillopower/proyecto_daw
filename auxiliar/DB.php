<?php
// añadimos los require necesario
require_once('Usuario.php');
require_once('Correo.php');
require_once('DatosEnvioCorreo.php');

class DB {  
    
    protected static function ejecutaConsulta($sql) {
        $opc = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
        $dsn = "mysql:host=localhost;dbname=proyecto";
        $usuario = 'root';
        $contrasena = '';
        
        $dwes = new PDO($dsn, $usuario, $contrasena, $opc);
        $resultado = null;
        if (isset($dwes)) $resultado = $dwes->query($sql);
        return $resultado;
    }

    public static function recuperarUsuario($correo, $password) {
        $sql = "SELECT * FROM usuarios";
        $sql .= " WHERE correo='" . $correo . "'";
        $sql .= "   AND password='" . $password . "'";
        $resultado = self::ejecutaConsulta ($sql);
        
	if(isset($resultado) && $resultado->rowCount() > 0) {
            $datos = $resultado->fetch();
            $usuario = new Usuario($datos);
            return $usuario;  
	}        
        return null;
    }
    
    public static function recuperarUsuariosPedienteAlta() {
        $sql = "SELECT * FROM usuarios";
        $sql .= " WHERE ID_ESTADO_USUARIO = 1 AND ID_TIPO_USUARIO = 2";

        $resultado = self::ejecutaConsulta ($sql);
        
	if(isset($resultado) && $resultado->rowCount() > 0) {
            
            $todoDatos = array();
            $datos = $resultado->fetch();
            
            while ($datos != null) {
                $todoDatos[] = new Usuario($datos);
                $datos = $resultado->fetch();
            }
            return $todoDatos;  
	}        
        return null;
    }
    
    
    public static function recuperarTodosUsuarios() {
        $sql = "SELECT * FROM usuarios";
        $sql .= " WHERE ID_TIPO_USUARIO = 2";
        $sql.= "  ORDER BY ID_USUARIO DESC";
        $resultado = self::ejecutaConsulta ($sql);
        
	if(isset($resultado) && $resultado->rowCount() > 0) {
            
            $todoDatos = array();
            $datos = $resultado->fetch();
            
            while ($datos != null) {
                $todoDatos[] = new Usuario($datos);
                $datos = $resultado->fetch();
            }
            return $todoDatos;  
	}        
        return null;
    }

    public static function recuperarUsuarioPorCorreo($correo) {
        $sql = "SELECT * FROM usuarios";
        $sql .= " WHERE correo='" . $correo . "'";
        $resultado = self::ejecutaConsulta ($sql);
        
	if(isset($resultado) && $resultado->rowCount() > 0) {
            $datos = $resultado->fetch();
            $usuario = new Usuario($datos);
            $usuario->setListaDistri(DB::recuperarListaDistribuccion($usuario->getId()));
            return $usuario;  
	}        
        return null;
    }
    
    public static function recuperarListaDistribuccion($id_usuario) {
        $sql = "SELECT * FROM lista_distribucion";
        $sql .= " WHERE id_usuario='" . $id_usuario . "'";
        $resultado = self::ejecutaConsulta ($sql);
        
	if(isset($resultado) && $resultado->rowCount() > 0) {
            $todoDatos = array();
            $datos = $resultado->fetch();
            
            while ($datos != null) {
                $todoDatos[$datos['NOMBRE_LISTA']] = DB::recuparDireccionesPorLista($datos['NOMBRE_LISTA']);
                
                $datos = $resultado->fetch();
            }
            return $todoDatos;  
	}        
        return null;
    }
    
     public static function recuparDireccionesPorLista($nombre_lista) {
        $sql = "SELECT * FROM direcciones";
        $sql .= " WHERE nombre_lista ='" . $nombre_lista . "'";
        $resultado = self::ejecutaConsulta ($sql);
        
	if(isset($resultado) && $resultado->rowCount() > 0) {
            $todoDatos = array();
            $datos = $resultado->fetch();
            
            while ($datos != null) {
                $todoDatos[] = $datos['DIRECCION'];
                
                $datos = $resultado->fetch();
            }
            return $todoDatos;  
	}        
        return null;
    }
    
    public static function recuperarUsuarioPorNombreEstado($nombre, $estado) {
        $sql = "SELECT * FROM usuarios WHERE";
        
        if ($nombre != null) {
            $sql .= " nombre like '%" . $nombre . "%'";
            if ($estado != null) {
                $sql .= " AND";
            }
        }
        
        if ($estado != null) {
            $sql .= " ID_ESTADO_USUARIO = ".$estado;
        }
        
        // incluyo ordenacion
        $sql.= "  ORDER BY ID_USUARIO DESC";
        
        $resultado = self::ejecutaConsulta ($sql);
        
	if(isset($resultado) && $resultado->rowCount() > 0) {
            
            $todoDatos = array();
            $datos = $resultado->fetch();
            
            while ($datos != null) {
                $todoDatos[] = new Usuario($datos);
                $datos = $resultado->fetch();
            }
            return $todoDatos;  
	}        
        return null;
    }
    
     public static function recuperarProxIdUsuario() {
        $sql = "SELECT MAX(ID_USUARIO) FROM usuarios";
        
        $resultado = self::ejecutaConsulta ($sql);
        
	if(isset($resultado) && $resultado->rowCount() > 0) {
            $datos = $resultado->fetch();
            return $datos[0] + 1 ;  
	}        
        return null;
    }

    public static function insertarOactualizarUsuario($tipoOperacion, $datos) {
        
        if (isset($datos['CORREO']) && self::recuperarUsuarioPorCorreo($datos['CORREO']) != null) {
            return 1;
        }
        
        if ("INSERT" == $tipoOperacion) {
            // recuperamos el proximo ID
            $id = self::recuperarProxIdUsuario();
            if ($id != null) {
                $campos = "ID_USUARIO,";
                $valores = $id . ",";
                foreach ($datos as $campo => $valor) {
                    $campos .= $campo . ",";
                    $valores .= "'".$valor . "',";
                }
                $campos = substr($campos, 0, -1);
                $valores = substr($valores, 0, -1);

                $sql = "INSERT INTO usuarios ";
                $sql .= "(";
                $sql .= $campos;
                $sql .= ")";
                $sql .= " VALUES ";
                $sql .= "(";
                $sql .= $valores;
                $sql .= ")";
                echo $sql;
                $resultado = self::ejecutaConsulta($sql);
                if ($resultado) {
                    return 0;
                } else {
                    return 2;
                }
            }
        } else if ("UPDATE" == $tipoOperacion){

            if (isset($datos['ID_USUARIO'])) {
                $sql = "UPDATE usuarios SET ";
                foreach ($datos as $campo => $valor) {
                    if ($campo != 'ID_USUARIO') {
                        $sql .= $campo." = '".$valor."',";
                    }
                }
                $sql =  substr($sql, 0, -1);
                $sql = $sql." WHERE ID_USUARIO = ".$datos['ID_USUARIO'];

                $resultado = self::ejecutaConsulta($sql);
                
                if ($resultado) {
                    return 0;
                } else {
                    return 2;
                }
            }
           
        }

 
    }
    
}

?>