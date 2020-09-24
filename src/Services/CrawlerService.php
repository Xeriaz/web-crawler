<?php

declare(strict_types=1);

namespace App\Services;

use App\Constant\LinksStates;
use App\Constant\Workflows;
use App\Constant\WorkflowTransitions;
use App\Entity\Links;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Workflow\Registry;

class CrawlerService
{
    /**
     * @var ResponseRetrieverService
     */
    private $retrieverService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Registry
     */
    private $workflows;

    /**
     * @var int
     */
    private $sleepSeconds;

    public function __construct(
        ResponseRetrieverService $retrieverService,
        EntityManagerInterface $entityManager,
        Registry $workflows,
        int $sleepSeconds
    ) {
        $this->retrieverService = $retrieverService;
        $this->entityManager = $entityManager;
        $this->workflows = $workflows;
        $this->sleepSeconds = $sleepSeconds;
    }

    public function crawl(string $baseUrl): void
    {
        $html = $this->retrieverService->getResponseContent($baseUrl);
        $this->saveCrawledLinks($html, $baseUrl);

        /** @var Links $links */
        $links = $this->entityManager
            ->getRepository(Links::class)
            ->findPendingLinksByBaseUrl($baseUrl);

        foreach ($links as $link) {
            sleep($this->sleepSeconds);

            $this->crawl($link->getLink());
        }
    }

    private function saveCrawledLinks(string $html, string $baseUrl): void
    {
        $linkExist = $this->entityManager
            ->getRepository(Links::class)
            ->findOneBy(['link' => $baseUrl]);

        $transition = ($linkExist !== null) ? WorkflowTransitions::SKIPPING : WorkflowTransitions::PENDING;

        $link = (new Links())
            ->setLink($baseUrl);

        $stateMachine = $this->workflows->get($link, Workflows::LINK_CRAWLING);

        $stateMachine->apply($link, $transition);

        $this->entityManager->persist($link);
        $crawlerLinks = (new Crawler($html, $baseUrl))
            ->filter('a')
            ->links();

        foreach ($crawlerLinks as $crawlerLink) {
            $uri = $crawlerLink->getUri();

            $linkExist = $this->entityManager
                ->getRepository(Links::class)
                ->findOneBy(['link' => $uri]);

            $transition = ($linkExist !== null) ? WorkflowTransitions::SKIPPING : WorkflowTransitions::PENDING;

            $innerLink = (new Links())
                ->setLink($uri)
                ->addParentLink($link);

            $stateMachine = $this->workflows->get($innerLink, Workflows::LINK_CRAWLING);
            $stateMachine->apply($innerLink, $transition);

            $this->entityManager->persist($innerLink);
        }

        $this->entityManager->flush();
    }
}
