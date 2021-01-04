<?php

namespace Maestro\Development;

use Generator;
use Maestro\Util\ClassNameFromFile;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Lexer\Lexer as PHPStanLexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PhpParser\Lexer;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Throwable;
use Webmozart\PathUtil\Path;

class TaskFinder
{
    public function __construct(private string $projectRoot)
    {
    }

    /**
     * @return Generator<TaskMetadata>
     */
    public function find(): Generator
    {
        $finder = new Finder();
        $finder->in($this->projectRoot);
        $finder->name('*Task.php');

        foreach ($finder as $file) {
            assert($file instanceof SplFileInfo);
            $name = ClassNameFromFile::classNameFromFile(Path::normalize($file->getPathname()));

            if (null === $name) {
                continue;
            }

            $reflection = new ReflectionClass($name);

            $comment = $reflection->getDocComment();

            if (!$comment) {
                continue;
            }
            
            $lines = array_filter(array_map(function (string $line) {
                $line = preg_replace('{^\s*/\\*\\*\s*$}', '', $line);
                $line = preg_replace('{^\s*\\*\s?}', '', $line);
                $line = preg_replace('{^\s*/\s*$}', '', $line);
                return $line;
            }, explode("\n", $comment)));
            $shortDescription = array_shift($lines);
            $text = trim(implode("\n", $lines));

            $parameters = [];
            foreach ($reflection->getMethods() as $method) {
                if ($method->getName() === '__construct') {
                    $parameters = $this->buildParameters($this->parseDoc($method->getDocComment()));
                    break;
                }
            }

            yield new TaskMetadata(
                rtrim($reflection->getShortName(), 'Task'),
                $shortDescription,
                join('\\', [
                    $reflection->getNamespaceName(),
                    $reflection->getShortName()
                ]),
                $text,
                $parameters
            );
        }

    }

    private function parseDoc(string $comment): ?PhpDocNode
    {
        if (empty($comment)) {
            return null;
        }
        $parser = new PhpDocParser(new TypeParser(), new ConstExprParser());
        $lexer = new PHPStanLexer();
        $tokens = new TokenIterator($lexer->tokenize($comment));
        try {
            $node = $parser->parse($tokens);
        } catch (Throwable $error) {
            throw new RuntimeException(sprintf(
                'Could not parse comment "%s"',
                $comment
            ));
        }
        return $node;
    }

    private function buildParameters(?Node $node): array
    {
        if (null === $node) {
            return [];
        }

        if (!$node instanceof PhpDocNode) {
            return [];
        }

        $params = [];
        foreach ($node->getParamTagValues() as $param) {
            assert($param instanceof ParamTagValueNode);
            $params[] = new TaskParameter(
                $param->parameterName,
                (string)$param->type,
                $param->description,
            );
        }

        return $params;
    }
}
