<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use App\Services\ApiService;



class BusControllerSearch extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    // Method to search buses based on provided criteria
    public function searchBuses(Request $request)
    {
        // Fetch the token from the ApiService
        $token = $this->apiService->getToken();

        // Validate the request input, including IP address
        $validatedData = $request->validate([
            'DateOfJourney' => 'required|date',
            'OriginId' => 'required|integer',
            'DestinationId' => 'required|integer',
            'PreferredCurrency' => 'required|string',
            'EndUserIp' => 'required|ip',  // Validating IP address
        ]);

        // Prepare the payload for the API request
        $searchPayload = [
            "DateOfJourney" => $validatedData['DateOfJourney'],
            "DestinationId" => $validatedData['DestinationId'],
            "EndUserIp" => $validatedData['EndUserIp'],  // Use validated IP
            "OriginId" => $validatedData['OriginId'],
            "TokenId" => $token,  // Use the token from the service
            "PreferredCurrency" => $validatedData['PreferredCurrency'],
        ];

        // Make the API request to search buses
        $response = Http::timeout(100)->withHeaders([])->post('https://busbe.tektravels.com/Busservice.svc/rest/Search', $searchPayload);

        // Handle token expiration or other errors and retry
        if ($response->json('Response.Error.ErrorCode') === 6) {
            // Re-authenticate to get a new token
            $token = $this->apiService->authenticate();
            $searchPayload['TokenId'] = $token;

            // Retry the API request with the new token
            $response = Http::timeout(90)->withHeaders([])->post('https://busbe.tektravels.com/Busservice.svc/rest/Search', $searchPayload);
        }

        // Return the API response as JSON
        return $response->json();
    }

    public function busSeatLayout(Request $request){
        $token = $this->apiService->getToken();

        // Validate the request input, including IP address
        $validatedData = $request->validate([
            'TraceId' => 'required',
            'ResultIndex' => 'required',
          
            'EndUserIp' => 'required|ip',  
        ]);

        $searchPayload = [
            "TraceId" => $validatedData['TraceId'],
            "ResultIndex" => $validatedData['ResultIndex'],
            "EndUserIp" => $validatedData['EndUserIp'],  // Use validated IP
            "TokenId" => $token,  // Use the token from the service
        ];

        $buslayout = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBusSeatLayOut', $searchPayload);
        $busBOARDING = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBoardingPointDetails', $searchPayload);

        
        if ($buslayout->json('Response.Error.ErrorCode') === 6) {
            // Re-authenticate to get a new token
            $token = $this->apiService->authenticate();
            $searchPayload['TokenId'] = $token;

            // Retry the API request with the new token
            $buslayout = Http::timeout(90)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBusSeatLayOut', $searchPayload);
            $busBOARDING = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBoardingPointDetails', $searchPayload);

        }
        // return $searchPayload;
        return   response()->json(["buslayout"=>json_decode($buslayout),"busbording"=>json_decode($busBOARDING)]);


    }
}
