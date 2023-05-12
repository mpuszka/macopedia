<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ImporterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImporterController extends AbstractController
{
    #[Route('/importer', name: 'app_importer')]
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ImporterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $importFile = $form->get('importFile')->getData();

            if ($importFile) {
                $safeFilename = $slugger->slug(pathinfo($importFile->getClientOriginalName(), PATHINFO_FILENAME));
                try {
                    $importFile->move(
                        $this->getParameter('csv_importer_directory'),
                        $safeFilename . '-' . uniqid() . '.' . $importFile->guessExtension()
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }
        }

        return $this->render('importer/index.html.twig', [
            'controller_name' => 'ImporterController',
            'form' => $form,
        ]);
    }
}
