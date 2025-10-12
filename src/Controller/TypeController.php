<?php

namespace App\Controller;

use App\DTO\ApiResponse;
use App\Service\TypeService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route('/type')]
class TypeController extends AbstractController
{
    public function __construct(
        private TypeService $typeService,
        private Environment $twig
    ) {}

    #[Route('/types', name: 'app_type')]
    public function index(): Response
    {
        return $this->render('type/index.html.twig', [
            'controller_name' => 'TypeController',
            'types' => $this->typeService->getAllTypes(),
            'title_page' => "TYPE DU PROJET"
        ]);
    }

    #[Route('/new/type', name: 'app_type_projet_new')]
    public function getModalTypeNew(): Response
    {
        try {
            $html = $this->twig->render("type/type_projet.html.twig");
            return ApiResponse::html($html);
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors du chargement du formulaire', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/new/edit/{idType}', name: 'app_type_projet_edit')]
    public function getModalTypeEdit(int $idType): Response
    {
        try {
            $type = $this->typeService->getTypeById($idType);

            if (!$type) {
                return ApiResponse::error(
                    "Le type avec l'id {$idType} n'existe pas",
                    Response::HTTP_NOT_FOUND
                );
            }

            $html = $this->twig->render("type/edit_type_projet.html.twig", [
                'type' => $type,
            ]);

            return ApiResponse::html($html);
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors du chargement du formulaire', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/new/type/projet', name: 'app_typeprojet_new', methods: ['GET', 'POST'])]
    public function addType(Request $request): Response
    {
        $data = $request->request->all()['service'] ?? [];

        if (empty($data['type_projet'])) {
            return ApiResponse::error('Veuillez remplir tous les champs du formulaire');
        }

        try {
            $type = $this->typeService->createType($data['type_projet']);

            return ApiResponse::success(
                [
                    'id' => $type->getId(),
                    'titre' => $type->getNameType()
                ],
                'Type créé avec succès'
            );
        } catch (UniqueConstraintViolationException $e) {
            return ApiResponse::error(
                'Une activité avec le même titre existe dans la base de données',
                Response::HTTP_CONFLICT
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Erreur lors de la création du type',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/update/type/projet', name: 'app_typeprojet_edit', methods: ['GET', 'POST'])]
    public function editType(Request $request): Response
    {
        $data = $request->request->all()['service'] ?? [];

        if (empty($data['type_projet']) || empty($data['idType'])) {
            return ApiResponse::error('Veuillez remplir tous les champs du formulaire');
        }

        try {
            $type = $this->typeService->updateType(
                (int) $data['idType'],
                $data['type_projet']
            );

            if (!$type) {
                return ApiResponse::error('Type de projet introuvable', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, 'Type de projet mis à jour avec succès');
        } catch (UniqueConstraintViolationException $e) {
            return ApiResponse::error(
                'Une activité avec le même titre existe dans la base de données',
                Response::HTTP_CONFLICT
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Erreur lors de la mise à jour du type',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/delete/{id}', name: 'annuler_type', methods: ['POST', 'DELETE'])]
    public function deleteType(int $id): Response
    {
        try {
            $deleted = $this->typeService->deleteType($id);

            if (!$deleted) {
                return ApiResponse::error('Type non trouvé', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, 'Type supprimé avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Erreur lors de la suppression du type: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
