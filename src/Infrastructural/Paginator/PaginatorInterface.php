<?php

namespace App\Infrastructural\Paginator;

use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface for pagination allowing the link with the external library.
 */
interface PaginatorInterface
{
    public function allowSort(string ...$fields): self;

    public function paginate(Query $query): PaginationInterface;
}
