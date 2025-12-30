<?php
use Phalcon\Flash\Direct as FlashDirect;
use Phalcon\Html\Escaper;
use Phalcon\Autoload\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager as EventsManager;

/*
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// 1. Registrar cargador de clases (Sintaxis Phalcon 5)
$loader = new Loader();
$loader->setDirectories([
    APP_PATH . '/controllers/',
    APP_PATH . '/models/',
]);
$loader->register();

// 2. Crear Inyector de Dependencias
$di = new FactoryDefault();

// 3. Configurar Vistas
$di->setShared('view', function () {
    $view = new View();
    $view->setViewsDir(APP_PATH . '/views/');
    return $view;
});

// 4. Configurar URL
$di->setShared('url', function () {
    $url = new UrlProvider();
    $url->setBaseUri('/inventario/');
    return $url;
});

$application = new Application($di);

try {
    // Obtenemos la URL desde el parámetro _url que envía el .htaccess
    $url = $_GET['_url'] ?? '/';
    $response = $application->handle($url);
    $response->send();
} catch (\Exception $e) {
    echo 'Excepción en el arranque: ', $e->getMessage();
}
*/

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// 1. CARGAR LA CONFIGURACIÓN
$config = include APP_PATH . '/config/config.php';

// 2. REGISTRAR DIRECTORIOS (usando la config)
$loader = new Loader();
$loader->setDirectories([
    $config->application->controllersDir,
    $config->application->modelsDir,
    $config->application->pluginsDir,
]);
$loader->register();

// 3. CREAR EL CONTENEDOR DE SERVICIOS (DI)
$di = new FactoryDefault();

// 4. SERVICIO DE BASE DE DATOS (usando la config)
$di->setShared('db', function () use ($config) {
    return new DbAdapter([
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ]);
});

// 5. SERVICIO DE VISTAS
$di->setShared('view', function () use ($config) {
    $view = new View();
    $view->setViewsDir($config->application->viewsDir);
    return $view;
});

// 6. SERVICIO DE URL
$di->setShared('url', function () use ($config) {
    $url = new UrlProvider();
    $url->setBaseUri($config->application->baseUri);
    return $url;
});

// AGREGANDO FLASH PARA MI CONTROLADOR
$di->setShared('flash', function () {
    // 1. En Phalcon 5, Flash necesita el Escaper para seguridad
    $escaper = new Escaper();
    $flash = new FlashDirect($escaper);

    // 2. Definimos las clases CSS por separado
    $flash->setCssClasses([
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning',
    ]);

    return $flash;
});

// SEESION O LOGIN
$di->setShared('session', function () {
    $session = new Manager();
    
    // Usamos sys_get_temp_dir() para que PHP elija la carpeta temporal 
    // correcta del sistema (usualmente C:\xampp\tmp en Windows)
    $files = new Stream([
        'savePath' => sys_get_temp_dir(),
    ]);
    
    $session->setAdapter($files);
    $session->start();

    return $session;
});

// PARA EL GUARDIAN EN EL LOGIN Y NO INGRESEN DIRECTAMENTE
$di->setShared('dispatcher', function () {
    // Creamos un manejador de eventos
    $eventsManager = new EventsManager();

    // Instanciamos nuestro Plugin y lo vinculamos al manejador
    $seguridad = new SeguridadPlugin();
    $eventsManager->attach('dispatch:beforeDispatch', $seguridad);

    $dispatcher = new Dispatcher();
    
    // Le asignamos el manejador de eventos al dispatcher
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});

$application = new Application($di);


try {
    $urlPath = $_GET['_url'] ?? '/';
    $response = $application->handle($urlPath);
    $response->send();
} catch (\Exception $e) {
    echo 'Error: ', $e->getMessage();
}