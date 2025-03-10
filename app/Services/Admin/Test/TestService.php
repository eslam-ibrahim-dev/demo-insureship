<?php

namespace App\Services\Admin\Test;

use App\Models\Offer;
use App\Models\Client;
use App\Models\Subclient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
class TestService {
    
    public function indexPage($data){

        $user = auth('admin')->user();
        $data = [
            'profile_picture' => $user->profile_picture,
            'user_name' => $user->name,
            'alevel' => $user->level,
            'admin_id' => $user->id,
        ];

        $data['clients'] = (new Client())->getAllRecords($data);

        $data['countries'] = config('locale.countries');
        // ^^ Replace with a method to fetch locale countries if needed

        return response()->json($data);
    }


    public function testOrderGetSubclients($data , $client_id){
        $user = auth('admin')->user();

        $data = [
            'alevel' => $user->level,
            'admin_id' => $user->id,
        ];

        $subclients = Subclient::where('client_id', $client_id)->get();

        return response()->json(['subclients' => $subclients], 200);
    }

    public function testOrderGetOffers($data , $subclient_id){
        $user = auth('admin')->user();

        $data = [
            'alevel' => $user->level,
            'admin_id' => $user->id,
        ];

        $offers = (new Offer())->getOffersBySubclientId($subclient_id);

        return response()->json(['offers' => $offers], 200);
    }

    public function testSubmit($data){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        $subclient = Subclient::find($data['subclient_id']);

        if (!$subclient) {
            return response()->json(['status' => 'Error', 'message' => 'Subclient not found'], 404);
        }

        $data['client_id'] = $data['subclient_id'];
        unset($data['subclient_id']);

        $apiSalt = env('API_SALT');
        $data['api_key'] = hash_hmac('sha512', $subclient['apikey'], $apiSalt);

        if (isset($data['offer_id']) && $data['offer_id'] <= 0) {
            unset($data['offer_id']);
        }

        // $api_url='https://api.insureship.com/new_policy';
        //$api_url='https://api.ticketguardian.net/new_policy';
        //$api_url='https://api.shopguarantee.timbur/new_policy';
        $api_url = 'https://api.insureship.com/new_policy';

        try {
            $response = Http::acceptJson()->post($api_url, $data);

            $status = $response->successful() ? 'Success' : 'Error';

            return response()->json(['status' => $status], $response->status());
        } catch (\Exception $e) {
            Log::error('API Error: ' . $e->getMessage());

            return response()->json(['status' => 'Error', 'message' => 'API request failed'], 500);
        }
    }
}