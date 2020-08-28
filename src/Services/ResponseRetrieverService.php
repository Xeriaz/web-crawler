<?php

declare(strict_types=1);

namespace App\Services;

use App\Constant\RouteStates;
use App\Entity\Routes;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em)
    {
        $this->client = $client;
        $this->em = $em;
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
        /** @var Routes $route */
        $route = $this->em->getRepository(Routes::class)->findOneBy(['route' => $url]);

        ($route === null) ?: $route->setState(RouteStates::SUCCESS);

        dump('Crawling Url: ' . $url. ', on: ' . date('H:i:s'));

        try {
            $response = $this->client->request('GET', $url);
        } catch (\Exception $e) {
            ($route === null) ?: $route->setState(RouteStates::FAILED);
            $this->em->flush();

            dump('Bad URL: ' . $url);

            return '';
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode !== Response::HTTP_OK) {
            dump('Url: ' . $url . ' Status code is: ' . $statusCode);
            ($route === null) ?: $route->setState(RouteStates::FAILED);
            $this->em->flush();

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
