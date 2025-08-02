<?php

namespace App;

class IpAddressRange
{
    public function __construct(
        public readonly string $start,
        public readonly string $end,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $protocolVersion = 'IPv4',
        public readonly ?int $index = null,
        public readonly ?array $originalData = null,
    ) {}

    public static function fromArray(array $data, ?int $index = null): self
    {
        return new self(
            start: $data['startingIPRange'] ?? $data['start'] ?? '',
            end: $data['endingIPRange'] ?? $data['end'] ?? '',
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            protocolVersion: $data['protocolVersion']['name'] ?? $data['protocol_version'] ?? 'IPv4',
            index: $index,
            originalData: $data
        );
    }

    public function getRangeSize(): int
    {
        if (! $this->isValid()) {
            return 0;
        }

        return max(0, $this->getEndLong() - $this->getStartLong() + 1);
    }

    public function isValid(): bool
    {
        return $this->getStartLong() !== false && $this->getEndLong() !== false;
    }

    public function getStartLong(): int|false
    {
        return ip2long($this->start);
    }

    public function getEndLong(): int|false
    {
        return ip2long($this->end);
    }

    public function isSingleIp(): bool
    {
        return $this->start === $this->end;
    }

    public function isInvertedRange(): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        return $this->getStartLong() > $this->getEndLong();
    }

    public function overlapsWith(IpAddressRange $other): bool
    {
        if (! $this->isValid() || ! $other->isValid()) {
            return false;
        }

        return $this->getStartLong() <= $other->getEndLong() &&
            $this->getEndLong() >= $other->getStartLong();
    }

    public function toArray(): array
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
            'name' => $this->name,
            'description' => $this->description,
            'protocol_version' => $this->protocolVersion,
            'index' => $this->index,
            'original_data' => $this->originalData,
        ];
    }

    public function spansMixedNetworks(): bool
    {
        return $this->isPrivateIpAddress($this->start) !== $this->isPrivateIpAddress($this->end);
    }

    public function isPrivateIpAddress(string $ip): bool
    {
        $long = ip2long($ip);

        if ($long === false) {
            return false;
        }

        // 10.0.0.0/8
        if ($long >= ip2long('10.0.0.0') && $long <= ip2long('10.255.255.255')) {
            return true;
        }

        // 172.16.0.0/12
        if ($long >= ip2long('172.16.0.0') && $long <= ip2long('172.31.255.255')) {
            return true;
        }

        // 192.168.0.0/16
        if ($long >= ip2long('192.168.0.0') && $long <= ip2long('192.168.255.255')) {
            return true;
        }

        return false;
    }

    public function isPrivateRange(): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        return $this->isPrivateIpAddress($this->start) && $this->isPrivateIpAddress($this->end);
    }

    public function containsReservedIps(): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        $startLong = $this->getStartLong();
        $endLong = $this->getEndLong();

        $reservedRanges = [
            ['start' => ip2long('0.0.0.0'), 'end' => ip2long('0.255.255.255')],
            ['start' => ip2long('127.0.0.0'), 'end' => ip2long('127.255.255.255')],
            ['start' => ip2long('169.254.0.0'), 'end' => ip2long('169.254.255.255')],
            ['start' => ip2long('224.0.0.0'), 'end' => ip2long('255.255.255.255')],
        ];

        foreach ($reservedRanges as $reserved) {
            if ($startLong <= $reserved['end'] && $endLong >= $reserved['start']) {
                return true;
            }
        }

        return false;
    }
}
