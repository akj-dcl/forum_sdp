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

    /**
     * HTTP Client ke SDP
     */
    protected function client()
    {
        return Http::withToken($this->token)
            ->acceptJson()
            ->timeout(60);
    }

    /**
     * GET Request umum
     */
    protected function get(string $endpoint)
    {
        return $this->client()
            ->get($this->baseUrl . $endpoint)
            ->throw()
            ->json();
    }

    /**
     * Master Kanwil
     */
    public function getKanwil()
    {
        return $this->get('/kanwil/v1/daftarringkas');
    }

    /**
     * Master UPT
     */
    public function getUpt()
    {
        return $this->get('/upt/v1/daftar');
    }
}