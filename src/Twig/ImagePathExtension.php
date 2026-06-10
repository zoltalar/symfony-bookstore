<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImagePathExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('image_path', [$this, 'getImagePath']),
        ];
    }
    
    public function getImagePath(string $fileName): string
    {
        return '/uploads/books/' . $fileName;
    }
}