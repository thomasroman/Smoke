<?php

namespace whm\Smoke\Test\Http;

use Phly\Http\Uri;
use whm\Smoke\Http\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testGetReferencedUris()
    {
        $document = new Document(file_get_contents(__DIR__ . '/fixtures/referencedUrls.html'), new Uri('http://www.example.com/test/'));
        $urls = $document->getReferencedUris();

        foreach ($urls as $url) {
            $currentUrls[] = (string) $url;
        }

        $expectedUrls = array(
            'http://foreign-domain-schema-relative.com',
            'http://www.example.com/test/images/relative_path.html?withQuery',
            'http://www.example.com/test/images/relative_path.html',
            'http://foreign-domain-schema-relative.com/file.js',
            'http://www.example.com/',
            'http://fonts.googleapis.com/css?family=Dancing+Script',
            'http://www.example.com/absolute_path.php',
            'http://www.notexample.com/foreign_domain.html', );

        $this->assertEquals($expectedUrls, $currentUrls);
    }
}
