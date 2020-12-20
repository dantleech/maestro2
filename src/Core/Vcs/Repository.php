<?php

namespace Maestro\Core\Vcs;

use Amp\Promise;
use Maestro\Core\Vcs\Exception\CheckoutError;

interface Repository
{
    /**
     * @throws CheckoutError
     * @return Promise<null>
     */
    public function checkout(string $url): Promise;

    /**
     * @return Promise<Tags>
     */
    public function listTags(): Promise;

    /**
     * @return Promise<null>
     */
    public function tag(string $tag): Promise;

    /**
     * @return Promise<string>
     */
    public function headId(): Promise;

    /**
     * @return Promise<list<string>>
     */
    public function commitsBetween(string $start, string $end): Promise;

    public function isCheckedOut(): bool;

    /**
     * @return Promise<string>
     */
    public function message(string $commitId): Promise;

    /**
     * @return Promise<null>
     */
    public function update(): Promise;
}
