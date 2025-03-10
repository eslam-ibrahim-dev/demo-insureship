<?php

namespace App\Services\Admin\Offer;
use App\Models\Offer;

class OfferService {
    public function indexPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        return response()->json(['data' => $data] , 200);
    }

    public function listPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        $data['offers'] = Offer::all();
        return response()->json(['data' => $data] , 200);
    }

    public function newPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        return response()->json(['data' => $data] , 200);
    }


    public function newSubmit($data , $request){
        $data['link_name'] = strtolower(str_replace(" ", "_", preg_replace("/[^ \w]+/", "", $data['name'])));
        foreach ($request->file('files') as $upload) {
            $short_dir = "images/icon/";
            $long_dir = storage_path("app/public/images/icon");
        
            if ($upload) {
                $new_filename = md5($upload->getClientOriginalName()) . '.' . $upload->getClientOriginalExtension();
        
                $upload->storeAs('public/images/icon', $new_filename);
        
                $data['icon'] = $short_dir . $new_filename;
            }
        }
        $insertStatus = Offer::create($data);
        if($insertStatus){
            return response()->json(['insert_status' => true] , 200);
        }
        else {
            return response()->json(['insert_status' => false] , 200);
        }
    }


    public function detailPage($data , $offer_id){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        $offer = Offer::find($offer_id);

        return response()->json(['offer' => $offer] , 200);
    }


    public function updateSubmit($data , $request , $offer_id){
        foreach ($request->files as $upload) {
            $short_dir = "/images/icon/";
            $long_dir = __DIR__."/../../../web/images/icon/";

            if (!empty($upload)) {
                $new_filename = md5($upload->getClientOriginalName()).".".($upload->getClientOriginalExtension());

                $upload->move($long_dir, $new_filename);

                $data['icon'] = $short_dir.$new_filename;
            }
        }
        $updateStatus = Offer::where('id' , $offer_id)->update($data);
        if ($updateStatus){
            return response()->json(['update_status' => true , 'offer_id' => $offer_id] , 200);
        }
        else{
            return response()->json(['update_status' => false] , 200);
        }
    }
}