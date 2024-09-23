<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Projet;
use App\Entity\Type;
use App\Form\ProjetType;
use App\Repository\ImageRepository;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProjetController extends AbstractController
{
    private string $photoDir;

    public function __construct(string $photoDir, private ProjetRepository $projetRepository,private ImageRepository $imageRepository)
    {
        $this->photoDir = $photoDir;
    }
    #[Route('/', name: 'app_projet')]
    public function index(): Response
    {
        return $this->render('projet/index.html.twig', [
            'controller_name' => 'ProjetController',
            'projets' => $this->projetRepository->findAll(),
            'title_page' => "LISTER DES PROJET"

        ]);
    }
    #[Route('/projet/new', name: 'app_projet_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Handle image uploads  
            try {

                $images = $request->files->get('projet')['images'];

                if ($images) {
                    foreach ($images as $image) {
                        if ($image instanceof UploadedFile) {
                            // Process each file (e.g., move it to a directory)
                            $destination = $this->getParameter('photo_dir');
                            $newFilename = uniqid() . '.' . $image->guessExtension();

                            // Move the file to the uploads directory
                            $image->move($destination, $newFilename);
                            $image = new Image();
                            $image->setFilename($newFilename);

                            // Associate the image with the project or save to the database
                            $projet->addImage($image);

                            // Save the file name in the database or perform other actions
                            $entityManager->persist($projet);
                            $entityManager->flush();
                        }
                    }
                }



                // Check that $imageFile is an instance of UploadedFile




                // Persist the project

            } catch (Exception $e) {
                dd($e->getMessage());
            }

            return $this->redirectToRoute(route: 'app_projet_new');
        }

        return $this->render('projet/new.html.twig', [
            'form' => $form->createView(),
            'title_page' => "CREER UN PROJET"

        ]);
    }
    #[Route('/delete/projet/{id}', name: 'app_projet_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {

        $projet = $this->projetRepository->find($id);

        if (!$projet) {
            return new JsonResponse(['success' => false, 'message' => 'Projet introuvable.'], 404);
        }

        // Récupérer les images associées au projet
        $images = $projet->getImages();

        // Supprimer les fichiers images du serveur
        foreach ($images as $image) {
            $filePath = $this->getParameter('photo_dir') . '/' . $image->getFilename();
            if (file_exists($filePath)) {
                unlink($filePath); // Supprimer le fichier physique
            }
            $entityManager->remove($image); // Supprimer l'entité Image de la base de données
        }

        // Supprimer le projet de la base de données
        $entityManager->remove($projet);
        $entityManager->flush(); // Exécuter la suppression en base

        // Rediriger vers la liste des projets ou une autre page
        return new JsonResponse([
            'success' => true,
            'message' => 'Projet supprimé avec succès'
        ], Response::HTTP_OK);
    }
    #[Route('/projets', name: 'app_projets_list')]
    public function listAll(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les types de projet distincts
        $types = $entityManager->getRepository(Type::class)->findAll();

        $projetsByType = [];

        // Pour chaque type, récupérer les projets associés
        foreach ($types as $type) {
            $projets = $entityManager->getRepository(Projet::class)->findBy(['type' => $type]);

            $projetsWithImages = [];

            foreach ($projets as $projet) {
                $image = null;

                // Si le projet a des images, on en sélectionne une (par exemple la première)
                if ($projet->getImages()->count() > 0) {
                    $image = $projet->getImages()->first()->getFilename();
                }

                $projetsWithImages[] = [
                    'name' => $projet->getName(),
                    'description' => $projet->getDescription(),
                    'image' => $image,  // Le nom de fichier de l'image
                ];
            }

            $projetsByType[$type->getNameType()] = $projetsWithImages;
        }
        dd($projetsByType);

        // Retourner la vue avec les projets groupés par type
        return $this->render('projet/list_all.html.twig', [
            'projetsByType' => $projetsByType,
            'title_page' => "Tous les Projets"
        ]);
    }

    #[Route('/projet/{id}/edit', name: 'app_projet_edit')]

    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Récupérer le projet par son ID
        $projet = $entityManager->getRepository(Projet::class)->find($id);

        if (!$projet) {
            throw $this->createNotFoundException('Le projet demandé n\'existe pas.');
        }
        // Création du formulaire
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Gestion des nouvelles images si elles sont ajoutées ou remplacées
            try {

                $projetData = $request->files->get('projet');

                if (isset($projetData['images']) && !empty($projetData['images'])) {
                    $images = $projetData['images'];


                    foreach ($images as $image) {
                        if ($image instanceof UploadedFile) {
                            // Chemin de destination pour le fichier
                            $destination = $this->getParameter('photo_dir');
                            $newFilename = uniqid() . '.' . $image->guessExtension();

                            // Déplacer le fichier vers le répertoire de destination
                            $image->move($destination, $newFilename);

                            // Créer une nouvelle entité Image et l'associer au projet
                            $imageEntity = new Image();
                            $imageEntity->setFilename($newFilename);
                            $projet->addImage($imageEntity);
                        }
                    }
                }

                // Si vous voulez supprimer des images existantes, vous pouvez ajouter ici une logique
                // pour détecter et supprimer les anciennes images non désirées

                // Enregistrer les modifications du projet et ses images
                $entityManager->persist($projet);
                $entityManager->flush();

                return $this->redirectToRoute('app_projet');
            } catch (Exception $e) {
                dd($e->getMessage());
            }
        }

        return $this->render('projet/edit.html.twig', [
            'form' => $form->createView(),
            'title_page' => "Éditer Projet",
            'projet' => $projet, // Passer le projet pour l'afficher dans le template
        ]);
    }
    #[Route('/delete/image/{id}', name: 'app_image_delete', methods: ['POST', 'DELETE'])]
    public function deleteImage(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer l'image par son ID
        $image = $this->imageRepository->find($id);

        if (!$image) {
            return new JsonResponse(['success' => false, 'message' => 'Image introuvable.'], 404);
        }

        // Récupérer le chemin du fichier image
        $filePath = $this->getParameter('photo_dir') . '/' . $image->getFilename();

        // Supprimer le fichier image du serveur
        if (file_exists($filePath)) {
            unlink($filePath); // Supprimer le fichier physique
        }

        // Supprimer l'entité Image de la base de données
        $entityManager->remove($image);
        $entityManager->flush(); // Appliquer les changements en base

        // Retourner une réponse JSON
        return new JsonResponse([
            'success' => true,
            'message' => 'Image supprimée avec succès'
        ], Response::HTTP_OK);
    }
}
