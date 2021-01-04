<?php

namespace Maestro\Core\Task;

/**
 * Perform a HTTP request
 *
 * Use this task to send a HTTP request. The response will be made available
 * in the next task.
 *
 * ```php:task
 * new HttpRequestTask(
 *     url: 'https://www.example.com/do/something',
 *     method: 'POST',
 *     body: 'hello'
 * );
 * ```
 *
 * You can access the results in the subsequent task:
 *
 * ```php:task
 * new SequentialTask([
 *     new HttpRequestTask(
 *         url: 'https://www.example.com/do/something'
 *     ),
 *     new Maestro\Core\Task\ClosureTask(
 *         closure: function (Context $context) {
 *             $response = $context->result();
 *             // do something
 *             return $context;
 *         }
 *     )
 * ]);
 */
class HttpRequestTask implements Task
{
    /**
     * @param string $url
     * @param string $method
     * @param array<string,string|list<string>> $headers
     * @param ?string $body
     */
    public function __construct(
        private string $url,
        private string $method = 'GET',
        private array $headers = [],
        private ?string $body = null
    ) {
    }

    public function body(): ?string
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
