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

namespace App\Tests\Manager;

use App\Entity\Link;
use App\Manager\LinkManager;
use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\Response\MockResponse;

class LinkManagerTest extends WebTestCase
{
    /** @var string */
    private const URL = 'https://nfq.test';

    private LinkManager $linkManager;

    private EntityManagerInterface $em;

    protected function setUp()
    {
        self::bootKernel();

        $container = self::$container;
        $this->em = self::$container->get(EntityManagerInterface::class);

        $this->linkManager = $container->get(LinkManager::class);
    }

    protected function tearDown(): void
    {
        $link = $this->linkManager->fetchOrCreateLink(self::URL, null);

        $this->em->remove($link);
        $this->em->flush();
    }

    public function testFetchOrCreateLink(): void
    {
        $createdLink = $this->linkManager->fetchOrCreateLink(self::URL, null);

        self::assertNull($createdLink->getId());

        $this->em->persist($createdLink);
        $this->em->flush();

        $fetchedLink = $this->linkManager->fetchOrCreateLink(self::URL, null);
        self::assertNotNull($fetchedLink->getId());
    }

    public function testUpdateLinkWithResponse(): void
    {
        $link = $this->linkManager->createLink(self::URL, null);
        $response = new MockResponse();

        self::assertEquals(Link::STATE_PENDING, $link->getState());
        $this->linkManager->updateLinkWithResponse($link, $response);
        self::assertEquals(Link::STATE_SUCCESS, $link->getState());
    }
}
