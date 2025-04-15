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


    public function searchBuses(Request $request)
    {
    
        $token = $this->apiService->getToken();

   
        $validatedData = $request->validate([
            'DateOfJourney' => 'required|date',
            'OriginId' => 'required|integer',
            'DestinationId' => 'required|integer',
            'PreferredCurrency' => 'required|string',
            'EndUserIp' => 'required|ip',  
        ]);

   
        $searchPayload = [
            "DateOfJourney" => $validatedData['DateOfJourney'],
            "DestinationId" => $validatedData['DestinationId'],
            "EndUserIp" => $validatedData['EndUserIp'], 
            "OriginId" => $validatedData['OriginId'],
            "TokenId" => $token,  
            "PreferredCurrency" => $validatedData['PreferredCurrency'],
        ];


        $response = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Search', $searchPayload);


        if ($response->json('Response.Error.ErrorCode') === 6) {
            // Re-authenticate to get a new token
            $token = $this->apiService->authenticate();
            $searchPayload['TokenId'] = $token;

            // Retry the API request with the new token
            $response = Http::timeout(90)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Search', $searchPayload);
        }

        // Return the API response as JSON
        return $response->json();
    }

    public function busSeatLayout(Request $request){
        $token = $this->apiService->getToken();
        
      
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

    public function bookbus(Request $request){
         $token = $this->apiService->getToken();
  
       
        $validatedData = $request->validate([
            'TraceId' => 'required|string',
            'BoardingPointId' => 'required|integer',
            'DropingPointId' => 'required|integer',
            'ResultIndex' => 'required|string',
        "passenger"=>'required|array'
          ]);

   $searchData=[
    "EndUserIp"=> "192.168.5.37",
    "ResultIndex"=> $validatedData["ResultIndex"],
    "TraceId"=> $validatedData["TraceId"],
    "TokenId"=> $token,
    "BoardingPointId"=>$validatedData["BoardingPointId"],
    "DropingPointId"=>$validatedData["DropingPointId"],
    "Passenger"=>$validatedData["passenger"]
   ];
 
        
   $bookbus= Http::timeout(90)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Book', $searchData);


   if($bookbus->json('Response.Error.ErrorCode') === 6){
    $token = $this->apiService->authenticate();
    $searchData['TokenId'] = $token;
    $bookbus= Http::timeout(90)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Book', $searchData);

   }
return $bookbus;

}
}












