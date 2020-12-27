<?php

namespace Maestro\Core\Task;

class JsonApiTask implements Task
{
    /**
     * @param array<string,string|list<string>> $headers
     */
    public function __construct(
        private string $url,
        private string $method = 'GET',
        private array $headers = [],
        private ?array $body = null
    ) {
    }

    public function body(): ?array
    {
        return $this->body;
    }

    /**
     * @return array<string,string|list<string>>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function url(): string
    {
        return $this->url;
    }
}
