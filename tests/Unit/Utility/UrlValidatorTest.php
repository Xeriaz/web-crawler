<?php

namespace App\Tests\Unit\Utility;

use App\Utility\UrlValidator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ValidatorBuilder;

class UrlValidatorTest extends WebTestCase
{
    /**
     * @dataProvider getUrls
     * @param string $url
     * @param bool $expected
     */
    public function testIsValid(string $url, bool $expected): void
    {
        $builder = new ValidatorBuilder();
        $validator = $builder->getValidator();

//        $validator = $this->createMock(RecursiveValidator::class);
//
//        $constraintViolation = $this->getMockBuilder(ConstraintViolationList::class)
//            ->setMethods(['add'])
//            ->getMock();
//
//        $validator->expects(self::once())
//            ->method('validate')
//            ->willReturn($constraintViolation);


        $isValid = (new UrlValidator($validator))->isValid($url);

        self::assertEquals($expected, $isValid);
    }

    /**
     * @return string[]
     */
    public function getUrls(): array
    {
        return [
            ['mailto:info@nfq.lt', false],
            ['https://info@nfq.lt', true],
        ];
    }
}
