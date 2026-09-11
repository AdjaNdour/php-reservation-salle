<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Session\SessionManager;
use PHPUnit\Framework\TestCase;

class SessionManagerTest extends TestCase
{
    private SessionManager $sessionManager;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->sessionManager = new SessionManager();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testSaveDataEtGetData(): void
    {
        $this->sessionManager->saveData('user_id', 42);
        $this->assertSame(42, $this->sessionManager->getData('user_id'));
    }

    public function testGetDataValeurParDefaut(): void
    {
        $this->assertNull($this->sessionManager->getData('cle_inexistante'));
        $this->assertSame('defaut', $this->sessionManager->getData('cle_inexistante', 'defaut'));
    }

    public function testHas(): void
    {
        $this->assertFalse($this->sessionManager->has('token'));

        $this->sessionManager->saveData('token', 'xyz123');
        $this->assertTrue($this->sessionManager->has('token'));
    }

    public function testRemoveData(): void
    {
        $this->sessionManager->saveData('flash', 'bienvenue');
        $this->assertTrue($this->sessionManager->has('flash'));

        $this->sessionManager->removeData('flash');
        $this->assertFalse($this->sessionManager->has('flash'));
        $this->assertNull($this->sessionManager->getData('flash'));
    }

    public function testDestroySession(): void
    {
        $this->sessionManager->saveData('cle1', 'val1');
        $this->sessionManager->saveData('cle2', 'val2');

        $this->sessionManager->destroySession();
        $this->assertEmpty($_SESSION);
        $this->assertFalse($this->sessionManager->has('cle1'));
    }

    public function testCompatibiliteCasseDesMethodes(): void
    {
        // Test des variantes de casse spécifiées par l'utilisateur
        $this->sessionManager->savedata('role', 'admin');
        $this->assertSame('admin', $this->sessionManager->getdata('role'));

        $this->sessionManager->DestroySession();
        $this->assertEmpty($_SESSION);
    }
}
