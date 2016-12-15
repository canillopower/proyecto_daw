<?php

/*
 * @author José María Rodríguez García
 */

// imports
require_once('Usuario.php');
require_once('Correo.php');
require_once("CONSTANTES.php");

class DB {

    /**
     * Metodo encargado de ejecutar la consulta contra la BBDD proyectos
     * @param type $sql Consulta a ejecutar
     * @return type Resultado de la operacion
     */
    protected static function ejecutaConsulta($sql) {
        $opc = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
        $dsn = "mysql:host=" . CONSTANTES::$HOST_BD . ";dbname=" . CONSTANTES::$NAME_BD . "";
        $usuario = CONSTANTES::$USER_BD;
        $contrasena = CONSTANTES::$PASS_BD;

        $dwes = new PDO($dsn, $usuario, $contrasena, $opc);
        $resultado = null;
        if (isset($dwes))
            $resultado = $dwes->query($sql);
        return $resultado;
    }

    /**
     * Metodo encargado de recuperar el usuario por el correo y password
     * @param type $correo Dirección de correo del usuario
     * @param type $password Password del usuario
     * @return \Usuario
     */
    public static function recuperarUsuario($correo, $password) {
        $sql = "SELECT * FROM usuarios";
        $sql .= " WHERE correo='" . $correo . "'";
        $sql .= "   AND password='" . $password . "'";
        $resultado = self::ejecutaConsulta($sql);

        if (isset($resultado) && $resultado->rowCount() > 0) {
            $datos = $resultado->fetch();
            $usuario = new Usuario($datos);
            return $usuario;
        }
        return null;
    }

