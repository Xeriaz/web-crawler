<?php

declare(strict_types=1);

namespace App\Tests\Unit\Manager;

use App\Entity\Link;
use App\Manager\LinkManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;
use Symfony\Contracts\HttpClient\ResponseInterface;

class LinkManagerTest extends WebTestCase
{
    private const LINK_URL = 'https://nfq.test';

    private LinkManager $linkManager;

    protected function setUp()
    {
        $em = $this->getMockedEntityManager();

        $workflow = $this->createMock(Workflow::class);
        $registry = $this->getMockedRegistry($workflow);

        $this->linkManager = new LinkManager($em, $registry);
    }

    /**
     * @dataProvider getData
     * @param Link $link
     * @param ResponseInterface $response
     */
    public function testUpdateLinkWithResponse(Link $link, ResponseInterface $response): void
    {
        $this->linkManager->updateLinkWithResponse($link, $response);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return [
            [$this->getLink(), new MockResponse('', ['http_code' => '100'])],
//            [$this->getLink(), new MockResponse('', ['http_code' => '200'])],
//            [$this->getLink(), new MockResponse('', ['http_code' => '300'])],
//            [$this->getLink(), new MockResponse('', ['http_code' => '400'])],
//            [$this->getLink(), new MockResponse('', ['http_code' => '500'])],
        ];
    }

    private function getLink()
    {
        $link = new Link();

        $link->setLink(self::LINK_URL);
        $link->setState(Link::STATE_PENDING);

        return $link;
    }

    /**
     * @return EntityManagerInterface|MockObject
     */
    private function getMockedEntityManager()
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects($this->any())
            ->method('persist');

        $em->expects($this->any())
            ->method('flush');

        return $em;
    }

    /**
     * @param MockObject $workflow
     * @return MockObject|Registry
     */
    private function getMockedRegistry(MockObject $workflow)
    {
        $registry = $this->createMock(Registry::class);

        $registry->expects($this->any())
            ->method('get')
            ->willReturn($workflow);

        return $registry;
    }
}
