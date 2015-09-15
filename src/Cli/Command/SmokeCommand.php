<?php

namespace whm\Smoke\Cli\Command;

use Ivory\HttpAdapter\HttpAdapterFactory;
use phmLabs\Components\Annovent\Dispatcher;
use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use whm\Smoke\Http\MessageFactory;
use whm\Smoke\Scanner\Scanner;

class SmokeCommand extends Command
{
    protected $output;
    protected $eventDispatcher;
    protected $config;

    protected function init(InputInterface $input, OutputInterface $output, $url = null)
    {
        if ($input->hasOption('bootstrap') && !is_null($input->getOption('bootstrap'))) {
            include $input->getOption('bootstrap');
        }

        $this->output = $output;
        $this->eventDispatcher = new Dispatcher();

        Init::registerGlobalParameter('_eventDispatcher', $this->eventDispatcher);
        Init::registerGlobalParameter('_output', $output);

        $this->writeSmokeCredentials($url);
    }

    /**
     * This function creates the credentials header for the command line.
     *
     * @param null $url
     */
    protected function writeSmokeCredentials($url = null)
    {
        $this->output->writeln("\n Smoke " . SMOKE_VERSION . " by Nils Langner\n");

        if ($url) {
            $this->output->writeln(' <info>Scanning ' . $url . "</info>\n");
        }
    }

    /**
     * This function return a http client.
     *
     * @throws \Ivory\HttpAdapter\HttpAdapterException
     *
     * @return \Ivory\HttpAdapter\HttpAdapterInterface
     */
    protected function getHttpClient()
    {
        $httpAdapter = HttpAdapterFactory::guess();
        $httpAdapter->getConfiguration()->setMessageFactory(new MessageFactory());

        return $httpAdapter;
    }

    protected function scan()
    {
        $scanner = new Scanner($this->config->getRules(),
            $this->getHttpClient(),
            $this->eventDispatcher,
            $this->config->getExtension('_ResponseRetriever')->getRetriever());

        $scanner->scan();

        return $scanner->getStatus();
    }

    /**
     * Returns an array representing the configuration.
     *
     * @param $configFile
     *
     * @return array
     */
    protected function getConfigArray($configFile, $mandatory = false)
    {
        $configArray = array();

        if ($configFile) {
            if (file_exists($configFile)) {
                $configArray = Yaml::parse(file_get_contents($configFile));
            } else {
                throw new \RuntimeException("Config file was not found ('" . $configFile . "').");
            }
        } else {
            if ($mandatory) {
                throw new \RuntimeException('Config file was not defined.');
            }
        }

        return $configArray;
    }
}
