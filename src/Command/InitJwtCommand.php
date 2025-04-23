<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:init-jwt',
    description: 'Generate JWT keys and configure .env.local automatically',
)]
class InitJwtCommand extends Command
{
    private string $envFile = '.env.local';
    private string $jwtDir = 'config/jwt';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->checkOpenSSLInstalled()) {
            throw new \RuntimeException('OpenSSL is not installed or not available in system PATH.');
        }

        $io = new SymfonyStyle($input, $output);

        //Getting passfrase
        $passphrase = $this->generateSecurePassphrase();

        //Creating jwt folder for keys
        if (!is_dir($this->jwtDir)) {
            if (!mkdir($concurrentDirectory = $this->jwtDir, 0700, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            $io->success('Created directory: ' . $this->jwtDir);
        }

        $io->section('Generating keys using OpenSSL lib...');
        $privateKeyPath = "$this->jwtDir/private.pem";
        $publicKeyPath = "$this->jwtDir/public.pem";

        //Generating a private key
        $process = new Process([
            'openssl', 'genpkey',
            '-out', $privateKeyPath,
            '-aes256',
            '-algorithm', 'rsa',
            '-pkeyopt', 'rsa_keygen_bits:4096',
            '-pass', "pass:$passphrase"
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            $io->error("Private key generation failed: " . $process->getErrorOutput());
            return Command::FAILURE;
        }

        //Generating a public key
        $process = new Process([
            'openssl', 'pkey',
            '-in', $privateKeyPath,
            '-out', $publicKeyPath,
            '-pubout',
            '-passin', "pass:$passphrase"
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            $io->error("Public key generation failed: " . $process->getErrorOutput());
            return Command::FAILURE;
        }

        $io->success('JWT keys successfully generated.');

        //Updating .env.local file with JWT variables
        $this->updateJwtEnvBlock($passphrase, $io);

        return Command::SUCCESS;
    }

    private function updateJwtEnvBlock(string $passphrase, SymfonyStyle $io): void
    {
        $targetFile = $this->envFile;

        if (!file_exists($targetFile)) {
            throw new \RuntimeException("$targetFile not found.");
        }

        $envContent = file_get_contents($targetFile);

        $pattern = '/###> lexik\/jwt-authentication-bundle ###.*?###< lexik\/jwt-authentication-bundle ###/s';

        if (!preg_match($pattern, $envContent)) {
            throw new \RuntimeException('JWT config block not found in .env.local');
        }

        $replacement = <<<ENV
###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=$passphrase
###< lexik/jwt-authentication-bundle ###
ENV;

        $envContent = preg_replace($pattern, $replacement, $envContent);

        file_put_contents($targetFile, $envContent);

        $io->success('.env.local updated with new JWT variables.');
    }

    private function generateSecurePassphrase(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function checkOpenSSLInstalled(): bool
    {
        $process = new Process(['openssl', 'version']);
        $process->run();

        return $process->isSuccessful();
    }

}
