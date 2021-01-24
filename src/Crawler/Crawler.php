<?php declare(strict_types=1);

namespace App\Crawler;

use App\Entity\Link;
use App\Manager\LinkManager;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Crawler
{
    private LinkManager $linkManager;

    private HttpClientInterface $httpClient;

    private ResponseParser $responseParser;

    public function __construct(
        LinkManager $linkManager,
        HttpClientInterface $httpClient,
        ResponseParser $responseParser
    ) {
        $this->linkManager = $linkManager;
        $this->httpClient = $httpClient;
        $this->responseParser = $responseParser;
    }

    public function crawl(string $url, ?Link $parentLink = null): void
    {
        $link = $this->linkManager->fetchOrCreateLink($url, $parentLink);

        if ($link === null) {
            return;
        }

        $response = $this->httpClient->request('GET', $link->getLink());

        $this->linkManager->updateLinkWithResponse($link, $response);

        $this->responseParser->parseResponse($link, $response);
    }
}
