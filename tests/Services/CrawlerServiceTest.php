<?php

/**
 * @copyright C UAB NFQ Technologies
 *
 * This Software is the property of NFQ Technologies
 * and is protected by copyright law – it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * Contact UAB NFQ Technologies:
 * E-mail: info@nfq.lt
 * http://www.nfq.lt
 */

declare(strict_types=1);

namespace App\Tests\Services;

use App\Services\CrawlerService;
use App\Services\ResponseRetrieverService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Workflow\Registry;

class CrawlerServiceTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var CrawlerService
     */
    private $crawler;

    public function __construct(Registry $registry, CrawlerService $crawler)
    {
        $this->registry = $registry;
        $this->crawler = $crawler;
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testCrawl()
    {
        $this->crawler->crawl('https://untools.co');die;

        $crawler = new CrawlerService(
            $this->getMockedResponseRetriever(),
            $this->entityManager,
            $this->registry,
            0
        );

        dump($crawler->crawl('https://untools.co'));


    }

    private function getMockedResponseRetriever()
    {
        $responseRetriever = $this->getMockBuilder(ResponseRetrieverService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $responseRetriever->method('getResponseContent')->willReturn($this->getHtml());

        return $responseRetriever;
    }

    private function getHtml()
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <p class="message">Hello World!</p>
        <p>Hello Crawler!</p>
        <a href="test-link.test">Test</a>
        <a href="outer-link.test">Outer</a>
    </body>
</html>
HTML;
    }
}
