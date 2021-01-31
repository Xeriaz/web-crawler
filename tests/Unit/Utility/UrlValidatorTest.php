<?php

namespace App\Tests\Unit\Utility;

use App\Utility\UrlValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Context\ExecutionContext;

class UrlValidatorTest extends WebTestCase
{
    public function testIsValid(): void
    {
        $context = $this->createMock(ExecutionContext::class);
        //buildViolation
        $validator = new UrlValidator($context);

        self::assertFalse($validator->isValid('mailto:info@nfq.lt'));
        self::assertTrue($validator->isValid('https://info@nfq.lt'));
    }
}
