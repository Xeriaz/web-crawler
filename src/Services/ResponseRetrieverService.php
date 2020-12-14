<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\Registry;
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

    /**
     * @var Registry
     */
    private $workflow;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em, Registry $workflow)
    {
        $this->client = $client;
        $this->em = $em;
        $this->workflow = $workflow;
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
        $stateMachine = $this->workflow->get($link);

        try {
            $response = $this->client->request('GET', $url);
            $statusCode = $response->getStatusCode();
            $link->setHttpStatus($statusCode);

            if ($statusCode !== Response::HTTP_OK) {
                $stateMachine->apply($link, Link::TRANSITION_FAILING);
                $link->setHttpStatus($statusCode);

                return '';
            }

        } catch (\Throwable $e) {
            return '';
        } finally {
            if (isset($statusCode)) {
                $transition = $this->resolveTransitionByStatusCode($statusCode);
            } else {
                $transition = Link::TRANSITION_FAILING;
            }

            $stateMachine->apply($link, $transition);

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

    private function resolveTransitionByStatusCode(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return Link::TRANSITION_DYING;
        }

        if ($statusCode >= 400) {
            return Link::TRANSITION_FAILING;
        }

        if ($statusCode >= 300) {
            return Link::TRANSITION_REDIRECTING;
        }

        if ($statusCode >= 200) {
            return Link::TRANSITION_SUCCESS;
        }

        return Link::TRANSITION_FAILING;
    }
}
