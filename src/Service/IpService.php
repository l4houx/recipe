<?php

namespace App\Service;

use GeoIp2\Exception\AddressNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use GeoIp2\Database\Reader;

class IpService
{
    private ?Reader $reader = null;

    public function __construct(
        #[Autowire('%website_geoip_database%')]
        private readonly string $dbPath
    ) {
    }

    public function getLocation(string $ip): ?IpRecord
    {
        try {
            $record = $this->getReader()->country($ip);

            if (!$record->country->isoCode) {
                return null;
            }

            return new IpRecord(
                country: $record->country->isoCode,
            );
        } catch (AddressNotFoundException $e) {
            return null;
        }
    }

    private function getReader(): Reader
    {
        if (!$this->reader) {
            $this->reader = new Reader($this->dbPath);
        }

        return $this->reader;
    }
}
