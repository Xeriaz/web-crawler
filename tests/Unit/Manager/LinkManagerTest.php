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
    }

    /**
     * @dataProvider getData
     *
     * @param Link $link
     * @param ResponseInterface $response
     * @param string $expected
     */
    public function testUpdateLinkWithResponse(Link $link, ResponseInterface $response, string $expected): void
    {
        $em = $this->getMockedEntityManager();

        $workflow = $this->createMock(Workflow::class);
        $workflow->expects($this->once())
            ->method('apply')
            ->with($link, $expected);

        $registry = $this->getMockedRegistry($workflow, $link);

        $this->linkManager = new LinkManager($em, $registry);

        $this->linkManager->updateLinkWithResponse($link, $response);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $getData = function($httpCode, $expectedTransition) {
            return [$this->getLink(), new MockResponse('', ['http_code' => $httpCode]), $expectedTransition];
        };

        return [
//            [$this->getLink(), new MockResponse('', ['http_code' => '100']), ],
            $getData('200', Link::TRANSITION_SUCCESS),
            $getData('300', Link::TRANSITION_REDIRECTING),
            $getData('400', Link::TRANSITION_FAILING),
            $getData('500', Link::TRANSITION_DYING),
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

        $em->expects($this->once())
            ->method('persist');

        $em->expects($this->once())
            ->method('flush');

        return $em;
    }

    /**
     * @param MockObject $workflow
     * @return MockObject|Registry
     */
    private function getMockedRegistry(MockObject $workflow, Link $link)
    {

        $registry = $this->createMock(Registry::class);

        $registry->expects($this->any())
            ->method('get')
            ->with($link)
            ->willReturn($workflow);

        return $registry;
    }
}
