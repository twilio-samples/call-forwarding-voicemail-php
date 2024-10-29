<?php

declare(strict_types=1);

namespace CallForwardingVoicemailTest;

use CallForwardingVoicemail\CallForwardingHandler;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;

class CallForwardingHandlerTest extends TestCase
{
    public function setUp(): void
    {
        $_ENV["WORK_WEEK_START"] = "Monday";
        $_ENV["WORK_WEEK_END"]   = "Friday";
        $_ENV["WORK_DAY_START"]  = 8;
        $_ENV["WORK_DAY_END"]    = 18;
        $_ENV["MY_PHONE_NUMBER"] = "+61000000000";
    }

    /**
     * @throws \Exception|Exception
     */
    #[TestWith(["Monday this week 09:00"])]
    #[TestWith(["Tuesday this week 17:59"])]
    #[TestWith(["Wednesday this week 12:00"])]
    #[TestWith(["Thursday this week 15:00"])]
    #[TestWith(["Friday this week 08:00"])]
    public function testCallForwardingIsActiveDuringBusinessHours(string $mockClockDate): void
    {
        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method("info")
            ->with("During business hours. Attempting to redirect.");

        /** @var ClockInterface&MockObject $clock */
        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->expects($this->once())
            ->method("now")
            ->willReturn(new DateTimeImmutable($mockClockDate));

        $handler = new CallForwardingHandler($clock, $logger);

        $response = $handler(
            $this->createMock(ServerRequestInterface::class),
            new Response()
        );

        $this->assertSame("application/xml", $response->getHeaderLine('Content-Type'));
        $responseBody = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<Response><Dial>+61000000000</Dial><Say>Please wait while we forward your call.</Say></Response>

EOF;

        $this->assertSame($responseBody, (string) $response->getBody());
    }

    /**
     * @throws \Exception|Exception
     */
    #[TestWith(["Monday this week 07:59"])]
    #[TestWith(["Friday this week 21:00"])]
    #[TestWith(["Friday this week 18:01"])]
    #[TestWith(["Saturday this week 09:00"])]
    public function testVoicemailIsActiveOutsideBusinessHours(string $mockClockDate): void
    {
        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method("info")
            ->with("Outside of business hours. Sending to voicemail.");

        /** @var ClockInterface&MockObject $clock */
        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->expects($this->once())
            ->method("now")
            ->willReturn(new DateTimeImmutable($mockClockDate));

        $handler = new CallForwardingHandler($clock, $logger);

        $response = $handler(
            $this->createMock(ServerRequestInterface::class),
            new Response()
        );

        $this->assertSame("application/xml", $response->getHeaderLine('Content-Type'));
        $responseBody = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<Response><Record finishOnKey="#" maxLength="300" timeout="10" transcribe="true" transcribeCallback="/sms"/></Response>

EOF;

        $this->assertSame($responseBody, (string) $response->getBody());
    }
}
