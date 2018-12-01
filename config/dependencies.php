<?php

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Zend\Diactoros \{
    ServerRequestFactory, ResponseFactory
};
use FSB\Container;
use Middlewares \{
    ContentType, RequestHandler
};
use FSB\Middleware \{
    HeadersMiddleware, VerifyCsrfTokenMiddleware, AuraSessionMiddleware, AuthMiddleware, GuestMiddleware, SanitizeInputMiddleware, AuraRouter
};
use FSB\Session \{
    Session
};
use App\Controller \{
    HomeController, LoginController, UserController
};
use App\View\Template\Twig\TwigTemplate;
use App\View\Template\TemplateInterface;
use App\View\Template\Twig\Extension\AppTwigExtension;
use Aura\Session\SessionFactory;
use League\Tactician\Container\ContainerLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use App\Handler \{
    LogoutHandler, LoginHandler, UserShowHandler
};
use Valitron\Validator;
use function DI \{
    create, get
};
use Middleland\Dispatcher;
use Aura\Router\RouterContainer;
use FSB\Router\Router;
use FSB\Middleware\SetRequestAttributesMiddleware;
use Middlewares\ClientIp;
use Middlewares\ErrorHandler;
use FSB\Middleware\ErrorRequestHandler;

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
    'routercontainer' => get(RouterContainer::class),
    Router::class => create()->constructor(get('routercontainer')),
    'router' => get(Router::class),

    /* MIDDLEWARES */

    /* Routes */
    AuraRouter::class => create()->constructor(get('routercontainer'))->method('responseFactory', get('responsefactory')),
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
    AuraSessionMiddleware::class => create()->constructor(get('sessionfactory'), $session)->method('name', $session['name']),
    'mw_session' => get(AuraSessionMiddleware::class),

    /* Authenticated User */
    SetRequestAttributesMiddleware::class => create(),
    'set-attributes' => get(SetRequestAttributesMiddleware::class),

    /* Content-Type Negotiation */
    ContentType::class => create(),
    'content-type' => get(ContentType::class),

    /* CSRF Protection */
    VerifyCsrfTokenMiddleware::class => create()->constructor(get('responsefactory')),
    'csrf' => get(VerifyCsrfTokenMiddleware::class),

    /* Client IP */
    ClientIp::class => create(),
    'client-ip' => get(ClientIp::class),

    /* HTTP Error Handler */
    ErrorRequestHandler::class => create()->constructor(get('template')),
    'error-request' => get(ErrorRequestHandler::class),
    ErrorHandler::class => create()->constructor(get('error-request')),
    'error-handler' => get(ErrorHandler::class),

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
    'template' => get(TwigTemplate::class),
    AppTwigExtension::class => create()->constructor(get('sessionfactory')),
    'apptwigext' => get(AppTwigExtension::class),

    /* INPUT VALIDATOR */
    // Validator::class => create(),
    // 'validator' => get(Validator::class),

    /* CONTROLLERS */
    HomeController::class => create()->constructor(get('responsefactory'), get('template'), get('commandbus'), get('validator')),
    LoginController::class => create()->constructor(get('responsefactory'), get('template'), get('commandbus'), get('validator')),
    UserController::class => create()->constructor(get('responsefactory'), get('template'), get('commandbus'), get('validator')),

    /* COMMAND HANDLERS */
    LogoutHandler::class => create(),
    LoginHandler::class => create(),
    UserShowHandler::class => create(),

];
