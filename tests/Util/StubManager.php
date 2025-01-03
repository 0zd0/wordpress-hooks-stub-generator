<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator\Tests\Util;

use Onepix\WordPressHooksStubGenerator\Tests\Util\StubFileEnum;
use RuntimeException;
use stdClass;

class StubManager
{
    protected string $stubDir;

    public function __construct()
    {
        $this->stubDir = dirname(__DIR__) . '/../stubs';
    }

    public function getStubJson(
        StubFileEnum $fileName,
        ?string $postfix = null,
        ?bool $associative = null,
    ): array|stdClass {
        $filePath = $this->stubDir . '/json/'. $fileName->value . ($postfix ? ".$postfix" : '') . '.json';

        if (!is_file($filePath)) {
            throw new RuntimeException("File {$filePath} does not exist.");
        }

        return json_decode(file_get_contents($filePath), $associative);
    }

    public function getStubJsonResult(
        StubFileEnum $fileName,
        ?bool $associative = null,
    ): array|stdClass {
        return $this->getStubJson($fileName, 'result', $associative);
    }

    public function getPhpStubFolder(): string
    {
        return $this->stubDir . '/php';
    }

    public function getPhpStubPath(string $fileName): string
    {
        $filePath = $this->getPhpStubFolder() . '/'. $fileName . '.php';

        if (!is_file($filePath)) {
            throw new RuntimeException("File {$filePath} does not exist.");
        }

        return $filePath;
    }
}
