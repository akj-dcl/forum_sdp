<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SdpMasterService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.sdp.base_url');
        $this->token = config('services.sdp.token');
    }

    protected function client()
    {
        return Http::withToken($this->token)
            ->acceptJson()
            ->timeout(60);
    }

    protected function get(string $endpoint)
    {
        return $this->client()
            ->get($this->baseUrl . $endpoint)
            ->throw()
            ->json();
    }

    public function getKanwil()
    {
        return $this->get('/kanwil/v1/daftarringkas');
    }

    public function getUpt()
    {
        return $this->get('/upt/v1/daftar');
    }
}