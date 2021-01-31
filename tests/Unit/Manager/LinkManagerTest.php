<?php

declare(strict_types=1);

namespace App\Tests\Unit\Manager;

use App\Entity\Link;
use App\Manager\LinkManager;
use App\Repository\LinkRepository;
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

    public function testFetchOrCreateLinkWithExistingLink(): void
    {
        $linkRepository = $this->createMock(LinkRepository::class);
        $linkRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['link' => self::LINK_URL])
            ->willReturn(
                $this->getLink()
            );

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(Link::class)
            ->willReturn(
                $linkRepository
            );

        $linkManager = new LinkManager($em, $this->createMock(Registry::class));
        $linkManager->fetchOrCreateLink(self::LINK_URL, null);
    }

    public function testFetchOrCreateLinkWithoutLink(): void
    {
        $linkRepository = $this->createMock(LinkRepository::class);
        $linkRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['link' => self::LINK_URL])
            ->willReturn(
                null
            );

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(Link::class)
            ->willReturn(
                $linkRepository
            );

        $linkManager = new LinkManager($em, $this->createMock(Registry::class));
        $linkManager->fetchOrCreateLink(self::LINK_URL, null);
    }

    public function testCreateLink(): void
    {
        $linkManager = new LinkManager(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(Registry::class)
        );

        $link = $linkManager->createLink(self::LINK_URL, null);
        $child = $linkManager->createLink(self::LINK_URL, $link);

        self::assertEquals($child->getParentLinks()->first(), $link);
    }

    /**
     * @dataProvider getData
     *
     * @param Link $link
     * @param ResponseInterface $response
     * @param string $expectedTransition
     */
    public function testUpdateLinkWithResponse(
        Link $link,
        ResponseInterface $response,
        string $expectedTransition
    ): void {
        $em = $this->getMockedEntityManager();

        $workflow = $this->createMock(Workflow::class);
        $workflow->expects(self::once())
            ->method('apply')
            ->with($link, $expectedTransition);

        $registry = $this->getMockedRegistry($workflow, $link);

        $linkManager = new LinkManager($em, $registry);

        $linkManager->updateLinkWithResponse($link, $response);
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
            $getData(null, Link::TRANSITION_FAILING),
            $getData('100', Link::TRANSITION_FAILING),
            $getData('200', Link::TRANSITION_SUCCESS),
            $getData('300', Link::TRANSITION_REDIRECTING),
            $getData('400', Link::TRANSITION_FAILING),
            $getData('500', Link::TRANSITION_DYING),
        ];
    }

    private function getLink(string $linkUrl = self::LINK_URL): Link
    {
        $link = new Link();

        $link->setLink($linkUrl);
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
     * @param Link $link
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
