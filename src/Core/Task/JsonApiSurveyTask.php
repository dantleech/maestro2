<?php

namespace Maestro2\Core\Task;

use Closure;

class JsonApiSurveyTask implements Task
{
    /**
     * @param Closure(array<array-key,mixed>):array<string,mixed> $extract
     * @param array<string,string|list<string>> $headers
     */
    public function __construct(
        private string $url,
        private Closure $extract,
        private array $headers = [],
    ) {
    }

    /**
     * @return array<string,string|list<string>>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * @return Closure(array<array-key,mixed>):array<string,mixed>
     */
    public function extract(): Closure
    {
        return $this->extract;
    }

    public function url(): string
    {
        return $this->url;
    }
}
