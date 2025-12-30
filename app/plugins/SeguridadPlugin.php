<?php

use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

class SeguridadPlugin extends Injectable
{
    public function beforeDispatch(Event $event, Dispatcher $dispatcher)
    {
        // 1. Obtenemos el nombre del controlador al que intenta entrar
        $controller = $dispatcher->getControllerName();

        // 2. Definimos qué controladores son PÚBLICOS (Cualquiera puede entrar)
        $publicos = [
            'session', // El login debe ser público para poder entrar
            //'index'  // La página de inicio (opcional)
        ];

        // 3. Revisamos si el usuario tiene sesión activa
        $auth = $this->session->get('auth');

        // 4. Lógica de bloqueo
        // Si el controlador NO es público y el usuario NO está logueado...
        if (!in_array($controller, $publicos) && !$auth) {
            
            $this->flash->error("Debe iniciar sesión para acceder a esta sección.");
            
            // Lo mandamos al login de forma interna
            $dispatcher->forward([
                'controller' => 'session',
                'action'     => 'index'
            ]);

            // Detenemos la ejecución
            return false;
        }
    }
}