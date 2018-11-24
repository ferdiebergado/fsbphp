<?php

use function DI \{
    create, get
};
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseFactoryInterface;
use Middlewares \{
    ContentType
};
use App\Controller \{
    HomeController, LoginController, UserController
};
use FSB\Middleware \{
    HeadersMiddleware, VerifyCsrfTokenMiddleware, AuraSessionMiddleware, AuthMiddleware, GuestMiddleware
};
use FSB\Session \{
    Session, SessionHelper
};
use FSB\Container;
use App\View\Template\Twig\TwigTemplate;
use App\View\Template\TemplateInterface;
use App\View\Template\Twig\Extension\AppTwigExtension;
use Aura\Session\SessionFactory;
use Northwoods\Broker\Broker;
use Psr\Container\ContainerInterface;
use League\Tactician\Container\ContainerLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use App\Handler\LogoutHandler;
use Valitron\Validator;
use FSB\Middleware\RouterMiddleware;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Router as LeagueRouter;

// $router = require(CONFIG_PATH . 'router.php');
$session = require(CONFIG_PATH . 'session.php');
$view = require(CONFIG_PATH . 'view.php');
// $authroutes = require(CONFIG_PATH . 'auth.php');
$handlermap = require(CONFIG_PATH . 'handlers.php');

return [
    Psr17Factory::class => create(),
    'psr17factory' => get(Psr17Factory::class),
    ServerRequestCreator::class => create()->constructor(get('psr17factory'), get('psr17factory'), get('psr17factory'), get('psr17factory')),
    'serverrequest' => get(ServerRequestCreator::class),
    'request' => function (ContainerInterface $c) {
        $serverrequest = $c->get('serverrequest');
        return $serverrequest->fromGlobals();
    },
    Broker::class => create(),
    'dispatcher' => get(Broker::class),
    ApplicationStrategy::class => create()->method('setContainer', get('container')),
    'strategy' => get(ApplicationStrategy::class),
    LeagueRouter::class => create()->method('setStrategy', get('strategy')),
    'router' => get(LeagueRouter::class),
    'routes' => function (ContainerInterface $c) {
        $router = $c->get('router');
        $routes = include(CONFIG_PATH . 'routes.php');
        // foreach ($routes as $route) {
        //     $router->map($route[0], $route[1], $namespace . $route[2][0] . '::' . $route[2][1]);
        // }
        return $router;
    },
    RouterMiddleware::class => create()->constructor(get('routes'), get('psr17factory')),
    'mw_router' => get(RouterMiddleware::class),
    // SessionMiddleware::class => create()->constructor($session['name'], $session['cookie_lifetime'], $session['save_path'], null, null),
    HeadersMiddleware::class => create(),
    'headers' => get(HeadersMiddleware::class),
    SessionFactory::class => create(),
    'sessionfactory' => get(SessionFactory::class),
    Session::class => create()->constructor(get('sessionfactory')),
    'session' => get(Session::class),
    ContentType::class => create(),
    'content-type' => get(ContentType::class),
    AuraSessionMiddleware::class => create()->constructor(get('sessionfactory'), $session)->method('name', $session['name']),
    'mw_session' => get(AuraSessionMiddleware::class),
    VerifyCsrfTokenMiddleware::class => create()->constructor(get('psr17factory')),
    'csrf' => get(VerifyCsrfTokenMiddleware::class),
    AuthMiddleware::class => create()->constructor(get('psr17factory')),
    'auth' => get(AuthMiddleware::class),
    GuestMiddleware::class => create()->constructor(get('psr17factory')),
    'guest' => get(GuestMiddleware::class),
    Container::class => create(),
    'container' => get(Container::class),
    ClassNameExtractor::class => create(),
    'extractor' => get(ClassNameExtractor::class),
    ContainerLocator::class => create()->constructor(get('container'), $handlermap),
    'locator' => get(ContainerLocator::class),
    HandleInflector::class => create(),
    'inflector' => get(HandleInflector::class),
    CommandHandlerMiddleware::class => create()->constructor(get('extractor'), get('locator'), get('inflector')),
    'commandhandler' => get(CommandHandlerMiddleware::class),
    CommandBus::class => create()->constructor([get('commandhandler')]),
    'commandbus' => get(CommandBus::class),
    Twig_Loader_Filesystem::class => create()->constructor(VIEW_PATH),
    'loader' => get(Twig_Loader_Filesystem::class),
    Twig_Environment::class => create()->constructor(get('loader'), $view)->method('addExtension', get('apptwigext')),
    'twig' => get(Twig_Environment::class),
    TwigTemplate::class => create()->constructor(get('twig')),
    TemplateInterface::class => get(TwigTemplate::class),
    'template' => get(TemplateInterface::class),
    AppTwigExtension::class => create()->constructor(get('session')),
    'apptwigext' => get(AppTwigExtension::class),
    Validator::class => create()->constructor($_POST),
    'validator' => get(Validator::class),
    HomeController::class => create()->constructor(get('psr17factory'), get('template'), get('commandbus'), get('validator')),
    LoginController::class => create()->constructor(get('psr17factory'), get('template'), get('commandbus'), get('validator')),
    UserController::class => create()->constructor(get('psr17factory'), get('template'), get('commandbus'), get('validator')),
    LogoutHandler::class => create()
];
