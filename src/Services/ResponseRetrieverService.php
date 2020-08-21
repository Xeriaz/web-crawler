<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ResponseRetrieverService
{
    /** @var string[] */
    private $visitedUrls = [];

    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $url
     * @return string
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getResponseContent(string $url): string
    {
        try {
            $response = $this->client->request(
                'GET',
                $url
            );
        } catch (\Exception $e) {
            dump('Bad URL: ' . $url);

            return '';
        }

        $this->visitedUrls[] = $url;

        $statusCode = $response->getStatusCode();

        if ($statusCode !== Response::HTTP_OK) {
            dump('Url: ' . $url . 'Status code is: ' . $statusCode);
            return '';
        }

        return $response->getContent();
    }

    /**
     * @param string $url
     * @return bool
     */
    public function isUrlVisited(string $url): bool
    {
        return in_array($url, $this->visitedUrls);
    }

    public function getVisitedUrl()
    {
        return $this->visitedUrls;
    }
}
