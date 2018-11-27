<?php


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
use Psr\Container\ContainerInterface;
use League\Tactician\Container\ContainerLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use App\Handler\LogoutHandler;
use Valitron\Validator;
use App\Handler\LoginHandler;
use function DI \{
    create, get
};
use Zend\Diactoros\ServerRequestFactory;
use Middleland\Dispatcher;
use Aura\Router\RouterContainer;
use Middlewares\RequestHandler;
use Middlewares\AuraRouter;
use Zend\Diactoros\ResponseFactory;

$session = require(CONFIG_PATH . 'session.php');
$view = require(CONFIG_PATH . 'view.php');
$handlermap = require(CONFIG_PATH . 'handlers.php');

return [

    /* DEPENDENCY INJECTION CONTAINER */
    Container::class => create(),
    'container' => get(Container::class),

    /* PSR-7 HTTP MESSAGE IMPLEMENTATION */
    ServerRequestFactory::class => create(),
    'serverrequest' => get(ServerRequestFactory::class),
    ResponseFactory::class => create(),
    'responsefactory' => get(ResponseFactory::class),
    'request' => function (ContainerInterface $c) {
        $serverrequest = $c->get('serverrequest');
        return $serverrequest->fromGlobals();
    },    

    /* Router */
    RouterContainer::class => create(),
    'router' => get(RouterContainer::class),
    // 'routes' => function (ContainerInterface $c) {
    //     $router = $c->get('router');
    //     $map = $router->getMap();
    //     $routes = include(CONFIG_PATH . 'routes.php');
    //     return $routes;
    // },

    /* MIDDLEWARES */

    /* Routes */
    AuraRouter::class => create()->constructor(get('router')),
    'mw_router' => get(AuraRouter::class),

    /* Request-Handler */
    RequestHandler::class => create()->constructor(get('container')),
    'requesthandler' => get(RequestHandler::class),

    /* Headers */
    HeadersMiddleware::class => create(),
    'headers' => get(HeadersMiddleware::class),

    /* Session */
    SessionFactory::class => create(),
    'sessionfactory' => get(SessionFactory::class),
    Session::class => create()->constructor(get('sessionfactory'), $session),
    'session' => get(Session::class),
    AuraSessionMiddleware::class => create()->constructor(get('sessionfactory'), $session)->method('name', $session['name']),
    'mw_session' => get(AuraSessionMiddleware::class),

    /* Content-Type Negotiation */
    ContentType::class => create(),
    'content-type' => get(ContentType::class),

    /* CSRF Protection */
    VerifyCsrfTokenMiddleware::class => create()->constructor(get('responsefactory')),
    'csrf' => get(VerifyCsrfTokenMiddleware::class),

    /* Authentication */
    AuthMiddleware::class => create()->constructor(get('responsefactory')),
    'auth' => get(AuthMiddleware::class),
    GuestMiddleware::class => create()->constructor(get('responsefactory')),
    'guest' => get(GuestMiddleware::class),

    /* COMMAND BUS */
    ClassNameExtractor::class => create(),
    'extractor' => get(ClassNameExtractor::class),
    HandleInflector::class => create(),
    'inflector' => get(HandleInflector::class),
    ContainerLocator::class => create()->constructor(get('container'), $handlermap),
    'locator' => get(ContainerLocator::class),
    CommandHandlerMiddleware::class => create()->constructor(get('extractor'), get('locator'), get('inflector')),
    'commandhandler' => get(CommandHandlerMiddleware::class),
    CommandBus::class => create()->constructor([get('commandhandler')]),
    'commandbus' => get(CommandBus::class),

    /* TEMPLATE ENGINE */
    Twig_Loader_Filesystem::class => create()->constructor(VIEW_PATH),
    'loader' => get(Twig_Loader_Filesystem::class),
    Twig_Environment::class => create()->constructor(get('loader'), $view)->method('addExtension', get('apptwigext')),
    'twig' => get(Twig_Environment::class),
    TwigTemplate::class => create()->constructor(get('twig')),
    TemplateInterface::class => get(TwigTemplate::class),
    'template' => get(TemplateInterface::class),
    AppTwigExtension::class => create()->constructor(get('session'), get('request')),
    'apptwigext' => get(AppTwigExtension::class),

    /* INPUT VALIDATOR */
    Validator::class => function (ContainerInterface $c) {
        $request = $c->get('request');
        $post = $request->getParsedBody();
        foreach ($post as $key => $value) {
            $post[$key] = test_input($post[$key]);
            $post[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
        }
        return new Validator($post);
    },
    'validator' => get(Validator::class),

    /* CONTROLLERS */
    HomeController::class => create()->constructor(get('responsefactory'), get('template'), get('commandbus'), get('validator')),
    LoginController::class => create()->constructor(get('responsefactory'), get('template'), get('commandbus'), get('validator')),
    UserController::class => create()->constructor(get('responsefactory'), get('template'), get('commandbus'), get('validator')),

    /* COMMAND HANDLERS */
    LogoutHandler::class => create(),
    LoginHandler::class => create(),

];
