<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Illuminate\Support\Facades\Http;


class InsuranceController extends Controller
{
    //
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }





    public function GetInsurance(Request $request){
        $token = $this->apiService->getToken();

        $validate=$request->validate([
     'EndUserIp' => 'required|ip',
      'PlanCategory' => 'required|in:1,2,3,4,5,6',
      'PlanCoverage' => 'required|in:1,2,3,4,5,6,7,8',
      'PlanType' => 'required|in:1,2',
   
          'TravelStartDate' => 'required',

      'TravelEndDate' => 'required',
      'NoOfPax' => 'required|integer|min:1',
      'PaxAge' => 'required|array',
     ]);


     
     $validate["TokenId"]=$token;
   

     $response= Http::timeout(100)->post("https://InsuranceBE.tektravels.com/InsuranceService.svc/rest/Search",$validate);

if($response->json('Response.Error.ErrorCode') === 6){

    $token = $this->apiService->authenticate();
    $validate['TokenId'] = $token;
    $response= Http::timeout(100)->post("https://InsuranceBE.tektravels.com/InsuranceService.svc/rest/Search",$validate);
}
  


return $response ;



    } 

    public function searchInsurance(Request $request)
{
    try {
        // Validate request parameters
        $validated = $request->validate([
            'EndUserIp'       => 'required|ip',
            'PlanCategory'    => 'required|integer|in:1,2,3,4,5,6',
            'PlanCoverage'    => 'required|integer|in:1,2,3,4,5,6,7,8',
            'PlanType'        => 'required|integer|in:1,2',
            'TravelStartDate' => 'required',
            'TravelEndDate'   => 'required',
            'NoOfPax'         => 'required|integer|min:1',
            'PaxAge'          => 'required|array|min:1',
            'PaxAge.*'        => 'required|integer|min:1|max:100', 
        ]);

        // Get API Token
        $token = $this->apiService->getToken();
        $validated["TokenId"] = $token;

        // Define API endpoint
        $apiUrl = "https://InsuranceBE.tektravels.com/InsuranceService.svc/rest/Search";

        // Send request to TekTravels API
        $response = Http::post($apiUrl, $validated);

        // Check if API response is valid
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'API request failed. Please try again later.',
                'error'   => $response->body(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'data'    => $response->json(),
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Illuminate\Http\Client\RequestException $e) {
        // Handle HTTP client errors
        return response()->json([
            'success' => false,
            'message' => 'Failed to communicate with the insurance API',
            'error'   => $e->getMessage(),
        ], 500);

    } catch (\Exception $e) {
        // Catch all other errors
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

public function bookInsurance(Request $request)
{
    try {
        // Get API Token
        $token = $this->apiService->getToken();

        // Convert Passenger DOB format from "YYYY-MM-DDTHH:MM:SS" to "d/m/Y"
        if ($request->has('Passenger')) {
            foreach ($request->Passenger as $key => $passenger) {
                if (!empty($passenger['DOB'])) {
                    try {
                        $formattedDOB = \Carbon\Carbon::parse($passenger['DOB'])->format('d/m/Y');
                        $request->merge(["Passenger.$key.DOB" => $formattedDOB]);
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => "Invalid date format for Passenger $key DOB.",
                            'error'   => $e->getMessage(),
                        ], 422);
                    }
                }
            }
        }

        // Validate request data
        $validated = $request->validate([
            'EndUserIp'                => 'required|ip',
            'TraceId'                  => 'required|string',
            'GenerateInsurancePolicy'  => 'required|boolean',
            'ResultIndex'              => 'required|integer',
            'Passenger'                => 'required|array|min:1',
            'Passenger.*.Title'        => 'required|string|in:Mr,Mrs,Miss,Ms,SHRI,SMT',
            'Passenger.*.BeneficiaryTitle'        => 'required|string|in:Mr,Mrs,Miss,Ms,SHRI,SMT',
            'Passenger.*.FirstName'    => 'required|string',
            'Passenger.*.LastName'     => 'required|string',
            'Passenger.*.BeneficiaryName' => 'required|string',
            'Passenger.*.RelationShipToInsured' => 'required|string',
            'Passenger.*.RelationToBeneficiary' => 'required|string',
            'Passenger.*.PassportCountry' => 'required|string',
            'Passenger.*.Gender'       => 'required|string',
            'Passenger.*.Sex'          => 'required|integer',
            'Passenger.*.DOB'          => 'required',
            'Passenger.*.PassportNo'   => 'required|string',
            'Passenger.*.PhoneNumber'  => 'required|numeric|digits_between:8,15',
            'Passenger.*.EmailId'      => 'required|email',
            'Passenger.*.AddressLine1' => 'required|string',
            'Passenger.*.AddressLine2' => 'nullable|string',
            'Passenger.*.CityCode'     => 'required|string',
            'Passenger.*.CountryCode'  => 'required|string',
            'Passenger.*.MajorDestination' => 'required|string',
            'Passenger.*.PinCode'      => 'required|numeric|digits_between:4,10',
        ]);

        // Ensure TokenId is included
        $validated["TokenId"] = $token;

        // API URL
        $apiUrl = "https://InsuranceBE.tektravels.com/InsuranceService.svc/rest/Book";

        // Send request to API
        $response = Http::post($apiUrl, $validated);

        // Check if API response failed
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'API request failed. Please try again later.',
                'error'   => $response->body(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'data'    => $response->json(),
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Illuminate\Http\Client\RequestException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to communicate with the insurance API',
            'error'   => $e->getMessage(),
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


public function generatePolicy(Request $request)
{
    try {
        // Get API Token
        $token = $this->apiService->getToken();

        // Validate request data
        $validated = $request->validate([
            'EndUserIp' => 'required|ip',
            'BookingId' => 'required|integer',
        ]);

        // Add TokenId to the request
        $validated["TokenId"] = $token;

        // API URL
        $apiUrl = "https://InsuranceBE.tektravels.com/InsuranceService.svc/rest/GeneratePolicy";

        // Send request to API
        $response = Http::post($apiUrl, $validated);

        // Check if API response failed
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'API request failed while generating policy.',
                'error'   => $response->body(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'data'    => $response->json(),
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Illuminate\Http\Client\RequestException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to communicate with the insurance API',
            'error'   => $e->getMessage(),
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred while generating the policy.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

public function getBookingDetail(Request $request)
{
    try {
        // Generate API Token
        $validated = $request->validate([
            'EndUserIp' => 'required|ip',
            'BookingId' => 'required|integer',
        ]);

        // Add the generated TokenId to the request
        $validated["TokenId"] = $this->apiService->getToken();

        // API URL
        $apiUrl = "https://InsuranceBE.tektravels.com/InsuranceService.svc/rest/GetBookingDetail";

        // Send request to TekTravels API
        $response = Http::post($apiUrl, $validated);

        // Check if API response failed
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'API request failed while fetching booking details.',
                'error'   => $response->body(),
            ], $response->status());
        }

        // Decode API response
        $apiResponse = $response->json();

        // Check if the response contains valid data
        if (!isset($apiResponse['Response']) || empty($apiResponse['Response'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid response received from the API.',
                'error'   => $apiResponse,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data'    => $apiResponse,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Illuminate\Http\Client\RequestException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to communicate with the insurance API.',
            'error'   => $e->getMessage(),
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred while fetching booking details.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



}

