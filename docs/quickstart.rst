Quickstart
==========

You need a project
------------------

First of all you need a project. You can include Maestro in an existing
project, but as Maestro may manage that project too, it's perhaps better to
create a new project:

.. code-block:: bash

    $ composer create-project my-project-hub
    $ cd my-project-hub

Setup the project
-----------------

Require Maestro:

.. code-block:: bash

    $ composer require dantleech/maestro

You will also need to setup some autoload paths for your pipelines:

.. code-block:: javascript

    {
        // ...
        "autoload": {
            "psr-4": {
                "MyProjectHub\\": "src/",
            }
        }
    }

Create a pipeline
-----------------

Create the following pipeline file: ``src/Pipeline/EmptyPipeline.php``:

.. code-block:: php

    <?php

    namespace MyProjectHub\Pipeline;

    use Maestro\Core\Inventory\MainNode;
    use Maestro\Core\Pipeline\Pipeline;
    use Maestro\Core\Task\NullTask;
    use Maestro\Core\Task\SequentialTask;
    use Maestro\Core\Task\SetReportingGroupTask;
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

Then run ``maestro``:

.. code-block:: bash

    $ ./vendor/bin/maestro run src/Pipeline/EmptyPipeline
