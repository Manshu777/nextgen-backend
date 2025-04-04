<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\ApiService;

class BusController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    // Method to search bus cities
    public function searchBusCityList(Request $request)
    {
        // Fetch token from the ApiService
        $token = $this->apiService->getToken();

        // Make IpAddress optional in the validation
        $validatedData = $request->validate([
            "IpAddress" => 'nullable|ip',
        ]);

        // Get the search query from the request (optional)
        $searchQuery = $request->query('city_name');  // GET query parameter

        // Prepare the payload for the API request
        $searchPayload = [
            "TokenId" => $token,
            "IpAddress" => $validatedData['IpAddress'] ?? '223.178.208.102', // Use provided IP or fallback to a default
            "ClientId" => 'ApiIntegrationNew',
        ];

        // Make the API request to fetch all bus cities
        $response = Http::timeout(100)
            ->withHeaders([])
            ->post('http://api.tektravels.com/SharedServices/StaticData.svc/rest/GetBusCityList', $searchPayload);

        // Check if the token has expired and retry with a new token
        if ($response->json('Response.Error.ErrorCode') === 6) {
            $token = $this->apiService->authenticate();
            $searchPayload['TokenId'] = $token;

            // Retry the request with the new token
            $response = Http::timeout(90)
                ->withHeaders([])
                ->post('http://api.tektravels.com/SharedServices/StaticData.svc/rest/GetBusCityList', $searchPayload);
        }

        // Extract bus cities from the API response
        $busCities = $response->json('BusCities');

        // If a search query is provided, filter the bus cities
        if ($searchQuery) {
            $busCities = collect($busCities)->filter(function ($city) use ($searchQuery) {
                return stripos($city['CityName'], $searchQuery) !== false;  // case-insensitive search
            })->values()->all();
        }

        // Return the filtered (or unfiltered) list of bus cities as JSON
        return response()->json([
            'BusCities' => $busCities
        ]);
    }
}
