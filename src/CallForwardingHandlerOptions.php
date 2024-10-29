<?php

declare(strict_types=1);

namespace CallForwardingVoicemail;

final readonly class CallForwardingHandlerOptions
{
    public function __construct(
        private string $workWeekStart,
        private string $workWeekEnd,
        private int $workDayStart,
        private int $workDayEnd
    ) {
    }

    public function getWorkWeekStart(): string
    {
        return $this->workWeekStart;
    }

    public function getWorkWeekEnd(): string
    {
        return $this->workWeekEnd;
    }

    public function getWorkDayStart(): int
    {
        return $this->workDayStart;
    }

    public function getWorkDayEnd(): int
    {
        return $this->workDayEnd;
    }
}
