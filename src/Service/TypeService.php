<?php

namespace App\Service;

use App\Entity\Type;
use App\Repository\TypeRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class TypeService
{
    public function __construct(
        private TypeRepository $typeRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Récupère tous les types
     */
    public function getAllTypes(): array
    {
        return $this->typeRepository->findAllTypes();
    }

    /**
     * Récupère un type par son ID
     */
    public function getTypeById(int $id): ?Type
    {
        return $this->typeRepository->find($id);
    }

    /**
     * Crée un nouveau type
     *
     * @throws UniqueConstraintViolationException
     */
    public function createType(string $name): Type
    {
        $type = new Type();
        $type->setNameType($name);

        $this->entityManager->persist($type);
        $this->entityManager->flush();

        return $type;
    }

    /**
     * Met à jour un type existant
     *
     * @throws UniqueConstraintViolationException
     */
    public function updateType(int $id, string $name): ?Type
    {
        $type = $this->getTypeById($id);

        if (!$type) {
            return null;
        }

        $type->setNameType($name);
        $this->entityManager->flush();

        return $type;
    }

    /**
     * Supprime un type
     */
    public function deleteType(int $id): bool
    {
        $type = $this->getTypeById($id);

        if (!$type) {
            return false;
        }

        $this->entityManager->remove($type);
        $this->entityManager->flush();

        return true;
    }
}
