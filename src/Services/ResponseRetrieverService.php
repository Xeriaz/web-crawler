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
        $route = $this->getRoute($url);

        $route->setState(RouteStates::SUCCESS);

        dump('Crawling Url: ' . $url. ', on: ' . date('H:i:s'));

        try {
            $response = $this->client->request('GET', $url);
            $statusCode = $response->getStatusCode();

            if ($statusCode !== Response::HTTP_OK) {
                dump('Url: ' . $url . '; Status code is: ' . $statusCode);

                $route->setState(RouteStates::FAILED);
                $route->setHttpStatus($statusCode);

                return '';
            }

            $route->setHttpStatus(Response::HTTP_OK);
        } catch (\Throwable $e) {
            $route->setState(RouteStates::FAILED);

            dump($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            dump('Bad URL: ' . $url);

            return '';
        } finally {
            $this->em->persist($route);
            $this->em->flush();
        }

        return $response->getContent();
    }

    /**
     * @param string $url
     * @return Routes
     */
    private function getRoute(string $url): Routes
    {
        /** @var Routes $route */
        $route = $this->em->getRepository(Routes::class)
            ->findOneBy(['route' => $url]);

        if ($route === null) {
            $route = new Routes();
            $route->setRoute($url);
        }

        return $route;
    }
}
