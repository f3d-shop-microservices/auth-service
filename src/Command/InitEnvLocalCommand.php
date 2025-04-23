<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-env-local',
    description: 'Initiating env.local file from .dist',
)]
class InitEnvLocalCommand extends Command
{
    private string $envFile = '.env.local';
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->initLocalEnvFile($io);

        return Command::SUCCESS;
    }

    private function initLocalEnvFile(SymfonyStyle $io): void
    {
        $distFile = '.env.dist';
        $targetFile = $this->envFile;

        if (!file_exists($distFile)) {
            throw new \RuntimeException("$distFile not found.");
        }

        if (!file_exists($targetFile)) {
            copy($distFile, $targetFile);
            $io->success("Created $targetFile from $distFile");
        }
    }
}
