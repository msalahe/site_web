<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebSiteController extends AbstractController
{
    #[Route('/', name: 'app_web_site')]
    public function index(): Response
    {
        return $this->render('web_site/index.html.twig', [
            'controller_name' => 'WebSiteController',
            'title_page' => "TYPE DU PROJET"

        ]);
    }
    #[Route('/realisations', name: 'app_web_site_realisations')]
    public function realisations(EntityManagerInterface $entityManager): Response
    {

        $types = $entityManager->getRepository(Type::class)->findAll();

        $projetsByType = [];

        // Pour chaque type, récupérer les projets associés
        $projets = $entityManager->getRepository(Projet::class)->findAll();



        return $this->render('web_site/realisations.html.twig', [
            'controller_name' => 'WebSiteController',
            'projets' => $projets,
            'types' => $types

        ]);
    }

    #[Route('/details/{id}', name: 'app_web_site_details')]
    public function details($id,EntityManagerInterface $entityManager): Response
    {


        $projet = $entityManager->getRepository(Projet::class)->find($id);

        // Vérifier si le projet existe
        if (!$projet) {
            throw $this->createNotFoundException('Le projet demandé n\'existe pas.');
        }
    
        // Récupérer toutes les images associées au projet
        $images = $projet->getImages();
    
        // Renvoyer les données à la vue
        return $this->render('web_site/detail.html.twig', [
            'projet' => $projet,
            'images' => $images,
        ]);
    }


    #[Route('/J4R-ÉCHAFAUDAGES', name: 'app_web_site_about')]
    public function about(): Response
    {
        return $this->render('web_site/about.html.twig', [
            'controller_name' => 'WebSiteController',
        ]);
    }
    #[Route('/contact', name: 'app_web_site_contact')]
    public function contact(): Response
    {
        return $this->render('web_site/contact.html.twig', [
            'controller_name' => 'WebSiteController',
        ]);
    }
}
