<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class WebSiteController extends AbstractController
{
    #[Route('/accueil', name: 'app_web_site')]
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
    public function details($id, EntityManagerInterface $entityManager): Response
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
    #[Route('/contact/send', name: 'contact_send', methods: ['POST'])]
    public function send(Request $request, MailerInterface $mailer): JsonResponse
    {
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $subject = $request->request->get('subject');
        $message = $request->request->get('message');

        // Validation basique
        if (!$name || !$email || !$message) {
            return new JsonResponse(['status' => 'error', 'message' => 'Tous les champs obligatoires doivent être remplis.'], 400);
        }

        // Créer l'email
        $emailMessage = (new Email())
            ->from('no_reply@crmj4r.fr')
            ->to('s.elmamouni@j4r.fr')  // Adresse où tu veux recevoir les messages
            ->subject($subject ?? 'Nouveau message de contact')
            ->text(
                "Vous avez reçu un nouveau message de $name ($email) :\n\n$message"
            );

        // Envoyer l'email
        try {
            $mailer->send($emailMessage);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Une erreur est survenue lors de l\'envoi de l\'email.'], 500);
        }

        // Répondre avec succès
        return new JsonResponse(['status' => 'success', 'message' => 'Votre message a bien été envoyé !']);
    }
}
