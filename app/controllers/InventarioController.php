<?php

use Phalcon\Mvc\Controller;

class InventarioController extends Controller
{
    /**
     * Muestra el stock consolidado por vendedor
     */
    public function indexAction()
    {
        // Consultamos los detalles de asignación agrupados
        // Queremos saber: Vendedor -> Producto -> Suma de cantidades
        $this->view->stockVendedores = DetAsigProducto::find([
            "group" => "id_producto, id_asignacion", // Agrupamos para organizar la lógica
            "order" => "id_asignacion DESC"
        ]);

        // Una mejor práctica es traer a los usuarios que son vendedores
        // y ver qué productos tienen asignados a través de las relaciones.
        $this->view->vendedores = Usuario::find([
            "conditions" => "rol = 'VENDEDOR' AND activo = 1"
        ]);
    }
}