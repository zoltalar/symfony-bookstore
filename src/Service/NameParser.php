<?php

namespace App\Service;

final class NameParser
{
    public function __construct(
        private string $name
    ) {}
    
    public function parse(): array
    {
        $name = trim($this->name);
        
        $lastName = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $firstName = trim(preg_replace('#' . $lastName . '#', '', $name));
        
        return [
            $firstName,
            $lastName
        ];
    }
}