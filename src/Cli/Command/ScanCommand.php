<?php

namespace whm\Smoke\Cli\Command;

use Ivory\HttpAdapter\HttpAdapterFactory;
use phmLabs\Components\Annovent\Dispatcher;
use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Http\MessageFactory;
use whm\Smoke\Scanner\Scanner;

class ScanCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('url', InputArgument::REQUIRED, 'the url to start with'),
                new InputOption('parallel_requests', 'p', InputOption::VALUE_OPTIONAL, 'number of parallel requests.', 10),
                new InputOption('num_urls', 'u', InputOption::VALUE_OPTIONAL, 'number of urls to be checked', 20),
            ])
            ->setDescription('analyses a website')
            ->setHelp('The <info>analyse</info> command runs a cache test.')
            ->setName('analyse');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventDispatcher = new Dispatcher();
        Init::registerGlobalParameter('_eventDispatcher', $eventDispatcher);
        Init::registerGlobalParameter('_output', $output);

        $config = $this->initConfiguration(
            $input->getOption('num_urls'),
            $input->getOption('parallel_requests'),
            new Uri($input->getArgument('url')),
            $eventDispatcher);

        $eventDispatcher->simpleNotify('ScannerCommand.Config.Register', array('config' => $config));
        $eventDispatcher->simpleNotify('ScannerCommand.Output.Register', array('output' => $output));

        $output->writeln("\n Smoke " . SMOKE_VERSION . " by Nils Langner\n");
        $output->writeln(' <info>Scanning ' . $config->getStartUri() . "</info>\n");

        $httpAdapter = HttpAdapterFactory::guess();
        $httpAdapter->getConfiguration()->setMessageFactory(new MessageFactory());

        $scanner = new Scanner($config, $httpAdapter, $eventDispatcher, $config->getExtension('_ResponseRetriever')->getRetriever());

        $scanner->scan();

        return $scanner->getStatus();
    }

    /**
     * Initializes the configuration.
     *
     * @return Configuration
     */
    private function initConfiguration($num_urls, $parallel_requests, Uri $uri, Dispatcher $dispatcher)
    {
        $configArray = Yaml::parse(file_get_contents(__DIR__ . '/../../settings/analyze.yml'));

        $config = new Configuration($uri, $dispatcher, $configArray);

        $crawler = $config->getExtension('_ResponseRetriever')->getRetriever();
        $crawler->setStartPage($uri);

        if ($num_urls) {
            $config->getExtension('_SmokeStop')->getStrategy('_CountStop')->init($num_urls);
            $config->getExtension('_ProgressBar')->setMax($num_urls);
        }

        if ($parallel_requests) {
            $config->setParallelRequestCount($parallel_requests);
        }

        return $config;
    }
}
