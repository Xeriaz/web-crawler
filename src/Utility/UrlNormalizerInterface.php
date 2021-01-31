<?php

namespace App\Utility;

interface UrlNormalizerInterface
{
    public function normalize(string $url): string;
}