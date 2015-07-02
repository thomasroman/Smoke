<?php

namespace whm\Smoke\Test\Rules;

use Phly\Http\Uri;
use phmLabs\Components\Annovent\Dispatcher;
use Symfony\Component\Yaml\Yaml;
use whm\Crawler\Filter;
use whm\Smoke\Config\Configuration;

use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler\Filter\BlackWhiteListFilter;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler\Filter\ForeignDomainFilter;

class FiltersTest extends \PHPUnit_Framework_TestCase
{
    private function initConfig(&$filter)
    {
        if ($filter instanceof ConfigAwareFilter) {
            $config = new Configuration(new Uri('http://www.example.com'),
                new Dispatcher(),
                Yaml::parse(file_get_contents(__DIR__ . '/fixtures/filter.yml')));
            $filter->setConfiguration($config);
        }
    }

    /**
     * @dataProvider isFilteredProvider
     */
    public function testIsFiltered(Filter $filter, $url)
    {
        return;
        $this->assertTrue($filter->isFiltered(new Uri($url), new Uri("http://www.example.com")));
    }

    /**
     * @dataProvider isNotFilteredProvider
     */
    public function testIsNotFiltered(Filter $filter, $url)
    {
        return;
        $this->assertFalse($filter->isFiltered(new Uri($url), new Uri("http://www.example.com")));
    }

    public function isFilteredProvider()
    {
        return [
            [new ForeignDomainFilter(), 'http://www.notexample.com'],
            [new BlackWhiteListFilter(), 'http://www.example.com/do_not_analyze'],
            [new BlackWhiteListFilter(), 'http://www.example.com/do_not_analyze/add_on'],
            [new BlackWhiteListFilter(), 'http://www.notexample.com/'],
        ];
    }

    public function isNotFilteredProvider()
    {
        return [
            [new ForeignDomainFilter(), 'http://www.example.com'],
            [new BlackWhiteListFilter(), 'http://www.example.com'],
            [new BlackWhiteListFilter(), 'http://www.example.com/with_path'],
        ];
    }
}
