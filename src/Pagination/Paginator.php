<?php

declare(strict_types=1);

namespace App\Pagination;

use JsonSerializable;

/**
 * @template T
 */
final class Paginator implements JsonSerializable
{
    private int $totalPages;

    /**
     * @param array<T> $items
     */
    public function __construct(
        private array $items,
        private int $totalItems,
        private int $currentPage = 1,
        private int $perPage = 10
    ) {
        $this->currentPage = max(1, $this->currentPage);
        $this->perPage = max(1, $this->perPage);
        $this->totalPages = max(1, (int) ceil($this->totalItems / $this->perPage));
        if ($this->currentPage > $this->totalPages && $this->totalItems > 0) {
            $this->currentPage = $this->totalPages;
        }
    }

    /**
     * @return array<T>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function getNextPage(): int
    {
        return min($this->totalPages, $this->currentPage + 1);
    }

    public function getFromIndex(): int
    {
        if ($this->totalItems === 0) {
            return 0;
        }
        return ($this->currentPage - 1) * $this->perPage + 1;
    }

    public function getToIndex(): int
    {
        return min($this->totalItems, $this->currentPage * $this->perPage);
    }

    /**
     * Génère l'URL avec conservation des filtres GET existants
     */
    public function urlForPage(int $page, array $extraParams = []): string
    {
        $params = array_merge($_GET ?? [], $extraParams, ['page' => $page]);
        return '?' . http_build_query($params);
    }

    public function jsonSerialize(): array
    {
        return [
            'items'         => $this->items,
            'total_items'   => $this->totalItems,
            'current_page'  => $this->currentPage,
            'per_page'      => $this->perPage,
            'total_pages'   => $this->totalPages,
            'has_next'      => $this->hasNextPage(),
            'has_previous'  => $this->hasPreviousPage(),
        ];
    }
}
