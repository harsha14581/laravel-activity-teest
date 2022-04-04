<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserActivity;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Activity;

class UserController extends Controller
{
    function login(Request $request){
        $credentials =  $request->validate([
             'email_id' => 'required|string|email',
             'password' => 'required|string' 
         ]);
 
         if (Auth::attempt($credentials)) {
             return response()->json([
                 'code' => 200,
                 'error' => false,
                 'message' => 'Successfully logged in',
                 'data' => [[
                     'access_token' => Auth::user()->createToken(Str::random(50))->accessToken,
                     'user_details' => Auth::user()
                 ]]
             ]);
         }else{
             return response()->json([
                 'code' => 422,
                 'error' => true,
                 'message' => 'Invalid credentials',
                 'data' => []
             ]);
         }
     }


    function register(Request $request){
        $data = $request->validate([
            'email_id' => 'required|string|email|unique:users,email_id',
            'name' => 'required|string',
            'password' => 'required|string'
        ]);
        
        $data['password'] = bcrypt($data['password']);
        $data['role'] = 'USER';
        User::create($data);
        return response()->json([
            'code' => 200,
            'error' => false,
            'message' => 'successfully create user',
            'data' => []
        ]);
    }


    function getUserActivityByDates(Request $request){
        if(Auth::user()->role != 'ADMIN'){
            $request->validate([
                'start_date' => 'required|string|date',
                'end_date' => 'required|string|date'
            ]);
            $start_date = $request['start_date'];
            $end_date = $request['end_date'];
            $formated_start_date = date('Y-m-d',strtotime($start_date));
            $formated_end_date = date('Y-m-d', strtotime($end_date));
            $user_activities = UserActivity::whereDate('date','>=', $formated_start_date)->whereDate('date', '<=', $formated_end_date)->where('user_id', Auth::user()->id)->get();
            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'successfully fetched user activites',
                'data' => $user_activities
            ]);
        }else{
            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'successfully fetched user activites',
                'data' => []
            ]);
        }
    }


    function getUserActivities(Request $request)
    {
        if(isset($request['id'])){
            $user_activity = UserActivity::where('id', $request['id'])->where('deleted_at', null)->first();
            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'user activites',
                'data' => $user_activity
            ]);
        }
        if(Auth::user()->role == 'ADMIN'){
            $user_activities = UserActivity::with('user')->where('deleted_at', null)->orderBy('created_at', 'desc')->get();
            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'user activites',
                'data' => $user_activities
            ]);
        }
    }

    function updateUserActivities(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:user_activities,id',
            'user_id' => 'required|integer|exists:user_activities,user_id',
            'title' => 'string',
            'description' => 'string'
        ]);
        
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

        UserActivity::where('id', $data['id'])->where('user_id',$data['user_id'])->update($activity);
        return response()->json([
            'code' => 200,
            'error' => false,
            'message' => 'Successfully updated user activity',
            'data' => []
        ]);
    }


    function deleteUserActivities(Request $request)
    {
       $data =  $request->validate([
            'id' => 'required|integer|exists:user_activities,id',
            'user_id' => 'required|integer|exists:user_activities,user_id'
        ]);


        UserActivity::where('id', $data['id'])->where('user_id',$data['user_id'])->update([
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        return response()->json([
            'code' => 200,
            'error' => false,
            'message' => 'Successfully deleted user activity',
            'data' => []
        ]);
    }

    function getUserNames()
    {
        if(Auth::user()->role == 'ADMIN'){
            $users = User::where('role', '!=', 'ADMIN')->where('deleted_at', null)->get(['id','name']);
            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'Successfully fetched usernames',
                'data' => $users
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

    function createUserActivity(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'activity_id' => 'required|integer'
        ]);

        if(Auth::user()->role == 'ADMIN'){
           $activity = Activity::where('id', $data['activity_id'])->first();
           if(empty($activity)){
               return response()->json([
                'code' => 422,
                'error' => true,
                'message' => 'Invalid data',
                'data' => []
             ]);
           }

            $user_activity = UserActivity::where('activity_id', $data['activity_id'])->first();
            
            if(empty($user_activity)){
                return response()->json([
                    'code' => 422,
                    'error' => true,
                    'message' => 'Cannot create same activity again.',
                    'data' => []
                ]);
            }

            UserActivity::create([
                'user_id' => $data['user_id'],
                'activity_id' => $activity['id'],
                'date' => $activity['date'],
                'title' => $activity['title'],
                'description' => $activity['description'],
                'image' => $activity['image']
            ]);
            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'successfully create user activity',
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
