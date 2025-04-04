<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;


class HotelControllerSearchRes extends Controller
{

    public function searchHotels(Request $request)
{
    $validated = $request->validate([
        'cityCode' => 'required|string',
        'checkIn' => 'required|date',
        'checkOut' => 'required|date',
        'adults' => 'required|integer|min:1',
        'children' => 'required|integer|min:0',
        'guestNationality' => 'required|string',
        'page' => 'required|integer',
    ]);

    $client = new Client();

    // Fetch hotel codes
    $response1 = $client->post('http://api.tbotechnology.in/TBOHolidays_HotelAPI/TBOHotelCodeList', [
        'auth' => ['TBOStaticAPITest', 'Tbo@11530818'],
        'json' => [
            "CityCode" => $validated['cityCode'],
            "IsDetailedResponse" => false,
        ]
    ]);

    $hotelData = json_decode($response1->getBody()->getContents(), true);
    $hotelCodes = array_column($hotelData['Hotels'], 'HotelCode');

    $pageSize = 10;
    $page = max(1, $validated['page']); // Ensure page starts at 1 if 0 is given

    $hotelresult = [];
    
    while ($page <= ceil(count($hotelCodes) / $pageSize)) {
        $start = ($page - 1) * $pageSize;
        $limitedHotelCodes = array_slice($hotelCodes, $start, $pageSize);

        if (empty($limitedHotelCodes)) {
            return response()->json([
                'message' => 'No hotels available',
                'totalHotels' => [],
                'count' => ceil(count($hotelCodes) / $pageSize)
            ]);
        }

        foreach ($limitedHotelCodes as $limitedHotelCode) {
            // 2nd API request: Search for hotel availability
            $response3 = $client->post('https://affiliate.tektravels.com/HotelAPI/Search', [
                'auth' => ['Apkatrip', 'Apkatrip@1234'],
                'json' => [
                    "CheckIn" => $validated['checkIn'],
                    "CheckOut" => $validated['checkOut'],
                    "HotelCodes" => $limitedHotelCode,
                    "GuestNationality" => $validated['guestNationality'],
                    "PaxRooms" => [
                        [
                            "Adults" => $validated['adults'],
                            "Children" => $validated['children'],
                            "ChildrenAges" => $validated['children'] > 0 ? [null] : null,
                        ]
                    ],
                    "ResponseTime" => 23.0,
                    "IsDetailedResponse" => true,
                    "Filters" => [
                        "Refundable" => false,
                        "NoOfRooms" => 1,
                        "MealType" => 0,
                        "OrderBy" => 0,
                        "StarRating" => 0,
                        "HotelName" => null,
                    ]
                ]
            ]);

            $searchResults = json_decode($response3->getBody()->getContents(), true);

            // Skip hotels with no available rooms
            if ($searchResults['Status']['Code'] === 201 && $searchResults['Status']['Description'] === "No Available rooms for given criteria") {
                continue;
            }

            // 3rd API request: Get hotel details
            $response2 = $client->post('http://api.tbotechnology.in/TBOHolidays_HotelAPI/Hoteldetails', [
                'auth' => ['TBOStaticAPITest', 'Tbo@11530818'],
                'json' => [
                    "Hotelcodes" => $limitedHotelCode,
                    "Language" => "EN",
                ]
            ]);

            $hotelDetails = json_decode($response2->getBody()->getContents(), true);

            // Add valid results to the array
            $hotelresult[] = [
                "hotelDetails" => $hotelDetails,
                "searchResults" => $searchResults
            ];
        }

        // If at least one hotel is found, break the loop
        if (!empty($hotelresult)) {
            break;
        }

        // If no results, move to the next page
        $page++;
    }

    return response()->json([
        'totalHotels' => $hotelresult,
        'count' => ceil(count($hotelCodes) / $pageSize)
    ]);
}



   public    function singleHotelget(Request $request)
    {
        $validated = $request->validate([
            'HotelCode' => 'required|string',
            'checkIn' => 'required|date',
            'checkOut' => 'required|date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'guestNationality' => 'required|string',


        ]);
        $client = new \GuzzleHttp\Client();


        $response1 = Http::withBasicAuth('TBOStaticAPITest', 'Tbo@11530818')->post('http://api.tbotechnology.in/TBOHolidays_HotelAPI/Hoteldetails', [
            "Hotelcodes" => $validated['HotelCode'],
            "Language" => "EN"
        ]);

        $response2 = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')->post('https://affiliate.tektravels.com/HotelAPI/Search', [


            "CheckIn" => $validated['checkIn'],
            "CheckOut" => $validated['checkOut'],
            "HotelCodes" => $validated['HotelCode'],
            "GuestNationality" => $validated['guestNationality'],
            "PaxRooms" => [
                [
                    "Adults" => $validated['adults'],
                    "Children" => $validated['children'],
                    "ChildrenAges" => $validated['children'] > 0 ? [null] : null
                ]
            ],
            "ResponseTime" => 23.0,
            "IsDetailedResponse" => true,
            "Filters" => [
                "Refundable" => false,
                "NoOfRooms" => 1,
                "MealType" => 0,
                "OrderBy" => 0,
                "StarRating" => 0,
                "HotelName" => null

            ]
        ]);



        $resp1 = json_decode($response1->getBody()->getContents(), true);
        $resp2 = json_decode($response2->getBody()->getContents(), true);

        $values = ["hoteldetail1" => $resp1['HotelDetails'], "hoteldetail2" => $resp2["HotelResult"]];
        return response()->JSON($values);

    }


    public   function preBooking(Request $request)
              {
        $validated = $request->validate([
            'BookingCode' => 'required',



        ]);


        $response1 = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')->post('https://affiliate.tektravels.com/HotelAPI/PreBook', [
            "BookingCode" => $validated['BookingCode']

        ]);
        $response1 = json_decode($response1->getBody()->getContents(), true);

        return $response1;
    }


    public function bookHotel(Request $request)
{
    $validated = $request->validate([
        'BookingCode' => 'required',
        'IsVoucherBooking' => 'required|boolean',
        'GuestNationality' => 'required|string',
        'EndUserIp' => 'required|ip',
        'RequestedBookingMode' => 'required|integer',
        'NetAmount' => 'required|numeric',
        'HotelRoomsDetails' => 'required|array',
    ]);

    // Send booking request without authentication
    $response = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')
            ->post('https://HotelBE.tektravels.com/hotelservice.svc/rest/book', [
                "BookingCode" => $validated['BookingCode'],
                "IsVoucherBooking" => $validated['IsVoucherBooking'],
                "GuestNationality" => $validated['GuestNationality'],
                "EndUserIp" => $validated['EndUserIp'],
                "RequestedBookingMode" => $validated['RequestedBookingMode'],
                "NetAmount" => $validated['NetAmount'],
                "HotelRoomsDetails" => $validated['HotelRoomsDetails']
            ]);
    // Decode the API response
    $responseData = json_decode($response->body(), true);

    return response()->json($responseData);
}



    public function getBookingDetail(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'BookingId' => 'required|string',
            'EndUserIp' => 'required|ip',
            'TokenId' => 'required|string'
        ]);

        // Send request to GetBookingDetail API
        $response = Http::post('http://HotelBE.tektravels.com/internalhotelservice.svc/rest/GetBookingDetail', [
            "BookingId" => $validated['BookingId'],
            "EndUserIp" => $validated['EndUserIp'],
            "TokenId" => $validated['TokenId']
        ]);

        // Decode API response
        $responseData = json_decode($response->body(), true);

        return response()->json($responseData);
    }

}
