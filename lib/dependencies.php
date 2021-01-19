<?php
/**
 * This file contains the dependency injection container configuration.
 * Pimple * (@see https://pimple.symfony.com) is used as the underlying 
 * dependency injection container implementation.
 */
$container = $app->getContainer();
// config
$container["config"] = function () {
    return $GLOBALS["options"];
};
// data sources
$container["ispcr"] = function ($c) {
    return new CCR\ISPCR\Service\External\IsPcrDataSource(
        new GuzzleHttp\Client(["base_uri" => $c->get("config")->ispcr->url])
    );
};
$container["easydb"] = function ($c) {
    return new \ParagonIE\EasyDB\EasyDB(
        $c->get("pdo"),
        "mysql"
    );
};
$container["latitude"] = function () {
    return new Latitude\QueryBuilder\QueryFactory("mysql");
};
$container["pdo"] = function ($c) {
    $host = $c->get("config")->database->host;
    $db = $c->get("config")->database->name;
    $user = $c->get("config")->database->user;
    $pass = $c->get("config")->database->password;
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=utf8",
        $host,
        $db
    );
    $opt = [
        \PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::MYSQL_ATTR_LOCAL_INFILE => true,
        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
    ];

    return new \PDO(
        $dsn,
        $user,
        $pass,
        $opt
    );
};
// dispatcher
$container["dispatcher"] = function ($c) {
    return new CCR\ISPCR\Service\Dispatcher\LoggedDispatcher(
        new CCR\ISPCR\Service\Dispatcher\ValidatingDispatcher(
            new CCR\ISPCR\Service\Dispatcher\SynchronizedDispatcher(
                new CCR\ISPCR\Service\Dispatcher\TransactionalDispatcher(
                    new CCR\ISPCR\Service\Dispatcher\Dispatcher(
                        new CCR\ISPCR\Service\Dispatcher\ClassNameCallableResolver($c)
                    ),
                    $c->get("easydb")
                ),
                $c->get("easydb")
            )
        ),
        $c->get("logger")
    );
};
// logger
$container["logger"] = function () {
    $logger = new Monolog\Logger("ISPCR");
    $handler = new Monolog\Handler\FingersCrossedHandler(new Monolog\Handler\ErrorLogHandler());
    $formatter = new Monolog\Formatter\LineFormatter();
    $formatter->allowInlineLineBreaks();
    $formatter->ignoreEmptyContextAndExtra();
    $handler->setFormatter($formatter);
    $logger->pushHandler($handler);

    return $logger;
};
// middlewares
$container["auth-middleware"] = function ($c) {
    return new CCR\ISPCR\Middleware\AuthMiddleware(
        $c->get("easydb"),
        $c->get("latitude"),
        $c->get("config")->general->site_auth_realm
    );
};
$container["debug-middleware"] = function ($c) {
    return new CCR\ISPCR\Middleware\DebugMiddleware();
};
// query handler
$container[CCR\ISPCR\Datasource\Query\GetAlignmentListHandler::class] = function ($c) {
    return new CCR\ISPCR\Datasource\Query\GetAlignmentListHandler(
        new CCR\ISPCR\Datasource\Service\AlignmentMatcher($c->get("ispcr")));
};
