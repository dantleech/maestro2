<?php

namespace Maestro\Core\Task;

use Stringable;

/**
 * Apply a Twig template at a file.
 *
 * Render a twig template and write it to a file.
 *
 * Templates are located in `templates/` in the root
 * of your Maestro project by default, you can customize
 * this location in `maestro.json`:
 *
 * ```json
 * {
 *     "core.templatePath": "example/templates",
 * }
 * ```
 *
 * Render a README file:
 *
 * ```
 * new TemplateTask(
 *     template: 'README.md.twig',
 *     target: 'README.md'
 * )
 * ```
 *
 * This will look for the template in `<maestro dir>/templates/README.md.twig`
 * and subsequently render it to `README.md` at the current workspace path 
 */
final class TemplateTask implements Task, Stringable
{
    /**
     * @param string $template Path to template (relative to the template directory)
     * @param string $target Path to file to write the rendered template to (in the workspace)
     * @param array $vars Variables to pass to the template
     * @param int $mode Mode for a newly created file
     * @param bool $overwrite If any existing file should be overwritten
     */
    public function __construct(
        private string $template,
        private string $target,
        private array $vars = [],
        private int $mode = 0644,
        private bool $overwrite = false
    ) {
    }

    public function target(): string
    {
        return $this->target;
    }

    public function template(): string
    {
        return $this->template;
    }

    public function vars(): array
    {
        return $this->vars;
    }

    public function mode(): int
    {
        return $this->mode;
    }

    public function overwrite(): bool
    {
        return $this->overwrite;
    }


    public function __toString(): string
    {
        return sprintf(
            'Applied "%s" to "%s"',
            $this->template,
            $this->target
        );
    }
}
