<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try{

            $request->validate([
                'name' => ['required','string','max:255'],
                'email' => ['required','string','email','max:255', 'unique:users'],
                'username' => ['required','string','max:255', 'unique:users'],
                'phone' => ['nullable','string','max:255'],
                'password' => ['required','string', Password::min(6)
                                                    ->mixedCase()
                                                    ->numbers()
                                                    ->symbols()]
            ]);

            User::create([
                'name' => $request->name,
                'email'=> $request->email,
                'username'=> $request->username,
                'phone'=> $request->phone,
                'password'=> Hash::make($request->password)
            ]);


            $user = User::where('email', $request->email)->first();

            $token_result = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
               'access_token'=> $token_result,
               'token_type'=> 'Bearer',
               'user'=> $user 
            ], 'User Registered'
            );
        } catch (ValidationException $validationError) {
            // Tangani kesalahan validasi
            return ResponseFormatter::error([
                'message' => 'Validation Failed',
                'errors' => $validationError->errors(),
            ], 'Validation Error', 422);
            
        }catch(Exception $error)
        {
            return ResponseFormatter::error([
                'Message' => 'Something went wrong',
                'error' => $error
             ], 'Authentication Failed', 500
             );
        }
    }

    public function login(Request $request)
    {
        try
        {
            $request -> validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credential = request(['email','password']);

            if(!Auth::attempt($credential)){
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();

            if(!Hash::check($request->password, $user->password,[])){
                throw new \Exception('Invalid Credential');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token'=>$tokenResult,
                'token_type'=>'Bearer',
                'user'=>$user
            ],'Authenticated');

        } catch(Exception $error)
        {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error'=> $error->getMessage()
            ], 'Authentication Failed', 500);
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(), 'Data profil berhasil diambil'
        );
    }
}
