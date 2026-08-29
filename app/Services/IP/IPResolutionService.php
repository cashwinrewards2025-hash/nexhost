<?php

namespace App\Services\IP;

use Illuminate\Support\Facades\Http;
use App\Models\ServerNetworkInfo;
use App\Models\Server;

class IPResolutionService
{
    protected string $ipstackApiKey;
    protected string $ipstackEndpoint = 'https://api.ipstack.com';

    public function __construct()
    {
        $this->ipstackApiKey = config('services.ipstack.key', '');
    }

    /**
     * Resolve IP information from multiple sources
     */
    public function resolveIP(string $ipAddress, Server $server): ServerNetworkInfo
    {
        $data = [
            'ip_address' => $ipAddress,
            'data_source' => 'manual',
            'resolved_at' => now(),
        ];

        // Try DNS lookup for hostname and reverse DNS
        $dnsData = $this->resolveDNS($ipAddress);
        $data = array_merge($data, $dnsData);

        // Try IPStack API for geolocation
        if ($this->ipstackApiKey) {
            $geoData = $this->resolveGeoLocation($ipAddress);
            $data = array_merge($data, $geoData);
        }

        // Try IP registry or public data
        $registryData = $this->resolveRegistry($ipAddress);
        $data = array_merge($data, $registryData);

        // Update or create network info
        $networkInfo = ServerNetworkInfo::updateOrCreate(
            ['server_id' => $server->id],
            $data
        );

        return $networkInfo;
    }

    /**
     * Resolve DNS information
     */
    protected function resolveDNS(string $ipAddress): array
    {
        $data = [];

        try {
            // Forward DNS lookup
            $hostname = gethostbyaddr($ipAddress);
            if ($hostname !== $ipAddress) {
                $data['hostname'] = $hostname;
            }
        } catch (\Exception $e) {
            //
        }

        return $data;
    }

    /**
     * Resolve geolocation using IPStack API
     */
    protected function resolveGeoLocation(string $ipAddress): array
    {
        $data = [];

        try {
            $response = Http::timeout(10)
                ->get("{$this->ipstackEndpoint}/{$ipAddress}", [
                    'access_key' => $this->ipstackApiKey,
                    'format' => 'json',
                ]);

            if ($response->successful()) {
                $geo = $response->json();

                if (!isset($geo['error'])) {
                    $data = array_merge($data, [
                        'country' => $geo['country_name'] ?? null,
                        'region' => $geo['region_name'] ?? null,
                        'city' => $geo['city'] ?? null,
                        'timezone' => $geo['time_zone']['id'] ?? null,
                        'latitude' => $geo['latitude'] ?? null,
                        'longitude' => $geo['longitude'] ?? null,
                        'isp' => $geo['isp'] ?? null,
                        'data_source' => 'ipstack',
                    ]);
                }
            }
        } catch (\Exception $e) {
            //
        }

        return $data;
    }

    /**
     * Resolve ASN and network provider information
     */
    protected function resolveRegistry(string $ipAddress): array
    {
        $data = [];

        try {
            // Try whois API alternative or local database
            // For demo purposes, this would integrate with a real whois service
            $response = Http::timeout(10)
                ->get('https://ipinfo.io/' . $ipAddress . '/json');

            if ($response->successful()) {
                $info = $response->json();
                $data = array_merge($data, [
                    'asn' => $info['asn'] ?? null,
                    'network_provider' => $info['org'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            //
        }

        return $data;
    }

    /**
     * Get IP information (geocoded)
     */
    public function getIPInfo(string $ipAddress): array
    {
        return [
            'ip' => $ipAddress,
            'hostname' => gethostbyaddr($ipAddress) ?: 'Not available',
            'country' => 'Not available',
            'region' => 'Not available',
            'city' => 'Not available',
            'timezone' => 'Not available',
            'asn' => 'Not available',
            'network_provider' => 'Not available',
        ];
    }

    /**
     * Validate IP address format
     */
    public function validateIP(string $ipAddress): bool
    {
        return filter_var($ipAddress, FILTER_VALIDATE_IP) !== false;
    }
}
