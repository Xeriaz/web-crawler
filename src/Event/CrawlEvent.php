<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Link;
use Symfony\Contracts\EventDispatcher\Event;

class CrawlEvent extends Event
{
    public const NAME = 'app.crawler.crawl';

    private string $url;

    private ?Link $parentLink;

    public function __construct(string $url, ?Link $parentLink)
    {
        $this->url = $url;
        $this->parentLink = $parentLink;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getParentLink(): ?Link
    {
        return $this->parentLink;
    }
}
