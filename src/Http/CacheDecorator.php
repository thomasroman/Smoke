<?php

namespace whm\Smoke\Http;

use Cache\Adapter\Common\CacheItem;
use GuzzleHttp\Psr7\Request;
use Ivory\HttpAdapter\ConfigurationInterface;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use whm\Smoke\Extensions\SmokeHttpClient\CacheAware;

class CacheDecorator implements HttpAdapterInterface, CacheAware
{
    private $client;
    private $cacheItemPool;

    private $disabled = false;

    public function __construct(HttpAdapterInterface $client, CacheItemPoolInterface $cacheItemPool)
    {
        $this->client = $client;
        $this->cacheItemPool = $cacheItemPool;
    }

    public function disableCache()
    {
        $this->disabled = true;
    }

    public function enableCache()
    {
        $this->disabled = false;
    }

    /**
     * Serialize a response so it can be stored inside a cache element.
     *
     * @param Response $response
     * @return string
     */
    private function serializeResponse(Response $response)
    {
        $array = ['object' => serialize($response), 'body' => (string)$response->getBody()];
        return serialize($array);
    }

    /**
     * Unserialze a response from cache element
     *
     * @param $serializedResponse
     * @return Response
     */
    private function unserializeResponse($serializedResponse)
    {
        $array = unserialize($serializedResponse);

        $response = unserialize($array['object']);
        /** @var Response $response */
        $response->setBody($array['body']);

        return $response;
    }

    private function getHash($uri, $headers, $method = "")
    {
        return md5(json_encode((string)$uri) . json_encode($headers) . strtolower($method));
    }

    private function cacheResponse($key, Response $response)
    {
        $serializedResponse = $this->serializeResponse($response);

        $item = new CacheItem($key);
        $item->set($serializedResponse);
        $item->expiresAfter(new \DateInterval('PT5M'));

        $this->cacheItemPool->save($item);
    }

    public function get($uri, array $headers = array())
    {
        if ($this->disabled) {
            return $this->client->get($uri, $headers);
        } else {
            $key = $this->getHash($uri, $headers, 'GET');

            if ($this->cacheItemPool->hasItem($key)) {
                $serializedResponse = $this->cacheItemPool->getItem($key)->get();
                return $this->unserializeResponse($serializedResponse);
            } else {
                $response = $this->client->get($uri, $headers);
                /** @var Response $response */
                $this->cacheResponse($key, $response);
                return $response;
            }
        }
    }

    public function head($uri, array $headers = array())
    {
        echo "Cache Decorator: the method head is not implemented yet";
        return $this->client->head($uri, $headers);
    }

    public function trace($uri, array $headers = array())
    {
        return $this->client->trace($uri, $headers);
    }

    public function post($uri, array $headers = array(), $datas = array(), array $files = array())
    {
        var_dump('post');
        return $this->client->post($uri, $headers, $datas, $files);
    }

    public function put($uri, array $headers = array(), $datas = array(), array $files = array())
    {
        return $this->client->put($uri, $headers, $datas, $files);
    }

    public function patch($uri, array $headers = array(), $datas = array(), array $files = array())
    {
        return $this->client->patch($uri, $headers, $datas, $files);
    }

    public function delete($uri, array $headers = array(), $datas = array(), array $files = array())
    {
        return $this->client->delete($uri, $headers, $datas, $files);
    }

    public function options($uri, array $headers = array(), $datas = array(), array $files = array())
    {
        return $this->client->options($uri, $headers, $datas, $files);
    }

    public function send($uri, $method, array $headers = array(), $datas = array(), array $files = array())
    {
        echo "Cache Decorator: the method send is not implemented yet";
        return $this->client->send($uri, $method, $headers, $datas, $files);
    }

    public function getConfiguration()
    {
        return $this->client->getConfiguration();
    }

    public function setConfiguration(ConfigurationInterface $configuration)
    {
        return $this->client->setConfiguration($configuration);
    }

    public function sendRequest(RequestInterface $request)
    {
        echo "Cache Decorator: the method sendRequest is not implemented yet";
        return $this->client->sendRequest($request);
    }

    /**
     * @param Request[] $requests
     * @return Response[]
     */
    public function sendRequests(array $requests)
    {
        if ($this->disabled) {
            return $this->client->sendRequests($requests);
        } else {
            $responses = array();

            foreach ($requests as $id => $request) {
                $key = $this->getHash($request->getUri(), $request->getHeaders(), $request->getMethod());
                if ($this->cacheItemPool->hasItem($key)) {
                    $responses[] = $this->unserializeResponse($this->cacheItemPool->getItem($key)->get());
                    unset($requests[$id]);
                }
            }

            $newResponses = $this->client->sendRequests($requests);

            foreach ($newResponses as $newResponse) {
                /** @var Response $newResponse */
                $key = $this->getHash($newResponse->getRequest()->getUri(), $newResponse->getRequest()->getHeaders());
                $this->cacheResponse($key, $newResponse);
            }

            $responses = array_merge($responses, $newResponses);

            return $responses;
        }
    }

    public function getName()
    {
        return $this->client->getName();
    }
}