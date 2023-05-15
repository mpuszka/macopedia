<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProductType;

class ProductController extends AbstractController
{
    #[Route('/product/edit/{id}', name: 'app_product_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (! $product) {
            $this->addFlash(
                'danger',
                'Product not found!'
            );

            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Product has been edited!'
            );

            return $this->redirectToRoute('app_home');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/product/delete/{id}', name: 'app_product_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $repository = $entityManager->getRepository(Product::class);

        $product = $repository->find($id);
        if ($product) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_home');
    }
}
