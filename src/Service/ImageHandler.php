<?php

namespace App\Service;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ImageHandler
{
    public function __construct(
        private string $uploadDirectory,
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager
    ) {}
    
    public function delete(Image $image)
    {
        $filePath = $this->getUploadDirectory() . '/' . $image->getFile();
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }
    
    public function upload(UploadedFile $file): array
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = $this->slugger->slug($originalFilename) . '-' . uniqid() . '.' . $file->guessExtension();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        
        try {
            $file->move($this->getUploadDirectory(), $fileName);
            
            return [
                'fileName' => $fileName,
                'originalName' => $file->getClientOriginalName(),
                'fileSize' => $fileSize,
                'mimeType' => $mimeType
            ];
        } catch (Exception $e) {
            throw new Exception('Could not upload file: ' . $e->getMessage());
        }
    }
    
    public function getUploadDirectory(): string
    {
        return $this->uploadDirectory;
    }
}
