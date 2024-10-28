<?php

declare(strict_types=1);

namespace CallForwardingVoicemail\CallForwardingVoicemail;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Twilio\Rest\Client;

use function is_array;

/**
 * This route receives a POST request with a voicemail recording transcription which is sent to the specified number.
 */
final readonly class VoiceRecordingTranscriptionHandler
{
    public const string MESSAGE_DELIVERY_FAIL    = "Something went wrong sending the voice recording transcript.";
    public const string MESSAGE_DELIVERY_SUCCESS = "The SMS with the voice recording transcript was sent successfully.";

    public function __construct(private Client $client, private ?LoggerInterface $logger)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        $requestBody = $request->getParsedBody();

        if (is_array($requestBody)) {
            $this->logger?->info(
                'Received SMS transcription request from Twilio',
                $requestBody
            );
        }

        $sms = $this->client
            ->messages
            ->create(
                $_ENV['MY_PHONE_NUMBER'],
                [
                    'body' => $requestBody['transcription_text'] ?? "",
                    'from' => $_ENV['TWILIO_PHONE_NUMBER'] ?? "",
                ]
            );

        $message = match ($sms->status) {
            'failed', 'canceled', 'undelivered' => self::MESSAGE_DELIVERY_FAIL,
            default => self::MESSAGE_DELIVERY_SUCCESS,
        };
        $response->getBody()->write($message);
        $this->logger?->info($message, [$sms]);

        return $response;
    }
}
