<?php

namespace App\Service;

final class Phrases
{
    public function __construct(
        private string $sentence
    ) {}
    
    public function extract(): array
    {
        $sentence = trim(preg_replace('!\s+!', ' ', $this->sentence));
        
        if (! empty($sentence)) {
            return explode(' ', $sentence);
        }
        
        return [];
    }
}