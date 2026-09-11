<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\View\ViewRenderer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ViewRendererTest extends TestCase
{
    private ViewRenderer $renderer;

    protected function setUp(): void
    {
        $_GET = [];
        $_SERVER['HTTP_ACCEPT'] = 'text/html';
        unset($_ENV['RESPONSE_FORMAT']);

        $this->renderer = new ViewRenderer();
    }

    protected function tearDown(): void
    {
        $_GET = [];
        unset($_SERVER['HTTP_ACCEPT']);
        unset($_ENV['RESPONSE_FORMAT']);
    }

    public function testRenderHtmlParDefaut(): void
    {
        ob_start();
        $this->renderer->render('error/404', [
            'titre'   => 'Test 404',
            'message' => 'Ressource introuvable pour le test',
        ], 404);
        $output = ob_get_clean();

        $this->assertSame(404, http_response_code());
        $this->assertStringContainsString('Test 404', $output);
        $this->assertStringContainsString('Ressource introuvable pour le test', $output);
    }

    public function testRenderJsonViaParametreGet(): void
    {
        $_GET['format'] = 'json';

        ob_start();
        $this->renderer->render('error/404', [
            'success' => false,
            'message' => 'Non trouvé',
        ], 404);
        $output = ob_get_clean();

        $this->assertSame(404, http_response_code());
        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['success']);
        $this->assertSame('Non trouvé', $decoded['message']);
    }

    public function testRenderJsonViaHeaderHttpAccept(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json, text/plain, */*';

        ob_start();
        $this->renderer->render('salle/index', [
            'salles' => [['id' => 1, 'nom' => 'Salle A']],
        ]);
        $output = ob_get_clean();

        $this->assertSame(200, http_response_code());
        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertSame('Salle A', $decoded['salles'][0]['nom']);
    }

    public function testRenderJsonViaVariableEnvironnement(): void
    {
        $_ENV['RESPONSE_FORMAT'] = 'json';

        ob_start();
        $this->renderer->render('salle/show', [
            'salle' => ['id' => 42, 'nom' => 'Amphi C'],
        ]);
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertSame(42, $decoded['salle']['id']);
    }

    public function testMethodeJsonDirecte(): void
    {
        ob_start();
        $this->renderer->json(['status' => 'success', 'data' => [1, 2, 3]], 201);
        $output = ob_get_clean();

        $this->assertSame(201, http_response_code());
        $decoded = json_decode($output, true);
        $this->assertSame('success', $decoded['status']);
        $this->assertSame([1, 2, 3], $decoded['data']);
    }

    public function testExceptionSiVueIntrouvableEnModeHtml(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fichier de vue introuvable');

        $this->renderer->render('vue_inexistante_xyz');
    }
}
