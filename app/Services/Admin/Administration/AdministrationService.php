<?php

namespace App\Services\Admin\Administration;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\AdminPermission;
use App\Models\AdminClient;
class AdministrationService {

    public function settingsPage($data){
        $user = auth('admin')->user();


        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        return response()->json(['data' => $data] , 200);
    }
    public function settingsSubmit($data){
        if ($data['password1'] != $data['password2']){
            return response()->json(['status' => 'Passwords don\'t match'] , 200);
        }
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $updatePasswordStatus = Admin::where('id' , $data['admin_id'])->update(['password' => $data['password1']]);
        if ($updatePasswordStatus) {
            return response()->json(['message' => 'success'] , 200);
        }
        return response()->json(['message' => 'failed'] , 200);
    }

    public function initSaveProfilePic($request){
        $validated = $request->validate([
            'img' => 'required|file|image|mimes:jpeg,jpg,png,gif|max:5120', 
        ]);

        $imagePath = 'public/profile'; 

        $file = $request->file('img');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs($imagePath, $filename);

        $url = Storage::url("profile/$filename");
        [$width, $height] = getimagesize($file);

        return response()->json([
            'status' => 'success',
            'url' => $url,
            'width' => $width,
            'height' => $height,
        ], 200);
    }


    public function cropProfilePic($request){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $validated = $request->validate([
            'imgUrl' => 'required|string',
            'imgInitW' => 'required|integer',
            'imgInitH' => 'required|integer',
            'imgW' => 'required|integer',
            'imgH' => 'required|integer',
            'imgY1' => 'required|integer',
            'imgX1' => 'required|integer',
            'cropW' => 'required|integer',
            'cropH' => 'required|integer',
            'rotation' => 'required|numeric',
            'admin_id' => 'required|integer|exists:osis_admins,id',
        ]);

        $imgUrl = storage_path('app/public/' . ltrim($validated['imgUrl'], '/'));
        $outputPath = 'public/profile/';
        $filename = "croppedImg_" . uniqid() . '.jpg';
        $outputFullPath = storage_path('app/' . $outputPath . $filename);

        $what = getimagesize($imgUrl);

        switch (strtolower($what['mime'])) {
            case 'image/png':
                $source_image = imagecreatefrompng($imgUrl);
                break;
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($imgUrl);
                break;
            case 'image/gif':
                $source_image = imagecreatefromgif($imgUrl);
                break;
            default:
                return response()->json(['message' => 'Unsupported image type'], 400);
        }

        if (!is_writable(dirname($outputFullPath))) {
            return response()->json(['status' => 'error', 'message' => 'Cannot write cropped file'], 500);
        }
        
        $resizedImage = imagecreatetruecolor($validated['imgW'], $validated['imgH']);
        imagecopyresampled(
            $resizedImage,
            $source_image,
            0,
            0,
            0,
            0,
            $validated['imgW'],
            $validated['imgH'],
            $validated['imgInitW'],
            $validated['imgInitH']
        );

        $rotatedImage = imagerotate($resizedImage, -$validated['rotation'], 0);

        $croppedImage = imagecreatetruecolor($validated['cropW'], $validated['cropH']);
        imagecopyresampled(
            $croppedImage,
            $rotatedImage,
            0,
            0,
            $validated['imgX1'],
            $validated['imgY1'],
            $validated['cropW'],
            $validated['cropH'],
            $validated['cropW'],
            $validated['cropH']
        );

        imagejpeg($croppedImage, $outputFullPath, 100);

        DB::table('osis_admins')
            ->where('id', $validated['admin_id'])
            ->update(['profile_picture' => Storage::url($outputPath . $filename)]);

        return response()->json([
            'status' => 'success',
            'url' => Storage::url($outputPath . $filename),
        ], 200);
    }



    public function accountsPage($data){
        $user = auth('admin')->user();

        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        $data['admins'] = Admin::whereNotIn('status', ['deleted', 'inactive'])->get();
        return response()->json(['data' => $data] , 200);
    }


    public function accountsUpdate($data , $admin_id){
        if (empty($data['password']) || $data['password'] == ""){
            unset($data['password']);
        }
        else {
            $data['password'] = bcrypt($data['password']);
        }
        $updateStatus = Admin::where('id' , $admin_id)->update(['password' => $data['password']]);
        if ($updateStatus) {
            return response()->json(['status' => 'updated'] , 200);
        }
        else {
            return response()->json(['status' => 'failed'] , 200);
        }
    }



    public function accountsDelete($data , $admin_id){
        $deleteStatus = Admin::where('id' , $admin_id)->delete();
        if ($deleteStatus){
            return response()->json(['status' => 'Deleted'] , 200);
        }
        else {
            return response()->json(['status' => 'failed'] , 200);
        }
    }


    public function newAccountPage($data){
        $user = auth('admin')->user();

        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $data['admin_permission_modules'] = AdminPermission::where('admin_id' , $data['admin_id'])->pluck('module');
        return response()->json(['data' => $data] , 200);
    }

    public function newAccountSubmit($data){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $createAdmin = Admin::create($data);
        if (!empty($data['modules'])) {
            foreach ($data['modules'] as $module) {
                AdminPermission::create([
                    'admin_id' => $data['admin_id'],  // استخدام admin_id من متغير $data
                    'module' => $module
                ]);
            }
        }
        return response()->json(['status' => 'updated'] , 200);
    }


    public function adminDetailPage($data , $admin_id){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        $data['admin'] = Admin::where('id' , $admin_id)->first();

        $data['admin_permission_modules'] = AdminPermission::all();

        $data['admin_permissions'] = AdminPermission::where('admin_id' , $admin_id)->get();

        $data['admin_assigned_modules'] = AdminPermission::where('admin_id' , $admin_id)->pluck('module');

        $data['admin_clients'] = DB::table('osis_client')
                                    ->whereIn('id', function ($query) use ($admin_id) {
                                        $query->select('client_id')
                                            ->from('osis_admin_client')
                                            ->where('admin_id', $admin_id);
                                    })
                                    ->orderBy('name', 'asc')
                                    ->get();

        $data['admin_clients_not'] = DB::table('osis_client')
                                        ->whereNotIn('id', function($query) use ($admin_id) {
                                            $query->select('client_id')
                                                ->from('osis_admin_client')
                                                ->where('admin_id', $admin_id);
                                        })
                                        ->orderBy('name', 'asc')
                                        ->get();


        return response()->json(['data' => $data] , 200);
    }


    public function adminDetailUpdatePermissions($data , $admin_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if (!empty($data['dashboard']) && $data['dashboard'] != "none") {
            Admin::where('id', $admin_id)->update(['dashboard' => $data['dashboard']]);
        }
        
        if (!empty($data['modules'])) {
            AdminPermission::where('admin_id', $admin_id)->delete();
        
            foreach ($data['modules'] as $module) {
                AdminPermission::create([
                    'admin_id' => $admin_id,
                    'module' => $module,
                ]);
            }
        }
        
        return response()->json(['status' => 'updated'], 200);
    }

    public function adminDetailAddClient($data , $admin_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $client_id = $data['client_id'];
        $createClient = AdminClient::create([
                                            'admin_id' => $admin_id,
                                            'client_id' => $client_id
                                            ]);
        return response()->json(['status' => 'updated'] , 200);
    }

    public function adminDetailRemoveClient($data , $admin_id , $client_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $deleteClient = AdminClient::where('admin_id', $admin_id)
                                        ->where('client_id', $client_id)
                                        ->delete();

        return response()->json(['admin_id' => $admin_id] , 200);
    }
}