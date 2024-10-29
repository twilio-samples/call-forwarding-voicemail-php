<?php

declare(strict_types=1);

namespace CallForwardingVoicemail;

use DateTime;
use Exception;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Twilio\TwiML\VoiceResponse;

final readonly class CallForwardingHandler
{
    public const int WORK_DAY_START                        = 8;
    public const int WORK_DAY_END                          = 18;
    public const string WORK_WEEK_START                    = "monday";
    public const string WORK_WEEK_END                      = "END";
    public const int VOICE_RESPONSE_MAX_LENGTH             = 300;
    public const int VOICE_RESPONSE_TIMEOUT                = 10;
    public const string VOICE_RESPONSE_FINISH_KEY          = "#";
    public const string VOICE_RESPONSE_TRANSCRIBE          = "true";
    public const string VOICE_RESPONSE_TRANSCRIBE_CALLBACK = "/sms";

    public function __construct(
        private ClockInterface $clock,
        private CallForwardingHandlerOptions $options,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(
        ServerRequestInterface $unusedRequest,
        ResponseInterface $response,
    ): ResponseInterface {
        $isDuringBusinessHours = $this->duringBusinessHours(
            $this->options->getWorkWeekStart(),
            $this->options->getWorkWeekEnd(),
            $this->options->getWorkDayStart(),
            $this->options->getWorkDayEnd(),
        );

        $this->logger?->info(
            $isDuringBusinessHours
                ? "During business hours. Attempting to redirect."
                : "Outside of business hours. Sending to voicemail."
        );

        $voiceResponse = new VoiceResponse();

        if ($isDuringBusinessHours) {
            $voiceResponse->dial($_ENV['MY_PHONE_NUMBER']);
            $voiceResponse->say('Please wait while we forward your call.');
        } else {
            $voiceResponse->record([
                'finishOnKey'        => self::VOICE_RESPONSE_FINISH_KEY,
                'maxLength'          => self::VOICE_RESPONSE_MAX_LENGTH,
                'timeout'            => self::VOICE_RESPONSE_TIMEOUT,
                'transcribe'         => self::VOICE_RESPONSE_TRANSCRIBE,
                'transcribeCallback' => self::VOICE_RESPONSE_TRANSCRIBE_CALLBACK,
            ]);
        }

        $newResponse = $response->withHeader('Content-Type', 'application/xml');
        $newResponse
            ->getBody()
            ->write($voiceResponse->asXML());

        return $newResponse;
    }

    /**
     * Check if business hours are in effect
     *
     * @throws Exception
     */
    private function duringBusinessHours(
        string $weekStart,
        string $weekEnd,
        int $dayStart,
        int $dayEnd
    ): bool {
        $now           = $this->clock->now();
        $workWeekStart = (new DateTime($weekStart . ' this week'))->setTime($dayStart, 0);
        $workWeekEnd   = (new DateTime($weekEnd . ' this week'))->setTime($dayEnd, 0);
        $workDayStart  = (DateTime::createFromImmutable($now))->setTime($dayStart, 0);
        $workDayEnd    = (DateTime::createFromImmutable($now))->setTime($dayEnd, 0);

        return ($now >= $workWeekStart && $now <= $workWeekEnd)
                && ($now >= $workDayStart && $now <= $workDayEnd);
    }
}
