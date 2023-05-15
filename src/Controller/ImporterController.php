<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ImporterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface as Logger;

class ImporterController extends AbstractController
{
    #[Route('/importer', name: 'app_importer')]
    public function index(Request $request, SluggerInterface $slugger, Logger $logger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

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
                    $this->addFlash(
                        'success',
                        'File has been importer!'
                    );
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        'Ops! Something went wrong!'
                    );
                    $logger->error('Importer: Error occured during the importing the file. Error message: ' . $e->getMessage());
                }

                return $this->redirectToRoute('app_importer');
            }
        }

        return $this->render('importer/index.html.twig', [
            'controller_name' => 'ImporterController',
            'form' => $form,
        ]);
    }
}
