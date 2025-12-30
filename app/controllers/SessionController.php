<?php

use Phalcon\Mvc\Controller;

class SessionController extends Controller
{
    // Muestra el formulario de acceso
    public function indexAction()
    {
        // Si ya está logueado, lo mandamos al index
        if ($this->session->has('auth')) {
            return $this->response->redirect('index');
        }
    }

    // Procesa el intento de login
    public function startAction()
    {
        if ($this->request->isPost()) {
            $email    = $this->request->getPost('email', 'email');
            $password = $this->request->getPost('password');

            // 1. Buscar al usuario por email
            $user = Usuario::findFirst([
                "conditions" => "email = :email: AND activo = 1",
                "bind"       => ["email" => $email]
            ]);

            if ($user) {
                // 2. Verificar la contraseña usando el componente Security
                // Esto compara el texto plano con el hash de la BD
                if ($this->security->checkHash($password, $user->password_hash)) {
                    
                    // 3. Crear la sesión (Guardamos datos básicos)
                    $this->session->set('auth', [
                        'id'     => $user->id_usuario,
                        'nombre' => $user->nombre,
                        'rol'    => $user->rol
                    ]);

                    $this->flash->success("Bienvenido(a) " . $user->nombre);
                    return $this->response->redirect('index');
                }
            }

            // Si falla el usuario o la clave, mandamos error genérico (Seguridad)
            $this->flash->error("Credenciales incorrectas o cuenta inactiva.");
            return $this->response->redirect('session');
        }
    }

    // Cierra la sesión
    public function endAction()
    {
        $this->session->remove('auth');
        $this->flash->notice("Sesión finalizada.");
        return $this->response->redirect('session');
    }
}