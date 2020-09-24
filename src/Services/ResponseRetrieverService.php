<?php

declare(strict_types=1);

namespace App\Services;

use App\Constant\LinksStates;
use App\Constant\Workflows;
use App\Constant\WorkflowTransitions;
use App\Entity\Links;
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
        $stateMachine = $this->workflow->get($link, Workflows::LINK_CRAWLING);

        dump('Crawling Url: ' . $url. ', on: ' . date('H:i:s'));

        try {
            $response = $this->client->request('GET', $url);
            $statusCode = $response->getStatusCode();

            if ($statusCode !== Response::HTTP_OK) {
                dump('Url: ' . $url . '; Status code is: ' . $statusCode);

                $stateMachine->apply($link, WorkflowTransitions::FAILING);
                $link->setHttpStatus($statusCode);

                return '';
            }

            $link->setHttpStatus(Response::HTTP_OK);
        } catch (\Throwable $e) {
            dump($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            dump('Bad URL: ' . $url);

            return '';
        } finally {
            if (isset($statusCode)) {
                $transition = $this->resolveTransitionByStatusCode($statusCode);
            } else {
                $transition = WorkflowTransitions::FAILING;
            }

            $stateMachine->apply($link, $transition);

            $this->em->persist($link);
            $this->em->flush();
        }

        return $response->getContent();
    }

    /**
     * @param string $url
     * @return Links
     */
    private function getLink(string $url): Links
    {
        /** @var Links $links */
        $links = $this->em->getRepository(Links::class)
            ->findOneBy(['link' => $url]);

        if ($links === null) {
            $links = new Links();
            $links->setLink($url);
        }

        return $links;
    }

    private function resolveTransitionByStatusCode(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return WorkflowTransitions::DYING;
        }

        if ($statusCode >= 400) {
            return WorkflowTransitions::FAILING;
        }

        if ($statusCode >= 300) {
            return WorkflowTransitions::REDIRECTING;
        }

        if ($statusCode >= 200) {
            return WorkflowTransitions::SUCCESS;
        }

        return WorkflowTransitions::FAILING;
    }
}
