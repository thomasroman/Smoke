<?php

namespace whm\Smoke\Test\Rules;

use Phly\Http\Uri;
use phmLabs\Components\Annovent\Dispatcher;
use Symfony\Component\Yaml\Yaml;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeFilter\Filter\BlackWhiteListFilter;
use whm\Smoke\Extensions\SmokeFilter\Filter\ConfigAwareFilter;
use whm\Smoke\Extensions\SmokeFilter\Filter\Filter;
use whm\Smoke\Extensions\SmokeFilter\Filter\ForeignDomainFilter;
use whm\Smoke\Extensions\SmokeFilter\Filter\ValidUrlFilter;

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
        $this->initConfig($filter);
        $this->assertTrue($filter->isFiltered(new Uri($url)));
    }

    /**
     * @dataProvider isNotFilteredProvider
     */
    public function testIsNotFiltered(Filter $filter, $url)
    {
        $this->initConfig($filter);
        $this->assertFalse($filter->isFiltered(new Uri($url)));
    }

    public function isFilteredProvider()
    {
        return [
            [new ValidUrlFilter(), 'http://www=123'],
            [new ForeignDomainFilter(), 'http://www.notexample.com'],
            [new BlackWhiteListFilter(), 'http://www.example.com/do_not_analyze'],
            [new BlackWhiteListFilter(), 'http://www.example.com/do_not_analyze/add_on'],
            [new BlackWhiteListFilter(), 'http://www.notexample.com/'],
        ];
    }

    public function isNotFilteredProvider()
    {
        return [
            [new ValidUrlFilter(), 'http://www.example.com'],
            [new ForeignDomainFilter(), 'http://www.example.com'],
            [new BlackWhiteListFilter(), 'http://www.example.com'],
            [new BlackWhiteListFilter(), 'http://www.example.com/with_path'],
        ];
    }
}
