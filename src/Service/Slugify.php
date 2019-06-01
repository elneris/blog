<?php

namespace App\Service;

class Slugify
{
    public function generate(string $input) : string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $input); // Remplace accent
        $slug = mb_strtolower(preg_replace( '/[^a-zA-Z0-9\-\s]/', '', $slug ));
        $slug = str_replace(' ','-',trim($slug));
        $slug = preg_replace('/([-])\\1+/', '$1', $slug); // enlever les -- succesif

        return $slug;
    }
}