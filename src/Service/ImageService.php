<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\Projet;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageService
{
    public function __construct(
        private ImageRepository $imageRepository,
        private EntityManagerInterface $entityManager,
        private string $photoDir
    ) {}

    /**
     * Upload et sauvegarde une image pour un projet
     */
    public function uploadImage(UploadedFile $file, Projet $projet): Image
    {
        $newFilename = uniqid() . '.' . $file->guessExtension();
        $file->move($this->photoDir, $newFilename);

        $image = new Image();
        $image->setFilename($newFilename);
        $image->setProjet($projet);

        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return $image;
    }

    /**
     * Upload plusieurs images pour un projet
     *
     * @param UploadedFile[] $files
     * @return Image[]
     */
    public function uploadMultipleImages(array $files, Projet $projet): array
    {
        $images = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $images[] = $this->uploadImage($file, $projet);
            }
        }

        return $images;
    }

    /**
     * Supprime une image
     */
    public function deleteImage(int $id): bool
    {
        $image = $this->imageRepository->find($id);

        if (!$image) {
            return false;
        }

        // Supprimer le fichier physique
        $filePath = $this->photoDir . '/' . $image->getFilename();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->entityManager->remove($image);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Supprime toutes les images d'un projet
     */
    public function deleteProjectImages(Projet $projet): void
    {
        foreach ($projet->getImages() as $image) {
            $filePath = $this->photoDir . '/' . $image->getFilename();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->entityManager->remove($image);
        }

        $this->entityManager->flush();
    }
}
