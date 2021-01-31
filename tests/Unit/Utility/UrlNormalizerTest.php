<?php declare(strict_types=1);

namespace App\Tests\Unit\Utility;

use App\Utility\UrlNormalizer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UrlNormalizerTest extends WebTestCase
{
    private const LINK_URL = 'https://test.com';

    /**
     * @dataProvider getUrls
     * @param string $url
     */
    public function testNormalizeUrl(string $url): void
    {
        $normalizer = new UrlNormalizer();

        $normalizedUrl = $normalizer->normalize($url);

        self::assertEquals(self::LINK_URL, $normalizedUrl);
    }

    /**
     * @return string[]
     */
    public function getUrls(): array
    {
        return [
            [self::LINK_URL . '?test=true'],
            [self::LINK_URL . '?test=true#anchor'],
            [self::LINK_URL . '#'],
            [self::LINK_URL . '/'],
        ];
    }
}
