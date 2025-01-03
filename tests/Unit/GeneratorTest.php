<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator\Tests\Unit;

use Onepix\WordPressHooksStubGenerator\Tests\Util\StubFileEnum;
use Onepix\WordPressHooksStubGenerator\Tests\TestCase;

class GeneratorTest extends TestCase
{
    public function testDefaultHooks()
    {
        $this->finder
            ->name(StubFileEnum::DEFAULT_HOOKS->value . '.php');

        $result = $this->generator->generate();

        $stub = $this->stubManager->getStubJsonResult(StubFileEnum::DEFAULT_HOOKS, true);

        self::assertArraysAreEqual(
            $result->getHooks(),
            $stub['hooks'],
            'Actions should be the same'
        );
    }

    public function testWithoutDoc()
    {
        $this->finder
            ->name(StubFileEnum::WITHOUT_DOC->value . '.php');

        $result = $this->generator->generate();

        $stub = $this->stubManager->getStubJsonResult(StubFileEnum::WITHOUT_DOC, true);

        self::assertArraysAreEqual(
            $result->getHooks(),
            $stub['hooks'],
            'Actions should be the same'
        );
    }

    public function testAllTypeFunctions()
    {
        $this->finder
            ->name(StubFileEnum::ALL_FUNCTIONS->value . '.php');

        $result = $this->generator->generate();

        $stub = $this->stubManager->getStubJsonResult(StubFileEnum::ALL_FUNCTIONS, true);

        self::assertArraysAreEqual(
            $result->getHooks(),
            $stub['hooks'],
            'Actions should be the same'
        );
    }

    public function testWithIgnoreHooks()
    {
        $this->finder
            ->name(StubFileEnum::WITH_IGNORE_HOOKS->value . '.php');

        $this->generator->setIgnoreHooks(['bred']);
        $result = $this->generator->generate();

        $stub = $this->stubManager->getStubJsonResult(StubFileEnum::WITH_IGNORE_HOOKS, true);

        self::assertArraysAreEqual(
            $result->getHooks(),
            $stub['hooks'],
            'Actions should be the same'
        );
    }

    public function testWithIgnoreFiles()
    {
        $this->finder
            ->name(StubFileEnum::WITH_IGNORE_FILES->value . '.php');

        $this->generator->setIgnoreFiles(['with_ignore_files']);
        $result = $this->generator->generate();
        self::assertEmpty($result->getHooks(), 'Actions should be empty');
    }

    public function testWithVariable()
    {
        $this->finder
            ->name(StubFileEnum::WITH_VARIABLE->value . '.php');

        $result = $this->generator->generate();

        $stub = $this->stubManager->getStubJsonResult(StubFileEnum::WITH_VARIABLE, true);

        self::assertArraysAreEqual(
            $result->getHooks(),
            $stub['hooks'],
            'Actions should be the same'
        );
    }

    public function testValidBySchema(): void
    {
        $result = $this->validator->validate($this->stubManager->getStubJson(StubFileEnum::VALIDATE_BY_SCHEMA), $this::getSchemaUrl());
        $this->assertTrue($result->isValid(), 'Data should be valid according to schema');
    }
}
