<?php

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        // Si el SeguridadPlugin ya protege este controlador, 
        // solo los logueados llegarán aquí.
        $this->view->totalProductos = Producto::count();
        $this->view->totalUsuarios = Usuario::count("activo = 1");
    }
}