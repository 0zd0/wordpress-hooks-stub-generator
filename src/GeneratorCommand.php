<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class GeneratorCommand extends Command
{
    private Filesystem $filesystem;
    private string $outputDir;

    public function configure(): void
    {
        $this->setName('generate-hooks')
            ->setDescription('Generates WordPress hooks documentation from PHP files')
            ->addOption(
                CommandOptionEnum::INPUT->value,
                'i',
                InputOption::VALUE_REQUIRED,
                'Source directory containing PHP files to scan'
            )
            ->addOption(
                CommandOptionEnum::OUTPUT->value,
                'o',
                InputOption::VALUE_REQUIRED,
                'Output directory for generated hook documentation'
            )
            ->addOption(
                CommandOptionEnum::IGNORE_HOOKS->value,
                null,
                InputOption::VALUE_OPTIONAL,
                'Comma-separated list of hooks to ignore',
                ''
            )
            ->addOption(
                CommandOptionEnum::IGNORE_FILES->value,
                null,
                InputOption::VALUE_OPTIONAL,
                'Comma-separated list of file patterns to ignore',
                ''
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->filesystem = new Filesystem();

        $outputDir = $input->getOption('output');
        $this->outputDir = $this->resolvePath($outputDir);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputDir = $this->resolvePath($input->getOption('input'));
        if (!$this->filesystem->exists($inputDir) || !is_dir($inputDir)) {
            throw new InvalidArgumentException("Input directory '$inputDir' does not exist or is not a directory.");
        }
        $finder = new Finder();
        $finder->in($inputDir)->init_default();

        $ignoreHooks = array_filter(
            explode(',', $input->getOption('ignore-hooks')),
            'strlen'
        );

        $ignoreFiles = array_filter(
            explode(',', $input->getOption('ignore-files')),
            'strlen'
        );

        $generator = new Generator(
            $ignoreFiles,
            $ignoreHooks,
        );
        $generator->setFinder($finder);
        $result = $generator->generate();
        $result->saveToFiles($this->outputDir);

        return Command::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        if (!$this->filesystem->isAbsolutePath($path)) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }
        return $path;
    }
}
