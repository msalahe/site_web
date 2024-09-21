<?php

namespace App\Controller;

use App\Entity\Type;
use App\Repository\TypeRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;

#[Route('/type')]
class TypeController extends AbstractController
{

    public function __construct(private TypeRepository $typeRepository) {}
    #[Route('/', name: 'app_type')]
    public function index(): Response
    {
        return $this->render('type/index.html.twig', [
            'controller_name' => 'TypeController',
            'types' => $this->typeRepository->findAllTypes(),
            'title_page' => "TYPE DU PROJET"
        ]);
    }

    #[Route('/new/type', name: 'app_type_projet_new', methods: ['GET', 'POST'])]
    public function getModalTypeNew(Request $request, Environment $environment): Response
    {

        $response = new Response();
        try {
            $html = $environment->render("type/type_projet.html.twig",);
        } catch (LoaderError $e) {
            dd($e);
        } catch (RuntimeError $e) {
            dd($e);
        } catch (SyntaxError $e) {
            dd($e);
        }
        $response->setContent(json_encode(['code' => 200, 'message' => $html]));
        return $response;
    }
    #[Route('/new/edit/{idType}', name: 'app_type_projet_edit', methods: ['GET', 'POST'])]
    public function getModalTypeEdit(int $idType, Request $request, Environment $environment): Response
    {
        // Fetch the Type entity using the idType
        $type = $this->typeRepository->find($idType);


        // If no type is found, handle the error (e.g., throw a 404 or return a default response)
        if (!$type) {
            throw $this->createNotFoundException('The type with id ' . $idType . ' does not exist.');
        }

        $response = new Response();

        try {
            // Pass the type entity to the Twig template
            $html = $environment->render("type/edit_type_projet.html.twig", [
                'type' => $type, // Passing the retrieved type object to the template
            ]);
        } catch (LoaderError $e) {
            dd($e);
        } catch (RuntimeError $e) {
            dd($e);
        } catch (SyntaxError $e) {
            dd($e);
        }

        $response->setContent(json_encode(['code' => 200, 'message' => $html]));
        return $response;
    }

    #[Route('/new/type/projet', name: 'app_typeprojet_new', methods: ['GET', 'POST'])]
    public function addType(Request $request): Response
    {

        $data = $request->request->all()['service'];
        $response = new Response();

        if (!empty($data['type_projet'])) {

            $type = new Type();
            $type->setNameType($data['type_projet']);



            try {
                $res = $this->typeRepository->save($type);
                $response->setContent(json_encode(['code' => 200, 'message' => ['id' =>   $res, 'titre' => $data['type_projet']]]));
            } catch (UniqueConstraintViolationException $e) {
                $response->setContent(json_encode(['code' => 404, 'message' => "Une activité avec le même titre existe dans la base de données"]));
            }
        } else {
            $response->setContent(json_encode(['code' => 404, 'message' => 'Veuillez remplir tous les champs du formulaire']));
        }
        return $response;
    }

    #[Route('/update/type/projet', name: 'app_typeprojet_edit', methods: ['GET', 'POST'])]
    public function edit_type(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $data = $request->request->all()['service'];
        $response = new Response();



        if (!empty($data['type_projet']) && !empty($data['idType'])) {
            $typeProjet = $this->typeRepository->find($data['idType']);

            if (!$typeProjet) {
                return $response->setContent(content: json_encode(['code' => 404, 'message' => 'Type de projet introuvable']));
            }
            $typeProjet->setNameType($data['type_projet']);

            // Persist and flush the changes



            try {
                $entityManager->persist($typeProjet);
                $entityManager->flush();
                return $response->setContent(json_encode(['code' => 200, 'message' => "Type de projet mis à jour avec succès"]));
            } catch (UniqueConstraintViolationException $e) {
                $response->setContent(json_encode(['code' => 404, 'message' => "Une activité avec le même titre existe dans la base de données"]));
            }
        } else {
            $response->setContent(content: json_encode(['code' => 404, 'message' => 'Veuillez remplir tous les champs du formulaire']));
        }
        return $response;
    }

    #[Route('/delete/{id}', name: 'annuler_type', methods: ['GET', 'POST'])]
    public function deleteType(
        $id,
        TypeRepository $typeRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            // Retrieve the Type entity by ID
            $type = $typeRepository->find($id);

            if (!$type) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Type non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            // Remove the entity from the database
            $entityManager->remove($type);
            $entityManager->flush();

            // Return success response
            return new JsonResponse([
                'success' => true,
                'message' => 'Type supprimé avec succès'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors and return failure response
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
