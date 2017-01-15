<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\ListRetriever;

use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\MultiHttpAdapterException;
use Psr\Http\Message\UriInterface;
use whm\Crawler\Http\RequestFactory;
use whm\Html\Uri;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever as SmokeRetriever;
use whm\Smoke\Scanner\SessionContainer;

class Retriever implements SmokeRetriever
{
    private $urls = [];

    /**
     * @var HttpAdapterInterface
     */
    private $httpClient;
    private $urlStack = [];

    private $redirects = array();

    /**
     * @var SessionContainer
     */
    private $sessionContainer;

    public function init($urls)
    {
        if (is_array($urls)) {
            foreach ($urls as $key => $urlList) {
                foreach ($urlList as $url) {
                    if (is_array($url)) {
                        $uri = new Uri($url['url']);
                        if (array_key_exists('cookies', $url)) {
                            foreach ($url['cookies'] as $cookie) {
                                foreach ($cookie as $key => $value) {
                                    $uri->addCookie($key, $value);
                                }
                            }
                        }
                        if (array_key_exists('session', $url)) {
                            $sessionName = $url['session'];
                            $uri->setSessionIdentifier($sessionName);
                        }
                        $this->urls[$url['url']] = ['url' => $uri, 'system' => $key];
                    } else {
                        $this->urls[$url] = ['url' => new Uri($url), 'system' => $key];
                    }
                }
            }
            $this->urlStack = $this->urls;
        }
    }

    /**
     * @param Uri $uri
     *
     * @return \Ivory\HttpAdapter\Message\Request
     */
    private function createRequest(Uri $uri)
    {
        $headers = ['Accept-Encoding' => 'gzip', 'Connection' => 'keep-alive'];

        if ($uri->getSessionIdentifier()) {
            $session = $this->sessionContainer->getSession($uri->getSessionIdentifier());
            foreach ($session->getCookies() as $key => $value) {
                $uri->addCookie($key, $value);
            }
        }

        if ($uri->hasCookies()) {
            $headers['Cookie'] = $uri->getCookieString();
        }

        $request = RequestFactory::getRequest($uri, 'GET', 'php://memory', $headers);

        return $request;
    }

    public function next()
    {
        if (empty($this->urlStack)) {
            return false;
        }

        $url = array_pop($this->urlStack);

        $request = $this->createRequest(new Uri($url['url']));

        try {
            $responses = $this->httpClient->sendRequests(array($request));
        } catch (MultiHttpAdapterException $e) {
            $exceptions = $e->getExceptions();
            /** @var \Exception[] $exceptions */
            $errorMessages = '';
            foreach ($exceptions as $exception) {
                // @fixme this must be part of the http client
                $message = $exception->getMessage();
                if (strpos($message, 'An error occurred when fetching the URI') === 0) {
                    $corruptUrl = substr($message, '41', strpos($message, '"', 41) - 41);
                    if (strpos($corruptUrl, '/') === 0) {
                        /* @var \Ivory\HttpAdapter\HttpAdapterException $exception */

                        $mainUri = $request->getUri();
                        $this->redirects[(string) $mainUri->getScheme() . '://' . $mainUri->getHost() . $corruptUrl] = (string) $mainUri;

                        $this->urls[] = ['url' => $mainUri->getScheme() . '://' . $mainUri->getHost() . $corruptUrl, 'system' => $url['system']];
                        $this->urlStack[] = ['url' => $mainUri->getScheme() . '://' . $mainUri->getHost() . $corruptUrl, 'system' => $url['system']];

                        return $this->next();
                    }

                    // the error handling should be done withing the calling class
                    echo "\n   " . $exception->getMessage() . "\n";

                    return $this->next();
                } else {
                    $errorMessages .= $exception->getMessage() . "\n";
                }
            }
            if ($errorMessages !== '') {
                throw new \RuntimeException($errorMessages);
            }
        }

        return $responses[0];
    }

    public function getOriginUri(UriInterface $uri)
    {
        if (array_key_exists((string) $uri, $this->redirects)) {
            return $this->urls[$this->redirects[(string) $uri]]['url'];
        }

        return $uri;
    }

    public function getComingFrom(UriInterface $uri)
    {
        return $uri;
    }

    public function getSystem(UriInterface $uri)
    {
        if (array_key_exists((string) $uri, $this->redirects)) {
            return $this->urls[$this->redirects[(string) $uri]]['system'];
        }

        return $this->urls[(string) $uri]['system'];
    }

    public function getSystems()
    {
        $systems = [];
        foreach ($this->urls as $key => $url) {
            $systems[] = $url['system'];
        }

        return $systems;
    }

    public function setHttpClient(HttpAdapterInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function setSessionContainer(SessionContainer $sessionContainer)
    {
        $this->sessionContainer = $sessionContainer;
    }
}
