<?php
use Phalcon\Mvc\Model;

class Proveedor extends Model {
    public $id_proveedor;
    public $razon_social;
    public $activo;

    public function initialize() {
        $this->setSource('proveedor'); // Nombre exacto de la tabla en tu DB
    }
}