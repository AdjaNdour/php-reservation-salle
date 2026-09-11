<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controller\Controller;
use App\View\FormatInterface;
use App\View\HtmlFormat;
use App\View\JsonFormat;
use App\View\ViewRenderer;
use PHPUnit\Framework\TestCase;

class ViewFormatTest extends TestCase
{
    private string $tempTemplatesDir;

    protected function setUp(): void
    {
        $_GET = [];
        unset($_SERVER['HTTP_ACCEPT']);

        // Créer un dossier temporaire pour les vues de test
        $this->tempTemplatesDir = sys_get_temp_dir() . '/test_templates_' . uniqid();
        mkdir($this->tempTemplatesDir, 0777, true);
        file_put_contents($this->tempTemplatesDir . '/test_view.php', '<h1><?= $titre ?></h1><p><?= $message ?></p>');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempTemplatesDir . '/test_view.php')) {
            unlink($this->tempTemplatesDir . '/test_view.php');
        }
        if (is_dir($this->tempTemplatesDir)) {
            rmdir($this->tempTemplatesDir);
        }
        $_GET = [];
        unset($_SERVER['HTTP_ACCEPT']);
    }

    public function testHtmlFormatImplementeInterface(): void
    {
        $format = new HtmlFormat($this->tempTemplatesDir);
        $this->assertInstanceOf(FormatInterface::class, $format);

        $result = $format->response('test_view', ['titre' => 'Mon Titre', 'message' => 'Bienvenue']);
        $this->assertStringContainsString('<h1>Mon Titre</h1>', $result);
        $this->assertStringContainsString('<p>Bienvenue</p>', $result);
    }

    public function testJsonFormatImplementeInterface(): void
    {
        $format = new JsonFormat();
        $this->assertInstanceOf(FormatInterface::class, $format);

        $data = ['titre' => 'Liste des salles', 'salles' => [['id' => 1, 'nom' => 'Salle A']]];
        $result = $format->response('salle/index', $data, 200);

        $this->assertJson($result);
        $decoded = json_decode($result, true);

        $this->assertSame('Liste des salles', $decoded['titre']);
        $this->assertSame([['id' => 1, 'nom' => 'Salle A']], $decoded['salles']);
        $this->assertSame(200, http_response_code());
    }

    public function testControllerPeutSwitcherEnJson(): void
    {
        $renderer = new ViewRenderer($this->tempTemplatesDir);
        $testController = new class($renderer) extends Controller {
            public function testAction(): void
            {
                $this->render('test_view', ['titre' => 'Mon Titre', 'items' => [1, 2, 3]], 200, 'json');
            }
        };

        ob_start();
        $testController->testAction();
        $result = ob_get_clean();

        $this->assertJson($result);

        $decoded = json_decode($result, true);
        $this->assertSame('Mon Titre', $decoded['titre']);
        $this->assertSame([1, 2, 3], $decoded['items']);
    }

    public function testControllerPeutSwitcherViaParametreGet(): void
    {
        $_GET['format'] = 'json';

        $renderer = new ViewRenderer($this->tempTemplatesDir);
        $testController = new class($renderer) extends Controller {
            public function testAction(): void
            {
                $this->render('test_view', ['titre' => 'Salles', 'count' => 5]);
            }
        };

        ob_start();
        $testController->testAction();
        $result = ob_get_clean();

        $this->assertJson($result);

        $decoded = json_decode($result, true);
        $this->assertSame('Salles', $decoded['titre']);
        $this->assertSame(5, $decoded['count']);
    }

    public function testConfigViewRetourneFormat(): void
    {
        $config = require dirname(__DIR__, 2) . '/config/view.php';
        $this->assertIsArray($config);
        $this->assertArrayHasKey('format', $config);
    }
}
