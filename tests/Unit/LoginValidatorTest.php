<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Validation\LoginValidator;
use PHPUnit\Framework\TestCase;

class LoginValidatorTest extends TestCase
{
    private LoginValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new LoginValidator();
    }

    public function testValidationReussieAvecIdentifiantsValides(): void
    {
        $data = [
            'email'    => 'adja@univ.sn',
            'password' => 'passer123',
        ];

        $result = $this->validator->validate($data);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->errors());
        $this->assertSame('adja@univ.sn', $result->validatedData()['email']);
    }

    public function testValidationEchoueSiEmailInvalide(): void
    {
        $data = [
            'email'    => 'invalid-email',
            'password' => 'secret123',
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($result->hasError('email'));
    }

    public function testValidationEchoueSiMotDePasseVide(): void
    {
        $data = [
            'email'    => 'test@univ.sn',
            'password' => '',
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($result->hasError('password'));
    }

    public function testEmailEstNormaliseEnMinuscules(): void
    {
        $data = [
            'email'    => '  ADJA.NDOUR@UNIV.SN  ',
            'password' => 'monmotdepasse',
        ];

        $result = $this->validator->validate($data);

        $this->assertTrue($result->isValid());
        $this->assertSame('adja.ndour@univ.sn', $result->validatedData()['email']);
    }
}
