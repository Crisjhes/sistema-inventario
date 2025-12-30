<?php
use Phalcon\Mvc\Model;

class DetAsigProducto extends Model {
    public $id_det_asig_producto;
    public $id_asignacion;
    public $id_producto;
    public $cantidad;

    public function initialize() {
        $this->setSource('det_asig_producto');

        // Relación: Cada detalle pertenece a una asignación maestra
        $this->belongsTo('id_asignacion', 'Asignacion', 'id_asignacion');
        
        // Relación: Cada detalle está vinculado a un producto
        $this->belongsTo('id_producto', 'Producto', 'id_producto', [
            'alias' => 'producto'
        ]);
    }
}