<?php

namespace App\Controller;

use App\DTO\ApiResponse;
use App\Entity\Projet;
use App\Form\ProjetType;
use App\Service\ImageService;
use App\Service\ProjetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProjetController extends AbstractController
{
    public function __construct(
        private ProjetService $projetService,
        private ImageService $imageService
    ) {}

    #[Route('/projets', name: 'app_projet', methods: ['GET'])]
    public function index(): Response
    {
        $projets = $this->projetService->getAllProjets();

        // Debug: vérifier si les projets ont des images
        foreach ($projets as $projet) {
            if ($projet->getImages()->count() > 0) {
                error_log("Projet {$projet->getId()} a {$projet->getImages()->count()} image(s)");
            }
        }

        return $this->render('projet/index.html.twig', [
            'controller_name' => 'ProjetController',
            'projets' => $projets,
            'title_page' => "LISTER DES PROJET"
        ]);
    }

    #[Route('/projet/new', name: 'app_projet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Persist le projet d'abord
                $entityManager->persist($projet);
                $entityManager->flush();

                error_log("Projet créé avec ID: " . $projet->getId());

                // Gérer les images depuis le formulaire
                $images = $form->get('images')->getData();

                error_log("Nombre d'images reçues: " . (is_array($images) ? count($images) : ($images ? 1 : 0)));

                if ($images) {
                    if (!is_array($images)) {
                        $images = [$images];
                    }

                    error_log("Upload de " . count($images) . " image(s)...");
                    $uploadedImages = $this->imageService->uploadMultipleImages($images, $projet);
                    error_log("Images uploadées: " . count($uploadedImages));
                }

                $this->addFlash('success', 'Projet créé avec succès');
                return $this->redirectToRoute('app_projet');
            } catch (\Exception $e) {
                error_log("Erreur lors de la création: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $this->addFlash('error', 'Erreur lors de la création du projet: ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            // Si le formulaire est soumis mais non valide, afficher les erreurs
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            if (!empty($errors)) {
                $this->addFlash('error', 'Erreurs de validation: ' . implode(', ', $errors));
            }
        }

        return $this->render('projet/new.html.twig', [
            'form' => $form->createView(),
            'title_page' => "CREER UN PROJET"
        ]);
    }

    #[Route('/projet/{id}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $projet = $this->projetService->getProjetById($id);

        if (!$projet) {
            throw $this->createNotFoundException('Le projet demandé n\'existe pas.');
        }

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Sauvegarder les modifications du projet
                $entityManager->flush();

                // Gérer les nouvelles images depuis le formulaire
                $images = $form->get('images')->getData();

                if ($images) {
                    $this->imageService->uploadMultipleImages($images, $projet);
                }

                $this->addFlash('success', 'Projet modifié avec succès');
                return $this->redirectToRoute('app_projet');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification du projet: ' . $e->getMessage());
            }
        }

        return $this->render('projet/edit.html.twig', [
            'form' => $form->createView(),
            'title_page' => "Éditer Projet",
            'projet' => $projet,
        ]);
    }

    #[Route('/delete/projet/{id}', name: 'app_projet_delete', methods: ['POST', 'DELETE'])]
    public function delete(int $id): Response
    {
        try {
            $deleted = $this->projetService->deleteProjet($id);

            if (!$deleted) {
                return ApiResponse::error('Projet introuvable', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, 'Projet supprimé avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Erreur lors de la suppression du projet: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/delete/image/{id}', name: 'app_image_delete', methods: ['POST', 'DELETE'])]
    public function deleteImage(int $id): Response
    {
        try {
            $deleted = $this->imageService->deleteImage($id);

            if (!$deleted) {
                return ApiResponse::error('Image introuvable', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, 'Image supprimée avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Erreur lors de la suppression de l\'image: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/projets', name: 'app_projets_list', methods: ['GET'])]
    public function listAll(): Response
    {
        try {
            $projetsByType = $this->projetService->getProjetsByType();

            return $this->render('projet/list_all.html.twig', [
                'projetsByType' => $projetsByType,
                'title_page' => "Tous les Projets"
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la récupération des projets: ' . $e->getMessage());
            return $this->redirectToRoute('app_projet');
        }
    }
}
