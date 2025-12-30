<?php

use Phalcon\Mvc\Controller;

class AsignacionController extends Controller {

    // ... acciones anteriores (index, nuevo) ...
    public function indexAction() {
        // Consultamos todas las asignaciones
        $this->view->asignaciones = Asignacion::find([
            "order" => "fecha DESC"
        ]);
    }
    
    public function nuevoAction() 
    {
        // Consultamos vendedores para el select
        $this->view->vendedores = Usuario::find("rol = 'VENDEDOR' AND activo = 1");

        // Consultamos productos activos
        $this->view->productos = Producto::find("activo = 1");
    }

    public function crearAction() {
        // 1. Verificación de seguridad
        if (!$this->request->isPost()) {
            return $this->response->redirect("asignacion");
        }

        // 2. Iniciar una Transacción
        // Esto protege la integridad de los datos en paulita_db
        $this->db->begin();

        try {
            // 3. Crear la Cabecera (Asignacion)
            $asignacion = new Asignacion();
            $asignacion->fecha = $this->request->getPost("fecha");
            $asignacion->id_vendedor = $this->request->getPost("id_vendedor");
            $asignacion->id_encargado = 1; // ID temporal hasta tener Login

            // USAMOS EL ID REAL DE LA SESIÓN
            $auth = $this->session->get('auth');
            $asignacion->id_encargado = $auth['id'];

            if (!$asignacion->save()) {
                // Si la cabecera falla, lanzamos una excepción para ir al catch
                throw new Exception("Error al crear la cabecera de asignación.");
            }

            // 4. Crear el Detalle (DetAsigProducto)
            $detalle = new DetAsigProducto();
            $detalle->id_asignacion = $asignacion->id_asignacion; // ID recién generado
            $detalle->id_producto = $this->request->getPost("id_producto");
            $detalle->cantidad = $this->request->getPost("cantidad");

            if (!$detalle->save()) {
                throw new Exception("Error al registrar el producto en la asignación.");
            }

            // 5. Si todo llegó aquí sin errores, confirmamos los cambios en la BD
            $this->db->commit();
            $this->flash->success("¡Asignación guardada y stock vinculado correctamente!");
            return $this->response->redirect("asignacion");

        } catch (Exception $e) {
            // 6. Si algo falló, deshacemos todo lo que se intentó escribir
            $this->db->rollback();
            $this->flash->error($e->getMessage());
            return $this->response->redirect("asignacion/nuevo");
        }
    }
}