<?php
use Phalcon\Mvc\Model;

class Asignacion extends Model {
    public $id_asignacion;
    public $fecha;
    public $id_vendedor;
    public $id_encargado;

    public function initialize() {
        $this->setSource('asignacion');

        // Relación: Una asignación tiene muchos detalles
        $this->hasMany('id_asignacion', 'DetAsigProducto', 'id_asignacion', [
            'alias' => 'detalles'
        ]);

        // Relación: Pertenece a un vendedor (Usuario)
        $this->belongsTo('id_vendedor', 'Usuario', 'id_usuario', [
            'alias' => 'vendedor'
        ]);
    }
}