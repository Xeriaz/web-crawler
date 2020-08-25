<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class CrawlerService
{
    /** @var string */
    private $baseUrl;

    /**
     * @var ResponseRetrieverService
     */
    private $retrieverService;

    public function __construct(ResponseRetrieverService $retrieverService)
    {
        $this->retrieverService = $retrieverService;
    }

    public function crawl(string $baseUrl): array
    {
        $html = $this->retrieverService->getResponseContent($baseUrl);

        $links = $this->getLinks($html, $baseUrl);

        foreach ($links as $key => $link) {
            if ($this->retrieverService->isUrlVisited($link)
                || strpos($link, $this->baseUrl) !== 0
            ) {
                continue;
            }

            return array_merge($links, array_unique($this->crawl($link)));
        }

        return $links;
    }

    private function getLinks(string $html, string $baseUrl): array
    {
        $links = [];

        if (isset($this->baseUrl) === false) {
            $this->baseUrl = $baseUrl;
        }

        $crawler = new Crawler($html, $baseUrl);

        $crawler_links = $crawler->filter('a')->links();

        foreach ($crawler_links as $link) {
            $links[] = $link->getUri();
        }

        return $links;
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
}
