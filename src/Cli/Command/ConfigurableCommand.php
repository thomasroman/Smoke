<?php

namespace whm\Smoke\Cli\Command;

use phmLabs\Components\Annovent\Dispatcher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;

abstract class ConfigurableCommand extends SmokeCommand
{
    /**
     * @inheritdoc
     */
    protected function configureCommand($description, $help, $name)
    {
        $this
            ->setDefinition([
                new InputOption('config_file', 'c', InputOption::VALUE_REQUIRED, 'config file'),
                new InputOption('bootstrap', 'b', InputOption::VALUE_OPTIONAL, 'bootstrap file'),
            ])
            ->setDescription($description)
            ->setHelp($help)
            ->setName($name);
    }
}
