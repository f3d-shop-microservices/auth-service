<?php

namespace App\Command;

use Shop\Common\Contract\ServiceDiscoveryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:service-discovery:register',
    description: 'Register service to service discovery',
)]
class ServiceDiscoveryRegisterCommand extends Command
{
    public function __construct(private ServiceDiscoveryInterface $serviceDiscovery)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->serviceDiscovery->register();
        $io->success('Registered in service discovery');

        return Command::SUCCESS;
    }
}
