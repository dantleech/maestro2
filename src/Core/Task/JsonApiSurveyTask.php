<?php

namespace Maestro\Core\Task;

use Closure;
use Stringable;

/**
 * Conduct a survey on a JSON endpoint
 *
 * Use this task to make a request and subsequently publish information
 */
class JsonApiSurveyTask implements Task, Stringable
{
    /**
     * @param Closure(array<array-key,mixed>):array<string,mixed> $extract Closure which is passed the JSON response as an array.
     * @param string $url
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

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return sprintf('Performing survey on JSON resource at %s', $this->url());
    }
}
