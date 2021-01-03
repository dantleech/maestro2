<?php

namespace Maestro\Git\Fact;

use Maestro\Core\Fact\Fact;

class GitSurveyFact implements Fact
{
    public function __construct(private string $headId, private int $commitsAhead, private string $latestTag, private string $lastMessage)
    {
    }

    public function commitsAhead(): int
    {
        return $this->commitsAhead;
    }

    public function headId(): string
    {
        return $this->headId;
    }

    public function latestTag(): string
    {
        return $this->latestTag;
    }

    public function lastMessage(): string
    {
        return $this->lastMessage;
    }
}
