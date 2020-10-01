<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

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
     * @var int
     */
    private $sleepSeconds;

    public function __construct(
        ResponseRetrieverService $retrieverService,
        EntityManagerInterface $entityManager,
        int $sleepSeconds
    )
    {
        $this->retrieverService = $retrieverService;
        $this->entityManager = $entityManager;
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

    private function saveCrawledLinks(string $html, string $baseUrl): void
    {
        $state = $this->resolveState($baseUrl);

        $link = (new Link())
            ->setLink($baseUrl)
            ->setState($state);

        $this->entityManager->persist($link);
        $crawlerLinks = (new Crawler($html, $baseUrl))
            ->filter('a')
            ->links();

        foreach ($crawlerLinks as $crawlerLink) {
            $uri = $crawlerLink->getUri();
            $state = $this->resolveState($uri);

            $innerLink = (new Link())
                ->setLink($uri)
                ->setState($state)
                ->addParentLink($link);

            $this->entityManager->persist($innerLink);
        }

        $this->entityManager->flush();
    }

    /**
     * @param string $baseUrl
     * @return string
     */
    private function resolveState(string $baseUrl): string
    {
        $linkExist = $this->entityManager
            ->getRepository(Link::class)
            ->findOneBy(['link' => $baseUrl]);

        return ($linkExist !== null) ? Link::STATE_SKIPPED : Link::STATE_PENDING;
    }
}
