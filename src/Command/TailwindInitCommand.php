<?php

namespace Symfonycasts\TailwindBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfonycasts\TailwindBundle\TailwindBinary;
use Symfonycasts\TailwindBundle\TailwindBuilder;

#[AsCommand(
    name: 'tailwind:init',
    description: 'Initializes Tailwind CSS for your project',
)]
class TailwindInitCommand extends Command
{
    public function __construct(
        private TailwindBuilder $tailwindBuilder,
        private string $projectDir,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (!$this->createTailwindConfig($io)) {
            return self::FAILURE;
        }

        $this->addTailwindDirectives($io);

        $io->success('Tailwind CSS is ready to use!');

        return self::SUCCESS;
    }

    private function createTailwindConfig(SymfonyStyle $io): bool
    {
        if (file_exists($this->projectDir.'/tailwind.config.js')) {
            $io->note('tailwind.config.js found: Tailwind CSS is already initialized.');

            return true;
        }

        $this->tailwindBuilder->setOutput($io);

        $process = $this->tailwindBuilder->runInit();
        $process->wait(function ($type, $buffer) use ($io) {
            $io->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $io->error('Tailwind CSS init failed: see output above.');

            return false;
        }

        $io->note('Updating tailwind.config.js for Symfony paths...');

        $tailwindConfig = <<<EOF
        /** @type {import('tailwindcss').Config} */
        module.exports = {
          content: [
            "./assets/**/*.js",
            "./templates/**/*.html.twig",
          ],
          theme: {
            extend: {},
          },
          plugins: [],
        }

        EOF;

        file_put_contents($this->projectDir.'/tailwind.config.js', $tailwindConfig);

        return true;
    }

    private function addTailwindDirectives(SymfonyStyle $io): void
    {
        $inputFile = $this->tailwindBuilder->getInputCssPath();
        $contents = is_file($inputFile) ? file_get_contents($inputFile) : '';
        if (str_contains($contents, '@tailwind base')) {
            $io->note(sprintf('Tailwind directives already exist in "%s"', $inputFile));

            return;
        }

        $io->note(sprintf('Adding Tailwind directives to "%s"', $inputFile));
        $tailwindDirectives = <<<EOF
        @tailwind base;
        @tailwind components;
        @tailwind utilities;
        EOF;

        file_put_contents($inputFile, $tailwindDirectives."\n\n".$contents);
    }
}