<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     */
    public function Verify(Request $request)
    {
        // $id = $request->route('id')->validate(['id' => 'required|numeric']);
        // $hash = $request->route('hash')->validate(['hash' => 'required|string']);
        $data = [
            'id' => $request->route('id'),
            'hash' => $request->route('hash'),
            'expires' => $request->query('expires'),
            'signature' => $request->query('signature'),
        ];

        $validator = Validator::make($data, [
            'id' => ['required', 'numeric'],
            'hash' => ['required', 'string'],
            'expires' => ['required', 'numeric'],
            'signature' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            abort(403, 'Invalid or tampered verification link: Wrong data format.');
        }

        // Retrieve parameters
        $id = $request->route('id');
        $hash = $request->route('hash');
        $expires = $request->query('expires');


        // For unauthenticated verification, we need to find the user by ID
        $user = User::findOrFail($id);

        // Check if the verification link has expired
        if ($expires < now()->timestamp) {
            abort(403, 'Verification link has expired.');
        }

        // Validate that the hash matches the user's email
        if (sha1($user->email) !== $hash) {
            abort(403, 'Invalid or tampered verification link.');
        }

        // Verify that the URL is a valid signed URL
        if (!URL::hasValidSignature($request)) {
            abort(403, 'Invalid verification link signature.');
        }

        if ($user->hasVerifiedEmail()) {
            Session::flash('success', 'Email Already Verified !');
            return redirect(route('login'));
        }

        // Mark email as verified and save the verification timestamp
        $user->email_verified_at = now();
        $user->save();

        Session::flash('success', 'Email Verified successfully!');
        return redirect(route('login'));
    }
}
