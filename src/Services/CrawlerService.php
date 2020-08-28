<?php

declare(strict_types=1);

namespace App\Services;

use App\Constant\RouteStates;
use App\Entity\BaseRoutes;
use App\Entity\Routes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerService
{
    /** @var string */
    private $baseUrl;

    /** @var BaseRoutes */
    private $baseRoute;

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
        $this->sleepSeconds = $sleepSeconds;
        $this->entityManager = $entityManager;
    }

    public function crawl(string $baseUrl): void
    {
        $html = $this->retrieverService->getResponseContent($baseUrl);
        $this->saveCrawledLinks($html, $baseUrl);

        /** @var Routes $routes */
        $routes = $this->entityManager->getRepository(Routes::class)
            ->findBy(
                [
                    'baseRoute' => $this->baseRoute,
                    'state' => RouteStates::PENDING
                ]
            );

        foreach ($routes as $key => $route) {
            $route->setState(RouteStates::IN_PROGRESS);
            sleep($this->sleepSeconds);

            $this->crawl($route->getRoute());
        }
    }

    private function saveCrawledLinks(string $html, string $baseUrl): void
    {
        $this->setBaseRoute($baseUrl);
        $links = [];

        $crawler_links = (new Crawler($html, $baseUrl))
            ->filter('a')
            ->links();

        foreach ($crawler_links as $link) {
            $link = $link->getUri();

            if (strpos($link, $this->baseRoute->getBaseRoute()) === 0) {
                $state = RouteStates::PENDING;
            } else {
                $state = RouteStates::OUTER;
            }

            $routesRepository = $this->entityManager->getRepository(Routes::class);
            $isRouteExisting = $routesRepository->findOneBy(['route' => $link]);

            if ($isRouteExisting !== null || in_array($link, $links, true)) {
                continue;
            }

            $links[] = $link;

            $route = (new Routes())
                ->setRoute($link)
                ->setState($state)
                ->setBaseRoute($this->baseRoute);

            $this->entityManager->persist($route);
        }

        $this->entityManager->flush();
    }

    public function getSortedLinks(array $links): array
    {
        $urls = $inner = $outer = [];

        foreach ($links as $link) {
            if (strpos($link, $this->baseUrl) === 0) {
                $inner[] = $link;

                continue;
            }

            $outer[] = $link;
        }

        $urls['inner'] = array_unique($inner);
        $urls['outer'] = array_unique($outer);

        return $urls;
    }

    /**
     * @param string $route
     */
    private function setBaseRoute(string $route): void
    {
        if (isset($this->baseRoute)) {
            return;
        }

        $baseRoute = $this->entityManager
            ->getRepository(BaseRoutes::class)
            ->findOneBy(['baseRoute' => $route]);

        if ($baseRoute !== null) {
            $this->baseRoute = $baseRoute;

            return;
        }

        $this->baseRoute = new BaseRoutes();
        $this->baseRoute->setBaseRoute($route);

        $this->entityManager->persist($this->baseRoute);
        $this->entityManager->flush();
    }
}
