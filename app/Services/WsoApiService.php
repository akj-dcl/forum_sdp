<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WsoApiService
{
    protected string $token;

    public function __construct()
    {
        $this->token = env('WSO_TOKEN');
    }

    private function client()
    {
        return Http::withToken($this->token)
            ->acceptJson()
            ->timeout(60);
    }

    public function getKanwil()
    {
        return $this->client()
            ->get(env('WSO_KANWIL_URL').'/daftarringkas')
            ->throw()
            ->json();
    }

    public function getKanwilLengkap()
    {
        return $this->client()
            ->get(env('WSO_KANWIL_URL').'/daftar')
            ->throw()
            ->json();
    }
    public function getUpt()
    {
        return $this->client()
            ->get(env('WSO_UPT_URL').'/daftarringkas')
            ->throw()
            ->json();
    }
    
    public function getUptLengkap()
    {
        return $this->client()
            ->get(env('WSO_UPT_URL').'/daftar')
            ->throw()
            ->json();
    }

    public function getWbp()
    {
        return $this->client()
            ->get(env('WSO_UPT_URL').'/detail_wbp')
            ->throw()
            ->json();
    }
}