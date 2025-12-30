<?php

use Phalcon\Mvc\Controller;

class UsuarioController extends Controller
{
    public function indexAction()
    {
        // Listamos todos los usuarios para la gestión del administrador
        $this->view->usuarios = Usuario::find([
            "order" => "rol, nombre"
        ]);
    }

    public function nuevoAction()
    {
        // Solo muestra la vista del formulario
    }

    public function crearAction()
    {
        if (!$this->request->isPost()) {
            return $this->response->redirect("usuario");
        }

        $usuario = new Usuario();
        
        // Asignamos datos básicos
        $usuario->nombre = $this->request->getPost("nombre");
        $usuario->email  = $this->request->getPost("email", "email");
        $usuario->rol    = $this->request->getPost("rol");
        $usuario->activo = 1;

        // SEGURIDAD: Encriptar la contraseña
        // El componente security de Phalcon usa Argon2 o Bcrypt por defecto
        $password = $this->request->getPost("password");
        $usuario->password_hash = $this->security->hash($password);

        if ($usuario->save()) {
            $this->flash->success("Usuario creado con éxito");
            return $this->response->redirect("usuario");
        } else {
            foreach ($usuario->getMessages() as $m) {
                $this->flash->error((string) $m);
            }
            return $this->response->redirect("usuario/nuevo");
        }
    }

    public function editarAction($id)
    {
        // Solo el ADMIN entra aquí
        if ($this->session->get('auth')['rol'] !== 'ADMIN') {
            $this->flash->error("Acceso denegado.");
            return $this->response->redirect('index');
        }

        $usuario = Usuario::findFirstByIdUsuario($id);
        if (!$usuario) {
            $this->flash->error("Usuario no encontrado.");
            return $this->response->redirect('usuario');
        }

        $this->view->usuario = $usuario;
    }

    public function guardarAction()
    {
        if (!$this->request->isPost()) return $this->response->redirect('usuario');

        $id = $this->request->getPost("id_usuario");
        $usuario = Usuario::findFirstByIdUsuario($id);

        if ($usuario) {
            $usuario->nombre = $this->request->getPost("nombre", "striptags");
            $usuario->email  = $this->request->getPost("email", "email");
            $usuario->rol    = $this->request->getPost("rol");

            // Lógica de contraseña: Si el campo no está vacío, hasheamos la nueva
            $password = $this->request->getPost("password");
            if (!empty($password)) {
                $usuario->password_hash = $this->security->hash($password);
            }

            if ($usuario->save()) {
                $this->flash->success("Usuario actualizado con éxito.");
            } else {
                foreach ($usuario->getMessages() as $m) $this->flash->error((string) $m);
            }
        }
        
        return $this->response->redirect('usuario');
    }

    public function eliminarAction($id)
    {
        // No borramos físicamente, "desactivamos" (Soft Delete)
        $usuario = Usuario::findFirstByIdUsuario($id);
        if ($usuario && $this->session->get('auth')['rol'] === 'ADMIN') {
            $usuario->activo = 0; 
            $usuario->save();
            $this->flash->notice("Usuario desactivado.");
        }
        return $this->response->redirect('usuario');
    }
}