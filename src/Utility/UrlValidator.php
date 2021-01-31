<?php declare(strict_types=1);

namespace App\Utility;

use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UrlValidator implements UrlValidatorInterface
{
    private ExecutionContextInterface $context;

    public function __construct(ExecutionContextInterface $context)
    {
        $this->context = $context;
    }

    public function isValid(string $url): bool
    {
        $validator = new \Symfony\Component\Validator\Constraints\UrlValidator();
        $validator->initialize($this->context);


        dd($validator->validate($url, new Url()));
        return false;
    }
}