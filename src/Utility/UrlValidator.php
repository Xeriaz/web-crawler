<?php declare(strict_types=1);

namespace App\Utility;

use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UrlValidator implements UrlValidatorInterface
{
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function isValid(string $url): bool
    {
        $errors = $this->validator->validate($url, new Url());

        if ($errors->count() > 0) {
            return false;
        }

        return true;
    }
}