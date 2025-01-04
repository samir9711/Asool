<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Traits\GeneralTrait;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\WorkUser;



class AuthController extends Controller
{
    //
    use GeneralTrait;
    public function register(Request $request)
    {

        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'number' => 'required|string|unique:users',
        ]);


        if ($validatedData->fails()) {
            return $this->apiResponse(null, false, $validatedData->errors()->first(), 400);
        }


        $verificationCode = rand(100000, 999999);


        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'number' => $request->input('number'),
        ]);


        $expiresAt = now()->addMinutes(30);


        DB::table('email_verification_codes')->insert([
            'email' => $request->input('email'),
            'verification_code' => $verificationCode,
            'expires_at' => $expiresAt,
        ]);


        try {
            Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));
        } catch (\Exception $e) {
            \Log::error('Mail sending error: ' . $e->getMessage());

            return $this->apiResponse(null, false, 'Failed to send verification email.', 500);
        }


        return $this->apiResponse(null, true, 'User registered successfully. Please verify your email.', 201);
    }



public function login(Request $request)
{
    $validatedData = Validator::make($request->all(), [
        'email' => 'required|string|email',
        'password' => 'nullable|required_without:verification_code',
        'verification_code' => 'nullable|required_without:password|string',
    ]);

    if ($validatedData->fails()) {
        return $this->requiredField($validatedData->errors()->first());
    }

    try {
        $user = User::where('email', $request->input('email'))->first();


        if (!$user) {
            return $this->apiResponse(null, false, 'Invalid email or password.', 400);
        }


        if (!$user->is_verified) {
            return $this->apiResponse(null, false, 'Please verify your email before logging in.', 403);
        }


        if ($request->filled('password')) {

            if (!Hash::check($request->input('password'), $user->password)) {
                return $this->apiResponse(null, false, 'Invalid email or password.', 400);
            }
        } elseif ($request->filled('verification_code')) {

            $verificationRecord = DB::table('email_verification_codes')
                                    ->where('email', $request->input('email'))
                                    ->where('verification_code', $request->input('verification_code'))
                                    ->first();

            if (!$verificationRecord) {
                return $this->apiResponse(null, false, 'Invalid verification code.', 400);
            }


            if ($verificationRecord->expires_at < now()) {
                return $this->apiResponse(null, false, 'Verification code has expired.', 400);
            }


            $user->is_verified = true;
            $user->save();


            DB::table('email_verification_codes')->where('email', $request->input('email'))->delete();
        }


        $token = $user->createToken('apiToken')->plainTextToken;


        $data['user'] = new UserResource($user);
        $data['token'] = $token;
        $data['message'] = 'User logged in successfully';

        return $this->apiResponse($data, true, null, 200);

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
}


    public function logout(Request $request){
        try{
            $user=auth()->user();
            if($user){
                $user->tokens()->delete();
            }
            $data['massage']='user has logged out successfully';
            return $this->apiResponse($data,true,null,200);
        }
        catch(\Exception $ex){
            return $this->apiResponse(null,false,$ex->getMessage(),500);
        }
    }


    public function verify(Request $request)
{

    $validatedData = Validator::make($request->all(), [
        'email' => 'required|string|email',
        'verification_code' => 'required|string',
    ]);

    if ($validatedData->fails()) {
        return $this->apiResponse(null, false, $validatedData->errors()->first(), 400);
    }


    $verificationRecord = DB::table('email_verification_codes')
                            ->where('email', $request->input('email'))
                            ->where('verification_code', $request->input('verification_code'))
                            ->first();


    if (!$verificationRecord) {
        return $this->apiResponse(null, false, 'Invalid verification code or email.', 400);
    }


    if ($verificationRecord->expires_at < now()) {
        return $this->apiResponse(null, false, 'Verification code has expired.', 400);
    }


    $user = User::where('email', $request->input('email'))->first();


    if ($user) {

        $user->is_verified = true;
        $user->save();


        DB::table('email_verification_codes')->where('email', $request->input('email'))->delete();


        return $this->apiResponse(null, true, 'Email verified successfully.', 200);
    }


    return $this->apiResponse(null, false, 'User not found.', 400);
}



public function requestPasswordReset(Request $request)
{

    $validatedData = Validator::make($request->all(), [
        'email' => 'required|string|email|exists:users,email',
    ]);

    if ($validatedData->fails()) {
        return $this->apiResponse(null, false, $validatedData->errors()->first(), 400);
    }


    $user = User::where('email', $request->input('email'))->first();
    $resetToken = Str::random(60);


    $expiresAt = now()->addHours(1);
    DB::table('password_resets')->insert([
        'email' => $request->input('email'),
        'token' => $resetToken,
        'expires_at' => $expiresAt,
    ]);


    try {
        Mail::to($user->email)->send(new PasswordResetMail($resetToken, $user));
    } catch (\Exception $e) {
        \Log::error('Mail sending error: ' . $e->getMessage());
        return $this->apiResponse(null, false, 'Failed to send password reset email.', 500);
    }

    return $this->apiResponse(null, true, null, 200);
}


public function resetPassword(Request $request)
{

    $validatedData = Validator::make($request->all(), [
        'token' => 'required|string',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validatedData->fails()) {
        return $this->apiResponse(null, false, $validatedData->errors()->first(), 400);
    }


    $resetRequest = DB::table('password_resets')
                        ->where('token', $request->input('token'))
                        ->first();

    if (!$resetRequest) {
        return $this->apiResponse(null, false, 'Invalid or expired reset token.', 400);
    }


    if ($resetRequest->expires_at < now()) {
        return $this->apiResponse(null, false, 'Password reset token has expired.', 400);
    }


    $user = User::where('email', $resetRequest->email)->first();
    if ($user) {
        $user->password = Hash::make($request->input('password'));
        $user->save();


        DB::table('password_resets')->where('token', $request->input('token'))->delete();

        return $this->apiResponse(
            ['email' => $user->email],
            true,
            null,
            200
        );
    }

    return $this->apiResponse(null, false, 'User not found.', 400);
}
/***************************************************************************** */
//Dashbourd for work User
public function loginDashboard(Request $request)
{

    $data = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);


    $user = WorkUser::where('username', $data['username'])->first();


    if (!$user || !Hash::check($data['password'], $user->password)) {
        return $this->apiResponse(null, false, 'Invalid credentials', 401);
    }


    $existingToken = $user->tokens()->first();
    if ($existingToken) {
        return $this->apiResponse(null, false, 'User is already logged in', 403);
    }


    $token = $user->createToken('WorkUserToken')->plainTextToken;

    
    return $this->apiResponse([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'type' => $user->type,
        ],
    ], true, null, 200);
}






}
