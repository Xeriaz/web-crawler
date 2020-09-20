<?php

declare(strict_types=1);

namespace App\Services;

use App\Constant\LinksStates;
use App\Entity\Links;
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
    ) {
        $this->retrieverService = $retrieverService;
        $this->entityManager = $entityManager;
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

        $state = ($linkExist !== null) ? LinksStates::SKIPPED : LinksStates::PENDING;

        $link = (new Links())
            ->setLink($baseUrl)
            ->setState($state);

        $this->entityManager->persist($link);
        $crawlerLinks = (new Crawler($html, $baseUrl))
            ->filter('a')
            ->links();

        foreach ($crawlerLinks as $crawlerLink) {
            $uri = $crawlerLink->getUri();

            $linkExist = $this->entityManager
                ->getRepository(Links::class)
                ->findOneBy(['link' => $uri]);

            $state = ($linkExist !== null) ? LinksStates::SKIPPED : LinksStates::PENDING;

            $innerLink = (new Links())
                ->setLink($uri)
                ->setState($state)
                ->addParentLink($link);

            $this->entityManager->persist($innerLink);
        }

        $this->entityManager->flush();
    }
}
