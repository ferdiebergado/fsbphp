<?php

use function DI \{
    create, get
};
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Zend\Diactoros \{
    ServerRequestFactory, ResponseFactory
};
use FSB\Container;
use Middlewares \{
    ContentType, RequestHandler, ClientIp, ErrorHandler
};
use FSB\Middleware \{
    HeadersMiddleware, VerifyCsrfTokenMiddleware, AuraSessionMiddleware, AuraRouter, ErrorRequestHandler
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
use Middleland\Dispatcher;
use Aura\Router\RouterContainer;
use FSB\Router\Router;
use Noodlehaus\Config;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Apix\Cache\Files;
use Apix\Cache\Factory as CacheFactory;
use FSB\Cache\Cache;

return [

    /* DEPENDENCY INJECTION CONTAINER */
    Container::class => create(),
    'container' => get(Container::class),

    /* Config */
    Dotenv::class => create()->constructor(BASE_PATH),
    'env' => get(Dotenv::class),
    Config::class => function () {
        $filenames = [
            'app',
            'cache',
            'database',
            'handlers',
            'headers',
            'middlewares',
            'session',
            'view'
        ];
        $ext = '.php';
        foreach ($filenames as $file) {
            $files[] = __DIR__ . '/' . $file . $ext;
        }
        return new Config($files);
    },
    'config' => get(Config::class),

    /* PSR-7 HTTP MESSAGE IMPLEMENTATION */
    ServerRequestFactory::class => create(),
    'serverrequest' => get(ServerRequestFactory::class),
    ResponseFactory::class => create(),
    'responsefactory' => get(ResponseFactory::class),
    'request' => function (ServerRequestFactory $serverRequest) {
        return $serverRequest::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );
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
    HeadersMiddleware::class => create()->constructor(get('config')),
    'headers' => get(HeadersMiddleware::class),

    /* Session */
    SessionFactory::class => create(),
    'sessionfactory' => get(SessionFactory::class),
    AuraSessionMiddleware::class => create()->constructor(get('sessionfactory'), get('config')),
    // ->method('name', $session['name']),
    'mw_session' => get(AuraSessionMiddleware::class),

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

    /* ORM */
    Capsule::class => create(),
    'capsule' => get(Capsule::class),

    /* CACHE */
    Files::class => function (Config $config) {
        return new Files($config->get('cache'));
    },
    'filecache' => get(Files::class),
    // CacheFactory::class => create()->method('getTaggablePool', get('filecache')),
    // 'cache' => get(CacheFactory::class),
    Cache::class => create()->constructor(get('filecache')),
    'cache' => get(Cache::class),

    /* COMMAND BUS */
    ClassNameExtractor::class => create(),
    'extractor' => get(ClassNameExtractor::class),
    HandleInflector::class => create(),
    'inflector' => get(HandleInflector::class),
    ContainerLocator::class => function (ContainerInterface $c, Config $config) {
        return new ContainerLocator($c, $config->get('handlers'));
    },
    'locator' => get(ContainerLocator::class),
    CommandHandlerMiddleware::class => create()->constructor(get('extractor'), get('locator'), get('inflector')),
    'commandhandler' => get(CommandHandlerMiddleware::class),
    CommandBus::class => create()->constructor([get('commandhandler')]),
    'commandbus' => get(CommandBus::class),

    /* TEMPLATE ENGINE */
    Twig_Loader_Filesystem::class => create()->constructor(VIEW_PATH),
    'loader' => get(Twig_Loader_Filesystem::class),
    Twig_Environment::class => function (Twig_Loader_Filesystem $loader, Config $config, AppTwigExtension $twigExt) {
        $view = $config->get('view');
        $twig = new Twig_Environment($loader, $view);
        $twig->addExtension($twigExt);
        return $twig;
    },
    'twig' => get(Twig_Environment::class),
    TwigTemplate::class => create()->constructor(get('twig')),
    'template' => get(TwigTemplate::class),
    AppTwigExtension::class => create()->constructor(get('sessionfactory')),
    'apptwigext' => get(AppTwigExtension::class),

    /* CONTROLLERS */
    HomeController::class => create()->constructor(get('template'), get('commandbus')),
    LoginController::class => create()->constructor(get('template'), get('commandbus')),
    UserController::class => create()->constructor(get('template'), get('commandbus')),

    /* COMMAND HANDLERS */
    LogoutHandler::class => create(),
    LoginHandler::class => create()->constructor(get('cache')),
    UserShowHandler::class => create()->constructor(get('cache')),

];
