<?php

declare(strict_types=1);

namespace CallForwardingVoicemailTest;

use CallForwardingVoicemail\VoiceRecordingTranscriptionHandler;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Api\V2010\Account\MessageList;
use Twilio\Rest\Client;

use function assert;

class VoiceRecordingTranscriptionHandlerTest extends TestCase
{
    public function setUp(): void
    {
        $_ENV["MY_PHONE_NUMBER"]     = "+61000000000";
        $_ENV["TWILIO_PHONE_NUMBER"] = "+61000000001";
    }

    #[TestWith(["sent", VoiceRecordingTranscriptionHandler::MESSAGE_DELIVERY_SUCCESS])]
    #[TestWith(["failed", VoiceRecordingTranscriptionHandler::MESSAGE_DELIVERY_FAIL])]
    #[TestWith(["canceled", VoiceRecordingTranscriptionHandler::MESSAGE_DELIVERY_FAIL])]
    #[TestWith(["undelivered", VoiceRecordingTranscriptionHandler::MESSAGE_DELIVERY_FAIL])]
    public function testCanSendTranscription(string $status, string $messageBody): void
    {
        /** @var MessageInstance&MockObject $message */
        $message = $this->createMock(MessageInstance::class);
        $message
            ->expects($this->once())
            ->method("__get")
            ->with("status")
            ->willReturn($status);

        /** @var MessageList&MockObject $messages */
        $messages = $this->createMock(MessageList::class);
        $messages
            ->expects(self::once())
            ->method('create')
            ->with(
                "+61000000000",
                [
                    "body" => "Transcription test",
                    "from" => "+61000000001",
                ]
            )
            ->willReturn($message);

        /** @var MockObject $client */
        $client = $this->createMock(Client::class);
        $client
            ->expects($this->once())
            ->method("__get")
            ->with("messages")
            ->willReturn($messages);

        /** @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([
                "transcription_text" => "Transcription test",
            ]);

        assert($client instanceof Client);
        $handler  = new VoiceRecordingTranscriptionHandler($client);
        $response = $handler($request, new Response());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($messageBody, (string) $response->getBody());
    }
}
