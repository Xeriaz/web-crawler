<?php

namespace App\Controller;

use App\Entity\Link;
use App\Crawler\Crawler;
use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    /**
     * @var Crawler
     */
    private Crawler $crawler;

    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * @Route("/", name="link_index", methods={"GET"})
     * @param LinkRepository $linkRepository
     * @return Response
     */
    public function index(LinkRepository $linkRepository): Response
    {
        return $this->render('link/index.html.twig', [
            'links' => $linkRepository->findAll(),
        ]);
    }

    /**
     * @Route("/link/crawl", name="link_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function crawl(Request $request): Response
    {
        $form = $this->createForm(TextType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->crawler->crawl($form->getData());

            return $this->redirectToRoute('link_index');
        }

        return $this->render('link/crawl.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/link/{id}", name="link_show", methods={"GET"})
     * @param Link $link
     * @return Response
     */
    public function show(Link $link): Response
    {
        return $this->render('link/show.html.twig', [
            'link' => $link,
        ]);
    }
}
