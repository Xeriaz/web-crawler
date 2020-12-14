<?php declare(strict_types=1);

namespace App\EventListener;

use App\Crawler\Crawler;
use App\Event\CrawlEvent;

class CrawlerListener
{
    private Crawler $crawler;

    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function onCrawlEvent(CrawlEvent $event): void
    {
        $this->crawler->crawl(
            $event->getUrl(),
            $event->getParentLink()
        );
    }
}
