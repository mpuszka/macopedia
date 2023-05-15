<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $repository = $entityManager->getRepository(Product::class);
        $pages = ceil($repository->getCountOfAll() / 10);
        $pageParam = $request->query->get('page');

        $page = (! $pageParam || 0 === $pageParam || $pageParam > $pages) ? 1 : $pageParam;

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $repository->getAllPosts($page),
            'page' => $page,
            'pages' => ceil($repository->getCountOfAll() / 10),
        ]);
    }
}