    /**
     * Metodo encargado de recuperar los usuario pendientes de alta
     * @return \Usuario
     */
    public static function recuperarUsuariosPedienteAlta() {
        $sql = "SELECT * FROM usuarios";
        $sql .= " WHERE ID_ESTADO_USUARIO = 1 AND ID_TIPO_USUARIO = 2";

        $resultado = self::ejecutaConsulta($sql);

        if (isset($resultado) && $resultado->rowCount() > 0) {

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

    /**
     * Metodo encargado de recuperar todos los usuarios
     * @return \Usuario
     */
    public static function recuperarTodosUsuarios() {
        $sql = "SELECT * FROM usuarios";
        $sql .= " WHERE ID_TIPO_USUARIO = 2";
        $sql.= "  ORDER BY ID_USUARIO DESC";
        $resultado = self::ejecutaConsulta($sql);

        if (isset($resultado) && $resultado->rowCount() > 0) {

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

    /**
     * Metodo encargado de recuperar un usuario por su correo
     * @param type $correo Correo del usuario logeado
     * @return \Usuario
     */
    public static function recuperarUsuarioPorCorreo($correo) {
        $sql = "SELECT * FROM usuarios";
        $sql .= " WHERE correo='" . $correo . "'";
        $resultado = self::ejecutaConsulta($sql);

        if (isset($resultado) && $resultado->rowCount() > 0) {
            $datos = $resultado->fetch();
            $usuario = new Usuario($datos);
            $usuario->setListaDistri(DB::recuperarListaDistribuccion($usuario->getId()));
            return $usuario;
        }
        return null;
    }

    /**
     * Metodo encargado de recuperar las listas de distribución de un usuario
     * @param type $id_usuario Identificador del usuario
     * @return type
     */
    public static function recuperarListaDistribuccion($id_usuario) {
        $sql = "SELECT * FROM lista_distribucion";
        $sql .= " WHERE id_usuario='" . $id_usuario . "'";
        $resultado = self::ejecutaConsulta($sql);

        if (isset($resultado) && $resultado->rowCount() > 0) {
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

    /**
     * Metodo encargado de recuperar todas las direcciones de una lista
     * @param type $nombre_lista Nombre de la lista de distribucion
     * @return type
     */
    public static function recuparDireccionesPorLista($nombre_lista) {
        $sql = "SELECT * FROM direcciones";
        $sql .= " WHERE nombre_lista ='" . $nombre_lista . "'";
        $resultado = self::ejecutaConsulta($sql);

        if (isset($resultado) && $resultado->rowCount() > 0) {
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

    /**
     * Metodo encargado de recuperar un usuario por nombre y estado para filtrar
     * @param type $nombre Nombre del usuario
     * @param type $estado Estado del usuario
     * @return \Usuario
     */
    public static function recuperarUsuarioPorNombreEstado($nombre, $estado) {
        $sql = "SELECT * FROM usuarios WHERE";

        if ($nombre != null && !empty($nombre)) {
            $sql .= " nombre like '%" . $nombre . "%'";
            if ($estado != null) {
                $sql .= " AND";
            }
        }

        if ($estado != null && !empty($estado)) {
            // si inactivo
            if ("2" == $estado) {
                $sql .= " ID_ESTADO_USUARIO = " . $estado;
            } else {
                $sql .= " ID_ESTADO_USUARIO IN (1,3)" ;
            }
            
        }

        // incluyo ordenacion
        $sql.= " AND ID_USUARIO != 0  ORDER BY ID_USUARIO DESC";

        $resultado = self::ejecutaConsulta($sql);

        if (isset($resultado) && $resultado->rowCount() > 0) {

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

    /**
     * Metodo encargado de obtener el siguiente id de usuario de BBDD
     * @return type
     */
    public static function recuperarProxIdUsuario() {
        $sql = "SELECT MAX(ID_USUARIO) FROM usuarios";

        $resultado = self::ejecutaConsulta($sql);

        if (isset($resultado) && $resultado->rowCount() > 0) {
            $datos = $resultado->fetch();
            return $datos[0] + 1;
        }
        return null;
    }

    /**
     * Metodo encargado de comprobar si un usuario tiene una lista con el mismo nombre 
     * @param type $nombreLista Nombre de la nueva lista
     * @return boolean
     */
    public static function comprobarSiExiste($nombreLista) {
        $sql = "SELECT NOMBRE_LISTA FROM lista_distribucion WHERE NOMBRE_LISTA = '" . $nombreLista . "'";

        $resultado = self::ejecutaConsulta($sql);

        if (isset($resultado) && $resultado->rowCount() > 0) {
            $datos = $resultado->fetch();
            return true;
        }
        return false;
    }

    /**
     * Metodo encargado de de comprobar is existe una direccion en una lista de distribucion
     * @param type $direccion Direccion a añadir
     * @param type $nombreLista Nombre de la lista a la que queremos añadir la direccion
     * @return boolean
     */
    public static function comprobarSiExisteElemento($direccion, $nombreLista) {
        $sql = "SELECT * FROM direcciones WHERE NOMBRE_LISTA = '" . $nombreLista . "' "
                . "AND DIRECCION  = '" . $direccion . "'";

        $resultado = self::ejecutaConsulta($sql);

        if (isset($resultado) && $resultado->rowCount() > 0) {
            $datos = $resultado->fetch();
            return true;
        }
        return false;
    }

    /**
     * Metodo encargado de asociar una lista a un usuario
     * @param type $idUsuario Identificador del usuario logeado
     * @param type $nombreLista Nombre de la nueva lista
     * @return int
     */
    public static function insertarListaDistribucion($idUsuario, $nombreLista) {
        if (DB::comprobarSiExiste($nombreLista)) {
            // SI RETORNA TRUE, ES QUE EXISTE
            return 1;
        } else {

            $sql = "INSERT INTO lista_distribucion (NOMBRE_LISTA, ID_USUARIO) ";
            $sql = $sql . " VALUES ('" . $nombreLista . "', " . $idUsuario . ")";
            $resultado = self::ejecutaConsulta($sql);

            if ($resultado) {
                return 0;
            } else {
                return 2;
            }
        }
    }

    /**
     * Metodo encargado de insertar una direccion de correo en una lista de distribucion
     * @param type $direccion Direccion que queremos añadir a una lista especifica
     * @param type $nombreLista Nombre de la lista a la que queremos añadir una direccion
     * @return int
     */
    public static function insertarElementoListaDistribucion($direccion, $nombreLista) {
        if (DB::comprobarSiExisteElemento($direccion, $nombreLista)) {
            // SI RETORNA TRUE, ES QUE EXISTE
            return 1;
        } else {

            $sql = "INSERT INTO direcciones (NOMBRE_LISTA, DIRECCION) ";
            $sql = $sql . " VALUES ('" . $nombreLista . "', '" . $direccion . "')";
            $resultado = self::ejecutaConsulta($sql);

            if ($resultado) {
                return 0;
            } else {
                return 2;
            }
        }
    }

    /**
     * Metodo encargado de borrar una lista de un usuario
     * @param type $idUsuario Identificador del usuario
     * @param type $nombreLista Nombre de la lista a borrar
     * @return int
     */
    public static function borrarListaDistribucion($idUsuario, $nombreLista) {

        // PRIMERO BORRAMOS LOS hijos
        $sql = "DELETE FROM direcciones WHERE NOMBRE_LISTA = '" . $nombreLista . "';";

        $resultado = self::ejecutaConsulta($sql);
        if ($resultado) {
            // SI TODO FUE BIEN CONTINUO BORRANDO
            $sql = "DELETE FROM lista_distribucion WHERE NOMBRE_LISTA = '" . $nombreLista . "' AND ID_USUARIO = " . $idUsuario . " ";
            $resultado = self::ejecutaConsulta($sql);

            if ($resultado) {
                return 0;
            } else {
                return 2;
            }
        } else {
            return 1;
        }
    }

    /**
     * Metodo encargado de borrar un elemento de una lista
     * @param type $direccion Direcion a borrar de la lista 
     * @param type $nombreLista Nombre de la lista sobre la que borrar
     * @return int
     */
    public static function borrarElementoListaDistribucion($direccion, $nombreLista) {

        // SI TODO FUE BIEN CONTINUO BORRANDO
        $sql = "DELETE FROM direcciones WHERE NOMBRE_LISTA = '" . $nombreLista . "' "
                . "AND DIRECCION = '" . $direccion . "';";
        $resultado = self::ejecutaConsulta($sql);

        if ($resultado) {
            return 0;
        } else {
            return 2;
        }
    }

    /**
     * Metodo encargado de insertar o actualizar un usuario en base a unos parámetros
     * pasados en un array
     * @param type $tipoOperacion Control sobre tipo de operación
     * @param type $datos Valores a añadir o a borrar
     * @return int
     */
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
                    $valores .= "'" . $valor . "',";
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
                //echo $sql;
                $resultado = self::ejecutaConsulta($sql);
                if ($resultado) {
                    return 0;
                } else {
                    return 2;
                }
            }
        } else if ("UPDATE" == $tipoOperacion) {

            if (isset($datos['ID_USUARIO'])) {
                $sql = "UPDATE usuarios SET ";
                foreach ($datos as $campo => $valor) {
                    if ($campo != 'ID_USUARIO') {
                        $sql .= $campo . " = '" . $valor . "',";
                    }
                }
                $sql = substr($sql, 0, -1);
                $sql = $sql . " WHERE ID_USUARIO = " . $datos['ID_USUARIO'];

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