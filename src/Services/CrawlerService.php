<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;

class CrawlerService
{
    /**
     * @param string $html
     * @param string $url
     */
    public function crawl(string $html, string $url)
    {
        $innerUrl = $externalUrl = [];

        $crawler = new Crawler($html, $url);

        $links = $crawler->filter('a')->links();

        foreach ($links as $link) {
            if (strpos($link->getUri(), $url) === 0) {
                $innerUrl[] = $link->getUri();
            }

            $externalUrl[] = $link->getUri();
        }

        return [$innerUrl, $externalUrl];
    }
}
