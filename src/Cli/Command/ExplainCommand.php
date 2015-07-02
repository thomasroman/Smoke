<?php

namespace whm\Smoke\Cli\Command;

use phmLabs\Components\Annovent\Dispatcher;
use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;

class ExplainCommand extends Command
{
    /**
     * Defines what arguments and options are available for the user. Can be listed using
     * Smoke.phar analyse --help.
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputOption('config_file', 'c', InputOption::VALUE_OPTIONAL, 'config file'),
                new InputOption('bootstrap', 'b', InputOption::VALUE_OPTIONAL, 'bootstrap file'),
            ])
            ->setDescription('explain the rules that are configured')
            ->setHelp('The <info>explain</info> command explains all the rules that will be executed.')
            ->setName('explain');
    }

    /**
     * Runs the analysis of the given website with all given parameters.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventDispatcher = new Dispatcher();
        Init::registerGlobalParameter('_eventDispatcher', $eventDispatcher);
        Init::registerGlobalParameter('_output', $output);

        $config = $this->initConfiguration($input->getOption('config_file'));

        $output->writeln("\n Smoke " . SMOKE_VERSION . " by Nils Langner\n");
        $output->writeln(" <info>Explaining</info>\n");

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
     * @param $loadForeign
     * @param Uri $uri
     *
     * @return Configuration
     */
    private function initConfiguration($configFile)
    {
        if ($configFile) {
            if (file_exists($configFile)) {
                $configArray = Yaml::parse(file_get_contents($configFile));
            } else {
                throw new \RuntimeException("Config file was not found ('" . $configFile . "').");
            }
        } else {
            $configArray = [];
        }

        $config = new Configuration(new Uri(''), new Dispatcher(), $configArray);

        return $config;
    }
}
