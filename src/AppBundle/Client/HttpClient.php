<?php

namespace AppBundle\Client;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Symfony\Component\DomCrawler\Crawler;

class HttpClient extends Client implements PageGetterInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $stack = HandlerStack::create();
        $stack->push(new CacheMiddleware(), 'cache');

        parent::__construct([
            'handler' => $stack,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getPageDom($url)
    {
        $response = $this->request('GET', $url);

        $responseBody = (string)$response->getBody();

        $crawler = new Crawler($responseBody, $url);

        return $crawler;
    }
}
