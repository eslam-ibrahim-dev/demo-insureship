<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

class MailConfigurationService
{
    public function getFullMailerByDomain(): array
    {
        $domain = $this->parseDomain();
        return config("mail.domains.{$domain}", config('mail.domains.default'));
    }

    protected function parseDomain(): string
    {
        $host = request()->getHost();
        $parts = explode('.', $host);
        return strtolower($parts[1] ?? $parts[0]);
    }
}
