<?php

class ForeignDomainFilterTest extends PHPUnit_Framework_TestCase
{
    private function getConfig()
    {
        $dispatcher = new \phmLabs\Components\Annovent\Dispatcher();
        \PhmLabs\Components\Init\Init::registerGlobalParameter('_eventDispatcher', $dispatcher);
        \PhmLabs\Components\Init\Init::registerGlobalParameter('_output', null);

        return new \whm\Smoke\Config\Configuration(new \whm\Html\Uri('http://www.example.com'),
            $dispatcher,
            \Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__ . '/fixtures/filter.yml')));
    }

    public function testFiltered()
    {
        $filter = new \whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler\Filter\ForeignDomainFilter();

        $this->assertFiltered($filter, 'http://www.notexample.com');
    }

    private function assertFiltered($filter, $url)
    {
        $this->assertTrue($filter->isFiltered(new \whm\Html\Uri($url), new \whm\Html\Uri('http://www.example.com')));
    }

    private function assertNotFiltered($filter, $url)
    {
        $this->assertFalse($filter->isFiltered(new \whm\Html\Uri($url), new \whm\Html\Uri('http://www.example.com')), "Url '" . $url . "' was filtered.");
    }

    public function testNotFiltered()
    {
        $filter = new \whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler\Filter\ForeignDomainFilter();

        $this->assertNotFiltered($filter, 'http://www.example.com');
    }
}
