<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Link;
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
        $link = $this->getLink($url);

        $link->setState(Link::STATE_SUCCESS);

        dump('Crawling Url: ' . $url. ', on: ' . date('H:i:s'));

        try {
            $response = $this->client->request('GET', $url);
            $statusCode = $response->getStatusCode();
            $link->setHttpStatus($statusCode);

            if ($statusCode !== Response::HTTP_OK) {
                dump('Url: ' . $url . '; Status code is: ' . $statusCode);

                $link->setState(Link::STATE_FAILED);
                $link->setHttpStatus($statusCode);

                return '';
            }

        } catch (\Throwable $e) {
            $link->setState(Link::STATE_FAILED);

            dump($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            dump('Bad URL: ' . $url);

            return '';
        } finally {
            $this->em->persist($link);
            $this->em->flush();
        }

        return $response->getContent();
    }

    /**
     * @param string $url
     * @return Link
     */
    private function getLink(string $url): Link
    {
        /** @var Link $links */
        $links = $this->em->getRepository(Link::class)
            ->findOneBy(['link' => $url]);

        if ($links === null) {
            $links = new Link();
            $links->setLink($url);
        }

        return $links;
    }
}
