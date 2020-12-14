<?php declare(strict_types=1);

namespace App\Crawler;

use App\Entity\Link;
use App\Event\CrawlEvent;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class ResponseParser
{
    protected const DEFAULT_SLEEP_IN_SECONDS = 1;

    protected static array $crawledUrls = [];

    private EventDispatcherInterface $eventDispatcher;

    private int $sleepInSeconds;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        int $sleepInSeconds = self::DEFAULT_SLEEP_IN_SECONDS
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->sleepInSeconds = $sleepInSeconds;
    }

    public function parseResponse(Link $link, ResponseInterface $response): void
    {
        self::$crawledUrls[] = $link->getLink();

        $content = $this->resolveContent($response);
        if ($content === null) {
            return;
        }

        $this->parseContent($link, $content);
    }

    protected function parseContent(Link $link, string $content): void
    {
        $linksInContent = $this->extractLinks($content, $link->getLink());

        foreach ($linksInContent as $linkInContent) {
            sleep($this->sleepInSeconds);

            $linkUrl = $linkInContent->getUri();
            if (in_array($linkUrl, self::$crawledUrls, true) === true) {
                continue;
            }

            $this->eventDispatcher->dispatch(
                (new CrawlEvent($linkUrl, $link))
            );
        }
    }

    /**
     * @return \Symfony\Component\DomCrawler\Link[]
     */
    protected function extractLinks(string $content, string $url)
    {
        return (new DomCrawler($content, $url))
            ->filter('a')
            ->links();
    }

    /**
     * @param ResponseInterface $response
     *
     * @return string|null
     */
    protected function resolveContent(ResponseInterface $response): ?string
    {
        try {
            $content = $response->getContent();
        } catch (Throwable $e) {
            $content = null;
        }

        return $content;
    }
}
