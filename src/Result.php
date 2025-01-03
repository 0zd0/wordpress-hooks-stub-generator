<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator;

use Exception;
use Symfony\Component\Filesystem\Filesystem;

class Result
{
    use ConfigTrait;

    public const ACTIONS_FILE = 'actions.json';
    public const FILTERS_FILE = 'filters.json';

    private Filesystem $filesystem;

    /**
     * @param array<string, Exception> $unparsedFiles
     */
    public function __construct(
        private readonly array $filters,
        private readonly array $actions,
        private readonly array $unparsedFiles = []
    ) {
        $this->filesystem = new Filesystem();
    }

    /**
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     */
    public function getHooks(): array
    {
        return [...$this->getActions(), ...$this->getFilters()];
    }

    /**
     * @return Exception[]
     */
    public function getUnparsedFiles(): array
    {
        return $this->unparsedFiles;
    }

    public function saveToFiles(
        string $outputDir
    ): void {
        $defaultObject = [
            '$schema' => self::getSchemaUrl(),
        ];

        $this->filesystem->dumpFile(
            $outputDir . '/' . self::ACTIONS_FILE,
            json_encode([...$defaultObject, 'hooks' => $this->getActions()], JSON_UNESCAPED_SLASHES)
        );

        $this->filesystem->dumpFile(
            $outputDir . '/' . self::FILTERS_FILE,
            json_encode([...$defaultObject, 'hooks' => $this->getFilters()], JSON_UNESCAPED_SLASHES)
        );
    }
}
