<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorMail;
use App\Models\Patient;
use App\Models\PaymentCompany;
use App\Models\User;
use App\Notifications\TwoFactorCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Validator;


class AuthController extends Controller
{

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {
        $email = request()->email;
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && !is_null($existingUser->code)) {
            $existingUser->delete();
        }

        $validator = Validator::make(request()->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users|regex:/^\+963\d{9}$/',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }


        $user = new User;
        $user->first_name = request()->first_name;
        $user->last_name = request()->last_name;
        $user->email = request()->email;
        $user->phone = request()->phone;
        $user->fcm_token = request()->fcm_token;
        $user->password = bcrypt(request()->password);
        $user->save();

        if (request()->enter_id) {
            $user->enter_id = request()->enter_id;
            $user->save();
            $patient = Patient::where('id', request()->enter_id)
                ->update([
                    'user_id' => $user->id
                ]);
            if (!$patient) {
                return response()->json(['message' => 'The patient not found you enter uncorect ID'], 404);
            }
        }
        $phone = $user->phone;
        // $paymentCompany = PaymentCompany::create([
        //     'user_id' => $user->id,
        //     'phone_number' => $user->phone,
        //     'company_name' => in_array($phone[5], ['9', '8']) || in_array(substr($phone, 5, 7), ['98,81,95,82,98,96,87,97']) ? "Syriatel_cash" : "MTN_Cash",
        //     'balance' => rand(2, 3) * 100000
        // ]);

        $user->generateCode();

        Mail::to($user->email)->send(new TwoFactorMail($user->code, $user->first_name));

        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $json = $this->respondWithToken($token)->getContent();
        // $user->fcm_token = $token;
        // $user->save();
        $array = json_decode($json, true);
        return response()->json(['token' => $array, 'user' => $user], 200);

    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();
        if ($user->role == 'doctor') {
            $user->doctor;
        }

        $fcmToken = request('fcm_token');
        $user->fcm_token = $fcmToken;
        $user->save();
        // $user->generateCode();

        // Mail::to($user->email)->send(new TwoFactorMail($user->code, $user->first_name));

        $json = $this->respondWithToken($token)->getContent();

        $array = json_decode($json, true);

        return response()->json(['token' => $array, 'user' => $user->load('patient')], 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // in this case I change the ttl from jwt config file and make it 1440
            // which mean the token expire in one day
            'expires_in' => auth()->factory()->getTTL() * 60,
            'expires_at' => Carbon::now()->addMinutes(auth()->factory()->getTTL())->toDateTimeString(),
        ]);
    }
}
