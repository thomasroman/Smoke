<?php

namespace whm\Smoke\Cli\Command;

use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;

class ExplainCommand extends ConfigurableCommand
{
    /**
     * Defines what arguments and options are available for the user. Can be listed using
     * Smoke.phar analyse --help.
     */
    protected function configure()
    {
        $this->configureCommand('explain the rules that are configured',
            'The <info>explain</info> command explains all the rules that will be executed.',
            'explain');
    }

    /**
     * Runs the analysis of the given website with all given parameters.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($output);

        $this->writeSmokeCredentials();

        $config = $this->initConfiguration($input->getOption('config_file'));

        if ($input->getOption('bootstrap')) {
            include $input->getOption('bootstrap');
        }

        $rules = $config->getRules();

        foreach ($rules as $name => $rule) {
            $info = Init::getInitInformationByClass(get_class($rule));
            $output->writeln('  ' . $name . ':');
            $output->writeln('    class: ' . get_class($rule));
            $output->writeln('    description: ' . str_replace("\n", "\n                 ", $info['documentation']));

            if (count($info['parameters']) > 0) {
                $output->writeln('    parameter:');

                foreach ($info['parameters'] as $parameter) {
                    $output->writeln('      ' . $parameter['name'] . ': ' . $parameter['description'] . ' (default: ' . $parameter['default'] . ')');
                }
            }

            $output->writeln('');
        }
    }

    /**
     * Initializes the configuration.
     *
     * @param $configFile
     *
     * @return Configuration
     */
    private function initConfiguration($configFile)
    {
        $configArray = $this->getConfigArray($configFile);
        $config = new Configuration(new Uri(''), $this->eventDispatcher, $configArray);

        return $config;
    }
}
