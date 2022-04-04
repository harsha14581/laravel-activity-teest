<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;

class ActivityController extends Controller
{
    function createGlobalActivities(Request $request){
        $data = $request->validate([
            'date' => 'required|string|date',
            'title' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|file|mimes:jpeg,bmp,png,jpg'
        ]);

        if(Auth::user()->role == 'ADMIN'){
           $activities_count =  Activity::whereDate('date', $data['date'])->count();
           if($activities_count >= 4){
               return response()->json([
                'code' => 422,
                'error' => true,
                'message' => 'Cannot create activity more than 4 for '.$data['date'],
                'data' => []
               ]);
           }

            $image      = $request->file('image');
            $fileName   = time() . '.' . $image->getClientOriginalExtension();
            $image_path = Storage::disk('public')->put('images', $data['image'], 'public');
            $data['image'] = $image_path;
            $activity = Activity::create($data);
            $users = User::where('role', '!=', 'ADMIN')->where('deleted_at', null)->get();
            foreach($users as $user){
                UserActivity::create([
                    'activity_id' => $activity->id,
                    'user_id' => $user->id,
                    'date' => $data['date'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'image' => $image_path
                ]);
            }
            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'successfully create activity',
                'data' => []
            ]);
        }else{
            return response()->json([
                'code' => 403,
                'error' => true,
                'message' => 'Access Denied',
                'data' => []
               ]);
        }
    }

    function getGlobalActivities(Request $request)
    {
        if(isset($request['activity_id'])){
            $activity = Activity::where('id', $request['activity_id'])->where('deleted_at', null)->first();
            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'successfully fetched activity',
                'data' => $activity
            ]);
        }

        $activities = Activity::where('deleted_at', null)->orderBy('created_at', 'desc')->get();
        return response()->json([
            'code' => 200,
            'error' => false,
            'message' => 'successfully fetched activities',
            'data' => $activities
        ]);
    }

    function getGlobalActivityList(){
        $activity = Activity::where('deleted_at', null)->get(['id', 'title']);
            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'successfully fetched activity',
                'data' => $activity
            ]);
    }

    function updateGlobalActivities(Request $request){
        $data = $request->validate([
            'activity_id' => 'required|integer|exists:activities,id',
            'title' => 'string',
            'description' => 'string'
        ]);

        if(Auth::user()->role == 'ADMIN'){

            if(isset($request['image'])){
                $request->validate([
                    'image' => 'file|mimes:jpeg,bmp,png,jpg'
                ]);
                $image      = $request->file('image');
                $fileName   = time() . '.' . $image->getClientOriginalExtension();
                $image_path = Storage::disk('public')->put('images', $data['image'], 'public');
                $activity =  [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'image' => $image_path
               ];
            }else{
                $activity =  [
                    'title' => $data['title'],
                    'description' => $data['description']
               ];
            }
            Activity::where('id', $data['activity_id'])->update($activity);
            $users = User::where('role', '!=', 'ADMIN')->where('deleted_at', null)->get();
            foreach($users as $user){
                UserActivity::where('activity_id', $data['activity_id'])->update($activity);
            }

            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'successfully updated activity',
                'data' => []
            ]);
        }else{
            return response()->json([
                'code' => 403,
                'error' => true,
                'message' => 'Access Denied',
                'data' => []
               ]);
        }
    }

    function deleteGlobalActivity(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:activities,id',
        ]);

        if(Auth::user()->role == 'ADMIN'){
            Activity::where('id', $data['id'])->update([
                'deleted_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            UserActivity::where('activity_id', $data['id'])->update([
                'deleted_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'successfully deleted activity',
                'data' => []
            ]);
        }else{
            return response()->json([
                'code' => 403,
                'error' => true,
                'message' => 'Access Denied',
                'data' => []
               ]);
        }
    }
}
