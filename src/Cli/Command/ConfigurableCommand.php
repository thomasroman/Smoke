<?php

namespace whm\Smoke\Cli\Command;

use Symfony\Component\Console\Input\InputOption;

abstract class ConfigurableCommand extends SmokeCommand
{
    /**
     * {@inheritdoc}
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
