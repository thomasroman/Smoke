<?php

namespace whm\Smoke\Cli\Command;

use phmLabs\Components\Annovent\Dispatcher;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;

class WarmUpCommand extends SmokeCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('url', InputArgument::REQUIRED, 'the url to start with'),
                new InputOption('duration', 'd', InputOption::VALUE_OPTIONAL, 'duration in seconds', 60),
                new InputOption('parallel_requests', 'p', InputOption::VALUE_OPTIONAL, 'number of parallel requests.', 10),
            ])
            ->setDescription('analyses a website')
            ->setHelp('The <info>warmup</info> command warms a website up.')
            ->setName('warmup');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output, $input->getArgument('url'));

        $this->initConfiguration(
            new Uri($input->getArgument('url')),
            $this->eventDispatcher);

        $timeStrategy = $this->config->getExtension('_SmokeStop')->getStrategy('_TimeStop');
        $timeStrategy->init($input->getOption('duration'));

        return $this->scan();
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
    private function initConfiguration(Uri $uri, Dispatcher $dispatcher)
    {
        $configArray = $this->getConfigArray(__DIR__ . '/../../settings/warmup.yml');

        $config = new Configuration($uri, $dispatcher, $configArray);

        $crawler = $config->getExtension('_ResponseRetriever')->getRetriever();
        $crawler->setStartPage($uri);

        $this->config = $config;
    }
}
