<?php

namespace App\Traits;

use Livewire\Attributes\Url;
use Livewire\WithPagination;

trait HasPagination
{
    use WithPagination;

    #[Url(except: 15)]
    public int $perPage = 15;

    public int $totalRecords = 0;

    public function getCurrentPage(): int
    {
        return $this->getPage();
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }
}
