<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\Type;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjetService
{
    public function __construct(
        private ProjetRepository $projetRepository,
        private EntityManagerInterface $entityManager,
        private ImageService $imageService
    ) {}

    /**
     * Récupère tous les projets
     */
    public function getAllProjets(): array
    {
        return $this->projetRepository->findAll();
    }

    /**
     * Récupère un projet par son ID
     */
    public function getProjetById(int $id): ?Projet
    {
        return $this->projetRepository->find($id);
    }

    /**
     * Récupère les projets groupés par type
     */
    public function getProjetsByType(): array
    {
        $types = $this->entityManager->getRepository(Type::class)->findAll();
        $projetsByType = [];

        foreach ($types as $type) {
            $projets = $this->projetRepository->findBy(['type' => $type]);
            $projetsWithImages = [];

            foreach ($projets as $projet) {
                $image = null;

                if ($projet->getImages()->count() > 0) {
                    $image = $projet->getImages()->first()->getFilename();
                }

                $projetsWithImages[] = [
                    'id' => $projet->getId(),
                    'name' => $projet->getName(),
                    'description' => $projet->getDescription(),
                    'image' => $image,
                ];
            }

            $projetsByType[$type->getNameType()] = $projetsWithImages;
        }

        return $projetsByType;
    }

    /**
     * Crée un nouveau projet
     *
     * @param UploadedFile[]|null $images
     */
    public function createProjet(string $name, ?string $description, Type $type, ?array $images = null): Projet
    {
        $projet = new Projet();
        $projet->setName($name);
        $projet->setDescription($description);
        $projet->setType($type);

        $this->entityManager->persist($projet);
        $this->entityManager->flush();

        // Upload des images si présentes
        if ($images) {
            $this->imageService->uploadMultipleImages($images, $projet);
        }

        return $projet;
    }

    /**
     * Met à jour un projet existant
     *
     * @param UploadedFile[]|null $newImages
     */
    public function updateProjet(
        int $id,
        string $name,
        ?string $description,
        Type $type,
        ?array $newImages = null
    ): ?Projet {
        $projet = $this->getProjetById($id);

        if (!$projet) {
            return null;
        }

        $projet->setName($name);
        $projet->setDescription($description);
        $projet->setType($type);

        $this->entityManager->flush();

        // Upload des nouvelles images si présentes
        if ($newImages) {
            $this->imageService->uploadMultipleImages($newImages, $projet);
        }

        return $projet;
    }

    /**
     * Supprime un projet et ses images
     */
    public function deleteProjet(int $id): bool
    {
        $projet = $this->getProjetById($id);

        if (!$projet) {
            return false;
        }

        // Supprimer toutes les images associées
        $this->imageService->deleteProjectImages($projet);

        // Supprimer le projet
        $this->entityManager->remove($projet);
        $this->entityManager->flush();

        return true;
    }
}
