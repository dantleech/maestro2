Maestro2
========

![CI](https://github.com/dantleech/maestro2/workflows/CI/badge.svg?branch=master)

Maestro2 is a Repository Manager System for PHP.

Think of it like Ansible for repositories.

- Perform upgrades on multiple packages.
- Standardize configuration accross repositories.
- Run CI
- Conduct surveys (discover latest tags, CI status etc)

This project is a work-in-progress.

How It Works
------------

Maestro is essentially a concurrent task runner which is conveniently adapted
for working with repositories.

It is your job to create a _pipeline_ class. This class will be instantiated
and passed the _configuration node_, and it must return a _task_. This _task_
can be an aggregate of many tasks.

Configuration
-------------

### `maestro.json`

This is the main configuration file, which can look something like:

```
{
    "core.inventory": [
        "example/inventory.json",
        "example/secrets.json"
    ],
    "core.templatePath": "example/templates",
    "core.workspacePath": "var",
    "core.concurrency": 10
}
```

The inventory files are where we define our repositories and variables:

```
{
    "vars": {
        "jobs": [
            "php-cs-fixer",
            "phpstan",
            "phpunit"
        ],
        "defaultBranch": "master"
    },
    "repositories": [
        {
            "name": "maestro",
            "url": "git@github.com:dantleech/maestro2",
            "vars": {
                "jobs": [
                    "psalm",
                    "phpunit"
                ]
            }
        },
        {
            "name": "worse-reflection",
            "url": "git@github.com:phpactor/worse-reflection"
        }
    ]
}
```

The inventory files will be merged and cast into configuration nodes which can
be used by pipelines.

Pipelines
---------

Create a pipeline and ensure that it is autoloadable, for example:

```php
<?php
// example/EmptyPipeline.php

namespace Maestro\Examples\Pipeline;

use Maestro\Core\Inventory\MainNode;
use Maestro\Core\Pipeline\Pipeline;
use Maestro\Core\Task\NullTask;
use Maestro\Core\Task\SequentialTask;
use Maestro\Core\Task\Task;

class EmptyPipeline implements Pipeline
{
    public function build(MainNode $mainNode): Task
    {
        return new SequentialTask([
            new NullTask(),
        ]);
    }
}
```

You can then run it with:

```
$ ./vendor/bin/maestro run pipeline/EmptyPipeline.php
```

This does very little - in fact it will do nothing as all we did was specify a
_reporting group_ for subsequent tasks, followed by the `NullTask`.

For working examples see the `example` directory in this project.

There are many tasks which you can add (including aggregate tasks to run more
tasks sequentially or in parallel). These can be found as implementations of
`Maestro\Core\Task\Task` (and also found in the same namespace).

Documentation
-------------

You will be able to read the documentation [here](https://dantleech.github.io/maestro2).
