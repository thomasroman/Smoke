<?php

namespace whm\Smoke\Http;

use Phly\Http\Uri;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Document.
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class Document
{
    private $content;
    private $uri;

    public function __construct($htmlContent, Uri $uri)
    {
        $this->content = $htmlContent;
        $this->uri = $uri;
    }

    /**
     * @return Uri[]
     */
    public function getReferencedUris()
    {
        $crawler = new Crawler($this->content);

        $urls = [];

        $this->findUrls($crawler, $urls, '//a', 'href');
        $this->findUrls($crawler, $urls, '//img', 'src');
        $this->findUrls($crawler, $urls, '//link', 'href');
        $this->findUrls($crawler, $urls, '//script', 'src');

        usort($urls, function (Uri $a, Uri $b) {
            if (!(string) $a || !(string) $b) {
                return 0;
            }
            if (strpos($this->content, (string) $a) === strpos($this->content, (string) $b)) {
                return 0;
            }

            return (strpos($this->content, (string) $a) < strpos($this->content, (string) $b)) ? -1 : 1;
        });

        foreach ($urls as &$uri) {
            try {
                if ($uri->getQuery() !== '') {
                    $query = '?' . $uri->getQuery();
                } else {
                    $query = '';
                }

                if ($uri->getScheme() === '') {
                    if ($uri->getHost() !== '') {
                        $uriString = $this->uri->getScheme() . '://' . $uri->getHost() . $uri->getPath() . $query;
                    } else {
                        if (strpos($uri->getPath(), '/') === 0) {
                            // absolute path
                            $uriString = $this->uri->getScheme() . '://' . $this->uri->getHost() . $uri->getPath() . $query;
                        } else {
                            // relative path
                            if (strpos(strrev($this->uri->getPath()), '/') !== 0) {
                                $separator = '/';
                            } else {
                                $separator = '';
                            }
                            $uriString = $this->uri->getScheme() . '://' . $this->uri->getHost() . $this->uri->getPath() . $separator . $uri->getPath() . $query;
                        }
                    }
                    $uri = new Uri($uriString);
                }
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException($e->getMessage() . ". ($uriString)");
            }
        }

        return $urls;
    }

    /**
     * @return Uri[]
     */
    public function getExternalDependencies($fileExtensions = ['css', 'js'])
    {
        $crawler = new Crawler($this->content);

        $urls = [];

        if (in_array('css', $fileExtensions, true)) {
            $this->findUrls($crawler, $urls, '//link[@rel="stylesheet"]', 'href');
        }

        if (in_array('js', $fileExtensions, true)) {
            $this->findUrls($crawler, $urls, '//script', 'src');
        }

        return $urls;
    }

    private function followableUrl($url)
    {
        // check if data url
        if (strpos($url, 'data:') !== false) {
            return false;
        }
        if ($urlParts = parse_url($url)) {
            if (isset($urlParts['scheme']) && !in_array($urlParts['scheme'], ['http', 'https'], true)) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param $crawler
     * @param Uri[] $urls
     */
    private function findUrls(Crawler $crawler, &$urls, $xpath, $attribute)
    {
        foreach ($crawler->filterXPath($xpath) as $node) {
            if ($node->hasAttribute($attribute) && $this->followableUrl($node->getAttribute($attribute))) {
                $urls[$node->getAttribute($attribute)] = new Uri($node->getAttribute($attribute));
            }
        }
    }
}
