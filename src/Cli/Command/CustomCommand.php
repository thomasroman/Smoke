<?php

namespace whm\Smoke\Cli\Command;

use Ivory\HttpAdapter\HttpAdapterFactory;
use phmLabs\Components\Annovent\Dispatcher;
use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Http\MessageFactory;
use whm\Smoke\Scanner\Scanner;

class CustomCommand extends SmokeCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputOption('config_file', 'c', InputOption::VALUE_OPTIONAL, 'config file'),
                new InputOption('bootstrap', 'b', InputOption::VALUE_OPTIONAL, 'bootstrap file'),
            ])
            ->setDescription('analyses a website given a config file')
            ->setHelp('The <info>custom</info> command runs a custom website analysis.')
            ->setName('custom');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($output);

        $this->writeSmokeCredentials();

        $this->initConfiguration(
            $input->getOption('config_file'),
            $this->eventDispatcher);

        if ($input->getOption('bootstrap')) {
            include $input->getOption('bootstrap');
        }

        return $this->scan();
    }

    /**
     * Initializes the configuration.
     *
     * @return Configuration
     */
    private function initConfiguration($configFile, Dispatcher $dispatcher)
    {
        if ($configFile) {
            if (file_exists($configFile)) {
                $configArray = Yaml::parse(file_get_contents($configFile));
            } else {
                throw new \RuntimeException("Config file was not found ('" . $configFile . "').");
            }
        } else {
            throw new \RuntimeException('Config file was not defined.');
        }

        $this->config = new Configuration(new Uri('http://example.com'), $dispatcher, $configArray);
    }
}
