<?php

namespace App\Utility;

interface UrlValidatorInterface
{
    public function isValid(string $url): bool;
}