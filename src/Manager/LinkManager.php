<?php declare(strict_types=1);

namespace App\Manager;

use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\UrlValidator;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class LinkManager
{
    private EntityManagerInterface $em;

    private Registry $registry;

    public function __construct(
        EntityManagerInterface $em,
        Registry $registry
    ) {
        $this->em = $em;
        $this->registry = $registry;
    }

    public function fetchOrCreateLink(string $url, ?Link $parentLink): ?Link
    {
        $url = $this->normalizeUrl($url);

        $existingLink = $this->em->getRepository(Link::class)->findOneBy(['link' => $url]);

        if ($existingLink !== null) {
            return $existingLink;
        }

        if ($this->isValidUrl($url) === false) {
            dump('Url is not valid: ' . $url);

            return null;
        }


        return $this->createLink($url, $parentLink);
    }

    public function createLink(string $url, ?Link $parentLink): Link
    {
        $newLink = new Link();
        $newLink->setLink($url);

        if ($parentLink !== null) {
            $newLink->addParentLink($parentLink);
        }

        return $newLink;
    }

    public function updateLinkWithResponse(Link $link, ResponseInterface $response): void
    {
        $statusCode = $this->resolveStatusCode($response);

        $this->registry->get($link)->apply(
            $link,
            $this->resolveTransitionByResponse($statusCode)
        );

        $this->em->persist($link);
        $this->em->flush();
    }

    public function isValidUrl(string $url): bool
    {
        $needles = ['http://', 'https://'];

        foreach ($needles as $needle) {
            if (strpos($url, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    public function normalizeUrl(string $url): string
    {
        $url = explode('?', $url)[0];
        $url = explode('#', $url)[0];

        return $url;
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
}
