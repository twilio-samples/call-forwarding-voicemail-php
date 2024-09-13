<?php
declare(strict_types=1);

use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();
$dotenv
    ->required(['MY_PHONE_NUMBER', 'TWILIO_ACCOUNT_SID', 'TWILIO_AUTH_TOKEN'])
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

/**
 * Check if business hours are in effect
 */
function duringBusinessHours(
    DateTimeImmutable $now,
    string            $weekStart,
    string            $weekEnd,
    int               $dayStart,
    int $dayEnd
): bool {
    $workWeekStart = (new DateTime($weekStart . ' this week'))->setTime($dayStart, 0);
    $workWeekEnd = (new DateTime($weekEnd . ' this week'))->setTime($dayEnd, 0);
    $workDayStart = (new DateTime())->setTime($dayStart, 0);
    $workDayEnd = (new DateTime())->setTime($dayEnd, 0);

    return (
        $now >= $workWeekStart
        || $now <= $workWeekEnd
        || $now >= $workDayStart
        || $now <= $workDayEnd
    );
}

$app->post('/', function (
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
): ResponseInterface {
    $isDuringBusinessHours = duringBusinessHours(
        new DateTimeImmutable('now'),
        $_ENV['WORK_WEEK_START'] ?? 'Monday',
        $_ENV['WORK_WEEK_END'] ?? 'Friday',
        $_ENV['WORK_DAY_START'] ?? 8,
        $_ENV['WORK_DAY_END'] ?? 18,
    );

    /** @var LoggerInterface $logger */
    $logger = $this->get(LoggerInterface::class);
    $logger->info(
        $isDuringBusinessHours
            ? "During business hours. Attempting to redirect."
            : "Outside of business hours. Sending to voicemail."
    );

    $voiceResponse = new VoiceResponse();

    if ($isDuringBusinessHours) {
        $voiceResponse->dial($_ENV['MY_PHONE_NUMBER']);
        $voiceResponse->say('Sorry, I was unable to redirect you. Goodbye.');
    }

    if (! $isDuringBusinessHours) {
        $voiceResponse->record([
            'finishOnKey' => '#',
            'maxLength' => 300,
            'timeout' => 10,
            'transcribe' => 'true',
            'transcribeCallback' => '/sms'
        ]);
    }

    $response->withHeader('Content-Type', 'application/xml');
    $response
        ->getBody()
        ->write($voiceResponse->asXML());

    return $response;
});

/**
 * This route receives a POST request with a voicemail recording transcription which is sent to the specified number.
 */
$app->post('/sms', function (
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
): ResponseInterface {
    /** @var LoggerInterface $logger */
    $logger = $this->get(LoggerInterface::class);
    $logger->info('Received SMS transcription request from Twilio', $request->getParsedBody());

    /** @var Client $client */
    $client = $this->get(Client::class);

    $sms = $client
        ->messages
        ->create(
            $_ENV['MY_PHONE_NUMBER'],
            [
                'body' => $request->getParsedBody()['transcription_text'],
                'from' => (string)$_ENV['TWILIO_PHONE_NUMBER'],
            ]
        );

    $message = match ($sms->status) {
        'failed', 'canceled', 'undelivered' => 'Something went wrong sending the SMS with the voice recording transcript.',
        default => 'The SMS with the voice recording transcript was sent successfully.'
    };
    $logger->info($message, [$sms]);
    $response->getBody()->write($message);

    return $response;
});

$app->run();