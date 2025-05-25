<?php

namespace App\Services;

use Illuminate\Http\Request;

class MailerService
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getByDomain(): array
    {
        $host = $this->request->getHost();
        $domain = strtolower(explode('.', $host)[1]) ?? '';

        foreach (config('mail.domains') as $key => $config) {
            if (str_contains($domain, $key)) {
                return $config;
            }
        }

        return config('mail.default');
    }

    public function getByClientId(int $clientId): array
    {
        return config("mail.clients.{$clientId}", config('mail.default'));
    }

    public function getByClientSubclientId(int $clientId, int $subclientId = 0): array
    {
        if ($subclientId && $mappedClientId = config("mail.subclients.{$subclientId}")) {
            return $this->getByClientId($mappedClientId);
        }

        return $this->getByClientId($clientId);
    }

    public function getBySuperclientClientSubclientId(
        int $clientId,
        int $subclientId = 0,
        int $superclientId = 0
    ): array {
        // Superclient takes highest priority
        if ($superclientId === 1) { // InsureShip
            return config('mail.domains.insureship');
        }

        // Then check subclient
        if ($subclientId && $mappedClientId = config("mail.subclients.{$subclientId}")) {
            return $this->getByClientId($mappedClientId);
        }

        // Finally fall back to client
        return $this->getByClientId($clientId);
    }

    // Static helper methods for convenience
    public static function byDomain(): array
    {
        return app(self::class)->getByDomain();
    }

    public static function byClientId(int $clientId): array
    {
        return app(self::class)->getByClientId($clientId);
    }

    public static function byClientSubclientId(int $clientId, int $subclientId = 0): array
    {
        return app(self::class)->getByClientSubclientId($clientId, $subclientId);
    }

    public static function bySuperclientClientSubclientId(
        int $clientId,
        int $subclientId = 0,
        int $superclientId = 0
    ): array {
        return app(self::class)->getBySuperclientClientSubclientId($clientId, $subclientId, $superclientId);
    }
}