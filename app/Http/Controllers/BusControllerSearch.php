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
        $response = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Search', $searchPayload);

        // Handle token expiration or other errors and retry
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
        // $token = $this->apiService->getToken();
        $token="b347ccfe-ee7b-4f57-8172-8ba114241259";

      
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

    public function BookBus(Request $request){
        $token = $this->apiService->getToken();

        $validatedData = $request->validate([
            'TraceId' => 'required|string',
            'ResultIndex' => 'required|integer',
            'EndUserIp' => 'required|ip',
            'TokenId' => 'required|string',
            'BoardingPointId' => 'required|integer',
            'DropingPointId' => 'required|integer',
            'Passenger' => 'required|array|min:1',
        
            'Passenger.*.LeadPassenger' => 'required|boolean',
            'Passenger.*.PassengerId' => 'required|integer',
            'Passenger.*.Title' => 'required|string',
            'Passenger.*.Address' => 'required|string|nullable',
            'Passenger.*.Age' => 'required|integer',
            'Passenger.*.Email' => 'required|email',
            'Passenger.*.FirstName' => 'required|string',
            'Passenger.*.LastName' => 'required|string',
            'Passenger.*.Phoneno' => 'required|string',
            'Passenger.*.Gender' => 'required', 
            'Passenger.*.IdNumber' => 'required|string|nullable',
            'Passenger.*.IdType' => 'required|string|nullable',
        
            'Passenger.*.Seat.ColumnNo' => 'required|string',
            'Passenger.*.Seat.Height' => 'required|integer',
            'Passenger.*.Seat.IsLadiesSeat' => 'required|boolean',
            'Passenger.*.Seat.IsMalesSeat' => 'required|boolean',
            'Passenger.*.Seat.IsUpper' => 'required|boolean',
            'Passenger.*.Seat.RowNo' => 'required|string',
            'Passenger.*.Seat.SeatIndex' => 'required',
            'Passenger.*.Seat.SeatName' => 'required|string',
            'Passenger.*.Seat.SeatStatus' => 'required|boolean',
            'Passenger.*.Seat.SeatType' => 'required|integer',
            'Passenger.*.Seat.Width' => 'required|integer',
        
            'Passenger.*.Seat.Price.CurrencyCode' => 'required|string',
            'Passenger.*.Seat.Price.BasePrice' => 'required|numeric',
            'Passenger.*.Seat.Price.Tax' => 'required|numeric',
            'Passenger.*.Seat.Price.OtherCharges' => 'required|numeric',
            'Passenger.*.Seat.Price.Discount' => 'required|numeric',
            'Passenger.*.Seat.Price.PublishedPrice' => 'required|numeric',
            'Passenger.*.Seat.Price.PublishedPriceRoundedOff' => 'required|numeric',
            'Passenger.*.Seat.Price.OfferedPrice' => 'required|numeric',
            'Passenger.*.Seat.Price.OfferedPriceRoundedOff' => 'required|numeric',
            'Passenger.*.Seat.Price.AgentCommission' => 'required|numeric',
            'Passenger.*.Seat.Price.AgentMarkUp' => 'required|numeric',
            'Passenger.*.Seat.Price.TDS' => 'required|numeric',
        
            'Passenger.*.Seat.Price.GST.CGSTAmount' => 'required|numeric',
            'Passenger.*.Seat.Price.GST.CGSTRate' => 'required|numeric',
            'Passenger.*.Seat.Price.GST.CessAmount' => 'required|numeric',
            'Passenger.*.Seat.Price.GST.CessRate' => 'required|numeric',
            'Passenger.*.Seat.Price.GST.IGSTAmount' => 'required|numeric',
            'Passenger.*.Seat.Price.GST.IGSTRate' => 'required|numeric',
            'Passenger.*.Seat.Price.GST.SGSTAmount' => 'required|numeric',
            'Passenger.*.Seat.Price.GST.SGSTRate' => 'required|numeric',
            'Passenger.*.Seat.Price.GST.TaxableAmount' => 'required|numeric',
        ]);

           //            $gstInfo=[
           //            "CGSTAmount"=>$request["CGSTAmount"],
           //            "CGSTRate"=>$request["CGSTRate"],
           //            "CessAmount"=>$request["CessAmount"],
           //            "CessRate"=>$request["CessRate"],
           //            "IGSTAmount"=>$request["IGSTAmount"],
           //            "IGSTRate"=>$request["IGSTRate"],
           //            "SGSTAmount"=>$request["SGSTAmount"],
           //            "SGSTRate"=>$request["SGSTRate"],
           //            "TaxableAmount"=>$request["TaxableAmount"]
           //            ];


        $search=[  "TraceId" => $validatedData['TraceId'],
            "ResultIndex" => $validatedData['ResultIndex'],
            "EndUserIp" => $validatedData['EndUserIp'],  
            "TokenId" => $token,
            "Passenger"=>$request["Passenger"]
    ];

    $busBook = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Search', $search);

    if ($busBook->json('Response.Error.ErrorCode') === 6) {
        // Re-authenticate to get a new token
        $token = $this->apiService->authenticate();
        $search['TokenId'] = $token;
        $busBook = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Search', $search);
       };


       return   response()->json(["buslayout"=>json_decode($busBook)]);



        
    }
}












