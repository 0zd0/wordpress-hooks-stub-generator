<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator\Tests;

use Onepix\WordPressHooksStubGenerator\ConfigTrait;
use Onepix\WordPressHooksStubGenerator\Finder;
use Onepix\WordPressHooksStubGenerator\Generator;
use Onepix\WordPressHooksStubGenerator\Tests\Util\ArraysAreEqualConstraint;
use Onepix\WordPressHooksStubGenerator\Tests\Util\StubManager;
use Opis\JsonSchema\Validator;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Filesystem\Filesystem;

class TestCase extends \PHPUnit\Framework\TestCase
{
    use ConfigTrait;

    protected Generator $generator;
    protected Finder $finder;
    protected Validator $validator;
    protected vfsStreamDirectory $root;
    protected string $outputDir;
    protected Filesystem $filesystem;
    protected StubManager $stubManager;

    protected function setUp(): void
    {
        $this->stubManager = new StubManager();

        $this->finder = (new Finder())
            ->files()
            ->in($this->stubManager->getPhpStubFolder());

        $this->generator = new Generator();
        $this->generator->setFinder($this->finder);

        $this->validator = new Validator();
        $this->validator->resolver()->registerFile(
            $this::getSchemaUrl(),
            dirname(__DIR__) . '/schema.json'
        );

        $this->root = vfsStream::setup();
        $this->outputDir = vfsStream::url('root');

        $this->filesystem = new Filesystem();
    }

    public static function assertArraysAreEqual(array $expected, array $actual, string $message = ''): void
    {
        $constraint = new ArraysAreEqualConstraint($expected);
        static::assertThat($actual, $constraint, $message);
    }
}
