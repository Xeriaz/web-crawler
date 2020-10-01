<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Link;
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
    private $workflow;

    /**
     * @var int
     */
    private $sleepSeconds;

    public function __construct(
        ResponseRetrieverService $retrieverService,
        EntityManagerInterface $entityManager,
        Registry $workflow,
        int $sleepSeconds
    ) {
        $this->retrieverService = $retrieverService;
        $this->entityManager = $entityManager;
        $this->workflow = $workflow;
        $this->sleepSeconds = $sleepSeconds;
    }

    public function crawl(string $baseUrl): void
    {
        $html = $this->retrieverService->getResponseContent($baseUrl);
        $this->saveCrawledLinks($html, $baseUrl);

        /** @var Link $links */
        $links = $this->entityManager
            ->getRepository(Link::class)
            ->findPendingLinksByBaseUrl($baseUrl);

        foreach ($links as $link) {
            sleep($this->sleepSeconds);

            $this->crawl($link->getLink());
        }
    }

    /**
     * @param string $html
     * @param string $baseUrl
     */
    private function saveCrawledLinks(string $html, string $baseUrl): void
    {
        $link = (new Link())->setLink($baseUrl);
        $this->applyState($link, $baseUrl);

        $crawlerLinks = (new Crawler($html, $baseUrl))
            ->filter('a')
            ->links();

        foreach ($crawlerLinks as $crawlerLink) {
            $uri = $crawlerLink->getUri();

            $innerLink = (new Link())
                ->setLink($uri)
                ->addParentLink($link);

            $this->applyState($innerLink, $uri);
        }

        $this->entityManager->flush();
    }

    /**
     * @param string $url
     * @return string
     */
    private function resolveTransition(string $url): string
    {
        $linkExist = $this->entityManager
            ->getRepository(Link::class)
            ->findOneBy(['link' => $url]);

        return ($linkExist !== null) ? Link::TRANSITION_SKIPPING : Link::TRANSITION_PENDING;
    }

    /**
     * @param Link $link
     * @param string $url
     * @return void
     */
    private function applyState(Link $link, string $url): void
    {
        $stateMachine = $this->workflow->get($link);
        $stateMachine->apply($link, $this->resolveTransition($url));

        $this->entityManager->persist($link);
    }
}
