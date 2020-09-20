<?php

declare(strict_types=1);

namespace App\Services;

use App\Constant\RouteStates;
use App\Entity\Routes;
use App\Repository\RoutesRepository;
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
     * @var RoutesRepository
     */
    private $repository;

    /**
     * @var int
     */
    private $sleepSeconds;

    public function __construct(
        ResponseRetrieverService $retrieverService,
        EntityManagerInterface $entityManager,
        RoutesRepository $repository,
        int $sleepSeconds
    ) {
        $this->retrieverService = $retrieverService;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->sleepSeconds = $sleepSeconds;
    }

    public function crawl(string $baseUrl): void
    {
        $html = $this->retrieverService->getResponseContent($baseUrl);
        $this->saveCrawledLinks($html, $baseUrl);

        /** @var Routes $routes */
        $routes = $this->repository
            ->findPendingRoutesByBaseUrl($baseUrl);

        foreach ($routes as $route) {
            sleep($this->sleepSeconds);

            $this->crawl($route->getRoute());
        }
    }

    private function saveCrawledLinks(string $html, string $baseUrl): void
    {
        $isRouteExisting = $this->repository->findOneBy(['route' => $baseUrl]);

        $state = ($isRouteExisting !== null) ? RouteStates::SKIPPED : RouteStates::PENDING;

        $route = (new Routes())
            ->setRoute($baseUrl)
            ->setState($state);

        $this->entityManager->persist($route);
        $crawler_links = (new Crawler($html, $baseUrl))
            ->filter('a')
            ->links();

        foreach ($crawler_links as $link) {
            $link = $link->getUri();

            $isRouteExisting = $this->repository->findOneBy(['route' => $link]);

            $state = ($isRouteExisting !== null) ? RouteStates::SKIPPED : RouteStates::PENDING;

            $innerRoute = (new Routes())
                ->setRoute($link)
                ->setState($state)
                ->addParentRoute($route);

            $this->entityManager->persist($innerRoute);
        }

        $this->entityManager->flush();
    }
}
