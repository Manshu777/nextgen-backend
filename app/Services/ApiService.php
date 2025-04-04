<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ApiToken;

class ApiService
{
    private $clientId;
    private $username;
    private $password;

    public function __construct()
    {
        $this->clientId = env('API_CLIENT_ID');
        $this->username = env('API_USERNAME');
        $this->password = env('API_PASSWORD');
    }

    public function authenticate()
    {
        $response = Http::post('https://Sharedapi.tektravels.com/SharedData.svc/rest/Authenticate', [
            'ClientId' => $this->clientId,
            'UserName' => $this->username,
            'Password' => $this->password,
            'EndUserIp' => request()->ip(),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $token = $data['TokenId'];
            $expiresAt = now()->addHours(14);

            ApiToken::updateOrCreate(
                ['id' => 1],
                ['token' => $token, 'expires_at' => $expiresAt]
            );

            return $token;
        }

        throw new \Exception('Authentication failed: ' . $response->body());
    }

    public function getToken()
    {
        $tokenData = ApiToken::first();

        if ($tokenData && now()->lessThan($tokenData->expires_at)) {
            return $tokenData->token;
        }

        return $this->authenticate();
    }

}
