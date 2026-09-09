<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Pagination\Paginator;
use PHPUnit\Framework\TestCase;

class PaginatorTest extends TestCase
{
    public function testCalculsDePagination(): void
    {
        $items = ['A', 'B', 'C', 'D', 'E'];
        $paginator = new Paginator($items, 25, 2, 5);

        $this->assertSame(2, $paginator->getCurrentPage());
        $this->assertSame(5, $paginator->getPerPage());
        $this->assertSame(25, $paginator->getTotalItems());
        $this->assertSame(5, $paginator->getTotalPages());
        $this->assertTrue($paginator->hasPreviousPage());
        $this->assertTrue($paginator->hasNextPage());
        $this->assertSame(1, $paginator->getPreviousPage());
        $this->assertSame(3, $paginator->getNextPage());
        $this->assertSame(6, $paginator->getFromIndex());
        $this->assertSame(10, $paginator->getToIndex());
    }

    public function testPremiereEtDernierePages(): void
    {
        $premierePage = new Paginator(['A'], 10, 1, 5);
        $this->assertFalse($premierePage->hasPreviousPage());
        $this->assertTrue($premierePage->hasNextPage());

        $dernierePage = new Paginator(['A'], 10, 2, 5);
        $this->assertTrue($dernierePage->hasPreviousPage());
        $this->assertFalse($dernierePage->hasNextPage());
    }

    public function testUrlForPageConserveParametresGet(): void
    {
        $_GET = ['nom' => 'Amphi', 'type' => 'cours'];

        $paginator = new Paginator(['A'], 30, 1, 10);
        $url = $paginator->urlForPage(2);

        $this->assertStringContainsString('page=2', $url);
        $this->assertStringContainsString('nom=Amphi', $url);
        $this->assertStringContainsString('type=cours', $url);
    }
}
