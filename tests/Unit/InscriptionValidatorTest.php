<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Validation\InscriptionValidator;
use PHPUnit\Framework\TestCase;

class InscriptionValidatorTest extends TestCase
{
    private InscriptionValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new InscriptionValidator();
    }

    public function testValidationReussiePourInscriptionValide(): void
    {
        $data = [
            'nom'                   => 'Fatou Bintou',
            'email'                 => 'fatou@univ.sn',
            'password'              => 'azerty123',
            'password_confirmation' => 'azerty123',
        ];

        $result = $this->validator->validate($data);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->errors());
        $this->assertSame('Fatou Bintou', $result->validatedData()['nom']);
        $this->assertSame('fatou@univ.sn', $result->validatedData()['email']);
    }

    public function testValidationEchoueSiNomTropCourt(): void
    {
        $data = [
            'nom'                   => 'A',
            'email'                 => 'fatou@univ.sn',
            'password'              => 'azerty123',
            'password_confirmation' => 'azerty123',
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($result->hasError('nom'));
    }

    public function testValidationEchoueSiMotsDePasseDifferents(): void
    {
        $data = [
            'nom'                   => 'Fatou Bintou',
            'email'                 => 'fatou@univ.sn',
            'password'              => 'azerty123',
            'password_confirmation' => 'different123',
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($result->hasError('password_confirmation'));
    }

    public function testValidationEchoueSiMotDePasseTropCourt(): void
    {
        $data = [
            'nom'                   => 'Fatou Bintou',
            'email'                 => 'fatou@univ.sn',
            'password'              => '123',
            'password_confirmation' => '123',
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($result->hasError('password'));
    }
}
