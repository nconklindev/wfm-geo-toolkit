<?php

namespace App\Livewire\Tools\HarAnalyzer\Tabs;

use App\Traits\FormatsHarData;
use Livewire\Component;

class Domains extends Component
{
    use FormatsHarData;

    public array $analysisData = [];

    public function mount(array $analysisData): void
    {
        $this->analysisData = $analysisData;
    }

    public function render()
    {
        return view('livewire.tools.har-analyzer.tabs.domains');
    }

    public function getDomainTypeProperty(): string
    {
        $domains = collect($this->analysisData['domains'] ?? []);
        $mainDomain = $domains->first()['domain'] ?? '';

        if (str_contains($mainDomain, 'localhost') || str_contains($mainDomain, '127.0.0.1')) {
            return 'local';
        } elseif (str_contains($mainDomain, '.dev') || str_contains($mainDomain, '.test')) {
            return 'development';
        } elseif (str_contains($mainDomain, 'staging') || str_contains($mainDomain, 'test')) {
            return 'staging';
        }

        return 'production';
    }

    public function getThirdPartyDomainsProperty(): array
    {
        $domains = collect($this->analysisData['domains'] ?? []);
        $mainDomain = $domains->first()['domain'] ?? '';
        $mainDomainParts = explode('.', $mainDomain);
        $rootDomain = count($mainDomainParts) >= 2 ?
            $mainDomainParts[count($mainDomainParts) - 2].'.'.$mainDomainParts[count($mainDomainParts) - 1] :
            $mainDomain;

        return $domains->filter(function ($domain) use ($rootDomain) {
            return ! str_contains($domain['domain'], $rootDomain) &&
                ! str_contains($domain['domain'], 'localhost') &&
                ! str_contains($domain['domain'], '127.0.0.1');
        })->toArray();
    }
}
