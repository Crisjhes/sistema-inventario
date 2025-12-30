<?php
use Phalcon\Mvc\Model;

class Categoria extends Model {
    public $id_categoria;
    public $nombre;
    public $activo;

    public function initialize() {
        $this->setSource('categoria'); // Nombre exacto de la tabla en tu DB
    }
}