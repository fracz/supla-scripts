<?php

namespace suplascripts\app\commands;

use Defuse\Crypto\Key;
use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEncryptionKeyCommand extends Command {

    const KEY_PATH = Application::VAR_PATH . '/system/key';

    protected function configure() {
        $this
            ->setName('encryptionKey:generate')
            ->setDescription('Generates an encryption key for this instance if it does not exists yet.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (file_exists(self::KEY_PATH)) {
            $output->writeln('<comment>The encryption key already exists. No changes made.</comment>');
        } else {
            $this->generateEncryptionKey();
            $output->writeln('<info>The encryption key has been generated.</info>');
        }
    }

    private function generateEncryptionKey() {
        $key = Key::createNewRandomKey();
        self::storeEncryptionKey($key->saveToAsciiSafeString());
    }

    public static function storeEncryptionKey(string $key) {
        if (!file_put_contents(self::KEY_PATH, $key)) {
            throw new \RuntimeException('The path ' . self::KEY_PATH . ' must be writable.');
        }
        if (!chmod(self::KEY_PATH, 0400)) {
            throw new \RuntimeException('The path ' . self::KEY_PATH . ' must be writable.');
        }
    }
}
