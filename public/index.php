<?php
declare(strict_types=1);

use Beste\Clock\SystemClock;
use DI\Container;
use Monolog\{Level, Logger};
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use CallForwardingVoicemail\{CallForwardingHandler, CallForwardingHandlerOptions, VoiceRecordingTranscriptionHandler};
use Twilio\Rest\Client;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();
$dotenv
    ->required([
        'MY_PHONE_NUMBER',
        'TWILIO_ACCOUNT_SID',
        'TWILIO_AUTH_TOKEN'
    ])
    ->notEmpty();

// Set the default timezone, if it's not already set, defaulting to UTC, if the TIMEZONE environment
// variable has not been set or is empty.
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'UTC');

$container = new Container();
$container->set(
    Client::class,
    fn () => new Client($_ENV["TWILIO_ACCOUNT_SID"], $_ENV["TWILIO_AUTH_TOKEN"])
);

$container->set(
    LoggerInterface::class,
    fn () => (new Logger('name'))
        ->pushHandler(
            new StreamHandler(
                __DIR__ . "/../app.log",
                Level::Debug
            )
        )
);

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->post('/', new CallForwardingHandler(
    SystemClock::create(),
    new CallForwardingHandlerOptions(
        $_ENV['WORK_WEEK_START'],
        $_ENV['WORK_WEEK_END'],
        (int) $_ENV['WORK_DAY_START'],
        (int) $_ENV['WORK_DAY_END'],
    ),
    /** @param LoggerInterface */
    $app->getContainer()->get(LoggerInterface::class)
));

$app->post('/sms', new VoiceRecordingTranscriptionHandler(
    /** @oaram Client */
    $app->getContainer()->get(Client::class),
    /** @oaram LoggerInterface */
    $app->getContainer()->get(LoggerInterface::class)
));

$app->run();
