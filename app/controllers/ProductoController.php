<?php

use Phalcon\Mvc\Controller;

class ProductoController extends Controller
{
    // Muestra la lista de productos
    public function indexAction()
{
    // Obtenemos todos los productos. 
    // Phalcon cargará automáticamente las relaciones (categoria, proveedor) 
    // cuando las llamemos en la vista gracias al initialize() del modelo.
    $this->view->productos = Producto::find([
        "order" => "id_producto DESC" // Los más nuevos primero
    ]);
}
    // Muestra el formulario vacío
    public function nuevoAction()
    {
        $this->view->categorias = Categoria::find("activo = 1");
        $this->view->proveedores = Proveedor::find("activo = 1");
    }

    // --- AQUÍ VA EL FRAGMENTO NUEVO ---
    public function crearAction()
    {
        // 1. Verificamos que los datos vengan por el formulario (POST)
        if (!$this->request->isPost()) {
            return $this->response->redirect("producto");
        }

        // 2. Creamos una nueva instancia del modelo
        $producto = new Producto();

        // 3. Asignamos los valores manualmente (Mapeo)
        // El primer parámetro es el "name" del input en tu HTML
        $producto->sku          = $this->request->getPost("sku");
        $producto->nombre       = $this->request->getPost("nombre");
        $producto->descripcion  = $this->request->getPost("descripcion");
        $producto->id_categoria = $this->request->getPost("id_categoria");
        $producto->id_proveedor = $this->request->getPost("id_proveedor");
        $producto->stock_min    = $this->request->getPost("stock_min", "int", 0);
        $producto->activo       = 1;

        // 4. Intentamos guardar en la base de datos
        if ($producto->save()) {
            // Si funciona, enviamos mensaje de éxito y redirigimos al listado
            $this->flash->success("¡Producto guardado exitosamente!");
            return $this->response->redirect("producto");
        } else {
            // Si falla (ej. SKU duplicado), mostramos por qué
            foreach ($producto->getMessages() as $message) {
                $this->flash->error((string) $message);
            }
            // Regresamos al formulario para corregir los errores
            return $this->response->redirect("producto/nuevo");
        }
    }

    public function editarAction($id)
    {
        $producto = Producto::findFirstByIdProducto($id);
        $this->view->producto = $producto;
        $this->view->categorias = Categoria::find();
        $this->view->proveedores = Proveedor::find();
    }

    public function eliminarAction($id)
    {
        $producto = Producto::findFirstByIdProducto($id);
        
        if ($producto) {
            $producto->activo = 0; // Lo desactivamos
            if ($producto->save()) {
                $this->flash->notice("El producto '{$producto->nombre}' ha sido desactivado (No se puede eliminar por tener historial).");
            }
        }
        
        return $this->response->redirect('producto');
    }

    public function guardarAction()
    {
        // 1. Solo permitimos acceso por POST para proteger los datos
        if (!$this->request->isPost()) {
            return $this->response->redirect('producto');
        }

        // 2. Obtenemos el ID del producto que viene del campo 'hidden'
        $id = $this->request->getPost("id_producto");
        $producto = Producto::findFirstByIdProducto($id);

        if (!$producto) {
            $this->flash->error("Error: El producto no existe.");
            return $this->response->redirect('producto');
        }

        // 3. Asignamos los nuevos valores filtrando el texto
        // 'striptags' elimina código HTML malicioso
        $producto->nombre       = $this->request->getPost("nombre", "striptags");
        $producto->sku          = $this->request->getPost("sku", "alphanum");
        $producto->id_categoria = $this->request->getPost("id_categoria", "int");
        $producto->stock_min    = $this->request->getPost("stock_min", "int");

        // 4. Intentamos guardar en la BD
        if ($producto->save()) {
            $this->flash->success("Producto '{$producto->nombre}' actualizado correctamente.");
            return $this->response->redirect('producto');
        } else {
            // 5. Si hay errores (ej. SKU duplicado), los mostramos
            foreach ($producto->getMessages() as $message) {
                $this->flash->error((string) $message);
            }
            return $this->response->redirect('producto/editar/' . $id);
        }
    }
}