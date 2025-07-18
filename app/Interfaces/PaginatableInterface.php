<?php

namespace App\Interfaces;

interface PaginatableInterface
{
    public function getPerPage(): int;

    public function getCurrentPage(): int;
}
