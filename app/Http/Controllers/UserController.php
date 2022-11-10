<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login()
    {
      if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
          $user = Auth::user();
          $success['token'] = $user->createToken('appToken')->accessToken;
          //After successfull authentication, notice how I return json parameters
          return response()->json([
            'success' => true,
            'token' => $success,
            'user' => $user
        ]);
      } else {
        //if authentication is unsuccessfull, notice how I return json parameters
          return response()->json([
            'success' => false,
            'message' => 'Invalid Email or Password',
        ], 401);
      }
    }

    /**
     * Register api.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'document' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);
        
        if ($validator->fails()) {
          return response()->json([
            'success' => false,
            'message' => $validator->errors(),
          ], 401);
        }

        return DB::transaction(function() use($request){
          $input = $request->all();
          $input['password'] = bcrypt($input['password']);
          $user = User::create($input);
          $success['token'] = $user->createToken('appToken')->accessToken;
            return response()->json([
              'success' => true,
              'token' => $success,
              'user' => $user
          ]);
        });
    }

    public function logout(Request $res)
    {
      return response()->json([
        'success' => true,
        'message' => 'Logout successfully'
      ]);

      if (Auth::user()) {
        $user = Auth::user()->token();
        $user->revoke();

        return response()->json([
          'success' => true,
          'message' => 'Logout successfully'
      ]);
      }else {
        return response()->json([
          'success' => false,
          'message' => 'Unable to Logout'
        ]);
      }
     }
}
