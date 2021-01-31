<?php declare(strict_types=1);

namespace App\Utility;

class UrlNormalizer implements UrlNormalizerInterface
{
    public function normalize(string $url): string
    {
        $url = explode('?', $url)[0];
        $url = explode('#', $url)[0];

        if (substr($url, -1) === '/') {
            return substr($url, 0, -1);
        }

        return $url;
    }
}