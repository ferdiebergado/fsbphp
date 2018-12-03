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
use Noodlehaus\Config;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Apix\Cache\Files;
use Apix\Cache\Factory as CacheFactory;
use FSB\Cache\Cache;
use Monolog\Logger;
use Aura\Router\RouterContainer;
use FSB\Router\Router;
use FSB\Router\Rule\Auth;
use FSB\Router\Rule\Guest;
use Whoops\Run;
use Whoops\Handler \{
    PrettyPageHandler, PlainTextHandler
};
use Monolog\Handler\StreamHandler;
use FSB\Exception\ExceptionHandler;
use Zend\HttpHandlerRunner\Emitter \{
    SapiEmitter, SapiStreamEmitter, EmitterStack
};
use Relay\Relay;
use FSB\Emitter\ConditionalEmitter;
use Monolog\Handler\SwiftMailerHandler;
// use Bernard\Driver\FlatFileDriver;
// use Bernard\Serializer;
// use Bernard\Producer;
// use Bernard\QueueFactory\PersistentFactory;
// use Bernard\Consumer;
// use Symfony\Component\EventDispatcher\EventDispatcher;
// use Bernard\Router\SimpleRouter;
// use Bernard\EventListener\ErrorLogSubscriber;
// use Bernard\EventListener\FailureSubscriber;
// use Bernard\Symfony\ContainerAwareRouter;
use Illuminate\Queue\Capsule\Manager as Queue;
use Middlewares\Whoops;
use Enqueue\Fs\FsConnectionFactory;
use App\Job\SwiftQueueSpool;
use FSB\Queue\MailQueue;

return [
    
    /* DEPENDENCY INJECTION CONTAINER */
    Container::class => create(),
    'container' => get(Container::class),

    /* LOGGER */
    Logger::class => create()->constructor('main'),
    'logger' => get(Logger::class),
    StreamHandler::class => create()->constructor(LOG_FILE),
    'stream-handler' => get(StreamHandler::class),
    'stream-logger' => function (Logger $logger, StreamHandler $handler) {
        return $logger->pushHandler($handler);
    },
    'security-logger' => function (Logger $logger) {
        return $logger->withName('security-logger');
    },

    /* EXCEPTION HANDLER */
    Run::class => create(),
    'whoops' => get(Run::class),
    PrettyPageHandler::class => create(),
    'prettypagehandler' => get(PrettyPageHandler::class),
    PlainTextHandler::class => create()->constructor(get('stream-logger')),
    'plaintexthandler' => get(PlainTextHandler::class),
    ExceptionHandler::class => create()->constructor(get('whoops'), get('prettypagehandler'), get('plaintexthandler'), get('logger'), get('mailer'), get('config'), get('template')),
    'exception-handler' => get(ExceptionHandler::class),

    /* CONFIG */
    Dotenv::class => create()->constructor(BASE_PATH),
    'env' => get(Dotenv::class),
    Config::class => function () {
        $filenames = [
            'app',
            'cache',
            'database',
            'handlers',
            'headers',
            'http',
            'mail',
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

    /* ROUTER */
    RouterContainer::class => create(),
    'router-container' => get(RouterContainer::class),
    Auth::class => create(),
    'auth' => get(Auth::class),
    Guest::class => create(),
    'guest' => get(Guest::class),
    Router::class => function (RouterContainer $routerContainer, ContainerInterface $c) {
        return new Router($routerContainer, $c->get('auth'), $c->get('guest'));
    },
    'router' => get(Router::class),

    /* DISPATCHER */
    Relay::class => function (ContainerInterface $c, Config $config) {
        $middlewares = $config->get('middlewares');
        $resolver = function ($entry) use ($c) {
            return $c->get($entry);
        };
        return new Relay($middlewares, $resolver);
    },
    'dispatcher' => get(Relay::class),

    /* RESPONSE EMITTER */
    SapiEmitter::class => create(),
    'emitter' => get(SapiEmitter::class),
    SapiStreamEmitter::class => function (Config $config) {
        return new SapiStreamEmitter($config->get('http.maxbufferlength'));
    },
    'stream-emitter' => get(SapiStreamEmitter::class),
    EmitterStack::class => create(),
    'emitter-stack' => get(EmitterStack::class),
    ConditionalEmitter::class => create()->constructor(get('stream-emitter')),
    'conditional-emitter' => get(ConditionalEmitter::class),

    /* MIDDLEWARES */

    /* Routes */
    AuraRouter::class => create()->constructor(get('router-container'))->method('responseFactory', get('responsefactory')),
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

    /* MAIL */
    Swift_SmtpTransport::class => function (Config $config) {
        $mail = $config->get('mail');
        $swift = new Swift_SmtpTransport($mail['host'], $mail['port']);
        $swift->setUsername($mail['username']);
        $swift->setPassword($mail['password']);
        return $swift;
    },
    'swift-smtp-transport' => get(Swift_SmtpTransport::class),
    SwiftQueueSpool::class => create()->constructor(get('queue')),
    'swift-queue-spool' => get(SwiftQueueSpool::class),
    Swift_SpoolTransport::class => create()->constructor(get('swift-queue-spool')),
    'swift-spool-transport' => get(Swift_SpoolTransport::class),
    Swift_Mailer::class => create()->constructor(get('swift-spool-transport')),
    'spool-mailer' => function (ContainerInterface $c) {
        return new Swift_Mailer($c->get('swift-spool-transport'));
    },
    'mailer' => function (ContainerInterface $c) {
        return new Swift_Mailer($c->get('swift-smtp-transport'));
    },

    /* QUEUE */
    FsConnectionFactory::class => function (Config $config) {
        $connectionFactory = new FsConnectionFactory($config->get('queue'));
        $context = $connectionFactory->createContext();
        return $context;
    },
    'queue' => get(FsConnectionFactory::class),
    MailQueue::class => create()->constructor(get('swift-spool-transport'), get('swift-smtp-transport')),
    'mail-queue' => get(MailQueue::class),

    // Serializer::class => create(),
    // 'queue-serializer' => get(Serializer::class),
    // ErrorLogSubscriber::class => create(),
    // 'errorlog-subscriber' => get(ErrorLogSubscriber::class),
    // FailureSubscriber::class => create()->constructor(get('queue-producer')),
    // 'failure-subscriber' => get(FailureSubscriber::class),
    // EventDispatcher::class => create()->method('addSubscriber', get('errorlog-subscriber'))->method('addSubscriber', get('failure-subscriber')),
    // 'event-dispatcher' => get(EventDispatcher::class),
    // FlatFileDriver::class => create()->constructor(CACHE_PATH . 'queue'),
    // 'queue-driver' => get(FlatFileDriver::class),
    // PersistentFactory::class => create()->constructor(get('queue-driver'), get('queue-serializer')),
    // 'queue-factory' => get(PersistentFactory::class),
    // Producer::class => create()->constructor(get('queue-factory'), get('event-dispatcher')),
    // 'queue-producer' => get(Producer::class),
    // ContainerAwareRouter::class => function (ContainerInterface $c, Config $config) {
    //     $receivers = $config->get('queue.receivers');
    //     return new ContainerAwareRouter($c, $receivers);
    // },
    // 'queue-router' => get(ContainerAwareRouter::class),
    // Consumer::class => create()->constructor(get('queue-router'), get('event-dispatcher')),
    // 'queue-consumer' => get(Consumer::class),

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
