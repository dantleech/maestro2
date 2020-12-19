<?php

namespace Maestro2\Core\Vcs;

use Amp\Promise;
use Amp\Success;

class TestRepository implements Repository
{
    private const COMMIT_SH = 'test-commit-sh';

    /**
     * @var list<Tag>
     */
    private $tags = [];

    public function checkout(string $url): Promise
    {
        return new Success();
    }

    public function listTags(): Promise
    {
        return new Success(new Tags($this->tags));
    }

    public function tag(string $tag): Promise
    {
        $this->tags[] = new Tag($tag, self::COMMIT_SH);

        return new Success();
    }

    public function headId(): Promise
    {
        return new Success(self::COMMIT_SH);
    }

    public function commitsBetween(string $start, string $end): Promise
    {
        return new Success();
    }

    public function isCheckedOut(): bool
    {
        return new Success();
    }

    public function message(string $commitId): Promise
    {
        return new Success();
    }

    public function update(): Promise
    {
        return new Success();
    }
}
