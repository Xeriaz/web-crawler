<?php declare(strict_types=1);

namespace App\Crawler;

use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class Crawler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        EntityManagerInterface $em,
        HttpClientInterface $httpClient,
        Registry $registry
    ) {
        $this->em = $em;
        $this->httpClient = $httpClient;
        $this->registry = $registry;
    }

    protected static $crawledUrls = [];

    public function crawl(string $url, ?Link $parentLink = null): void
    {
        dump($url);
        $link = $this->fetchOrCreateLink($url, $parentLink);

        $response = $this->httpClient->request(
            'GET',
            $link->getLink()
        );

        $this->updateLinkWithResponse($link, $response);

        $content = $this->resolveContent($response);

        if ($content === null) {
            $this->em->persist($link);
            $this->em->flush();

            return;
        }

        self::$crawledUrls[] = $url;

        $linksInContent = (new DomCrawler($content, $url))
            ->filter('a')
            ->links();

        foreach ($linksInContent as $linkInContent) {
            sleep(1);

            $linkUrl = $linkInContent->getUri();

            if (in_array($linkUrl, self::$crawledUrls, true) === true) {
                continue;
            }

            $this->crawl($linkUrl, $link);
        }

        $this->em->flush();
    }

    protected function updateLinkWithResponse(Link $link, ResponseInterface $response): void
    {
        $statusCode = $this->resolveStatusCode($response);

        $this->registry->get($link)->apply(
            $link,
            $this->resolveTransitionByResponse($statusCode)
        );
    }


    protected function resolveStatusCode(ResponseInterface $response): ?int
    {
        try {
            $statusCode = $response->getStatusCode();
        } catch (Throwable $e) {
            $statusCode = null;
        }

        return $statusCode;
    }

    /**
     * @param int|null $statusCode
     * @return string
     */
    private function resolveTransitionByResponse(?int $statusCode): string
    {
        if ($statusCode === null) {
            return Link::TRANSITION_FAILING;
        }

        if ($statusCode >= 500) {
            return Link::TRANSITION_DYING;
        }

        if ($statusCode >= 400) {
            return Link::TRANSITION_FAILING;
        }

        if ($statusCode >= 300) {
            return Link::TRANSITION_REDIRECTING;
        }

        if ($statusCode >= 200) {
            return Link::TRANSITION_SUCCESS;
        }

        return Link::TRANSITION_FAILING;
    }

    protected function resolveContent(ResponseInterface $response): ?string
    {
        try {
            $content = $response->getContent();
        } catch (Throwable $e) {
            $content = null;
        }

        return $content;
    }

    protected function fetchOrCreateLink(string $url, ?Link $parentLink): Link
    {
        $existingLink = $this->em->getRepository(Link::class)->findOneBy(['link' => $url]);

        if ($existingLink !== null) {
            return $existingLink;
        }

        return $this->createLink($url, $parentLink);
    }

    protected function createLink(string $url, ?Link $parentLink): Link
    {
        $newLink = new Link();
        $newLink->setLink($url);

        if ($parentLink !== null) {
            $newLink->addParentLink($parentLink);
        }

        return $newLink;
    }
}
