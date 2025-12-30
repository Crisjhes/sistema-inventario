<?php
use Phalcon\Mvc\Model;

class Producto extends Model {
    public $id_producto; // Llave primaria
    public $sku;
    public $nombre;
    public $descripcion;
    public $id_categoria;
    public $id_proveedor;
    public $stock_min;
    public $activo;


    public function initialize() {

        $this->setSource('producto'); // Asegúrate que en la BD se llame 'producto' (singular)

        // Relación: Muchos productos pertenecen a una categoría
        $this->belongsTo('id_categoria', 'Categoria', 'id_categoria', [
            'alias' => 'categoria',
            'reusable' => true // Mejora el rendimiento
        ]);

        // Relación: Muchos productos pertenecen a un proveedor
        $this->belongsTo('id_proveedor', 'Proveedor', 'id_proveedor', [
            'alias' => 'proveedor'
        ]);
    }

    public function getStockActual() {
        // Sumar todas las asignaciones de este producto
        $asignado = DetAsigProducto::sum([
            'column'     => 'cantidad',
            'conditions' => 'id_producto = :id:',
            'bind'       => ['id' => $this->id_producto]
        ]);

        // Por ahora, como no hay ventas, el stock es igual a lo asignado
        return $asignado ? $asignado : 0;
    }
}