<?php

use Phalcon\Mvc\Model;

class Usuario extends Model
{
    public $id_usuario;
    public $nombre;
    public $email;
    public $password_hash;
    public $rol;
    public $activo;

    public function initialize()
    {
        // Indicamos el nombre exacto de la tabla
        $this->setSource('usuario');
    }
}