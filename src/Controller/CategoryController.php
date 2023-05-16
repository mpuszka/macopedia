<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CategoryType;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $repository = $entityManager->getRepository(Category::class);

        return $this->render('category/index.html.twig', [
            'categories' => $repository->findAll(),
        ]);
    }

    #[Route('/category/add', name: 'app_category_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'The category has been added!'
            );

            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/add.edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/category/edit/{id}', name: 'app_category_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $category = $entityManager->getRepository(Category::class)->find($id);

        if (! $category) {
            $this->addFlash(
                'danger',
                'Category not found!'
            );

            return $this->redirectToRoute('app_category');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'The category has been edited!'
            );

            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/add.edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/category/delete/{id}', name: 'app_category_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $repository = $entityManager->getRepository(Category::class);

        $category = $repository->find($id);
        if ($category) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_category');
    }
}
