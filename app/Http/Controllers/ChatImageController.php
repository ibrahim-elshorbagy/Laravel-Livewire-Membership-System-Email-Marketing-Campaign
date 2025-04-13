<?php

namespace App\Http\Controllers;

use App\Models\Admin\Support\SupportConversation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ChatImageController extends Controller
{
    public function show($userId, $filename)
    {
        // Validate the input parameters
        $validator = Validator::make([
            'userId' => $userId,
            'filename' => $filename
        ], [
            'userId' => [
                'required',
                'integer',
                'exists:users,id'
            ],
            'filename' => [
                'required',
                'string',
            ]
        ]);

        if ($validator->fails()) {
            abort(404, 'Invalid image parameters.');
        }

        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $path = "admin/support/{$userId}/{$filename}";

        // Authorization checks
        if (!$user->hasRole('admin') && $user->id != $userId) {
            $hasAccess = SupportConversation::where('user_id', $userId)
                ->whereHas('ticket', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->exists();

            if (!$hasAccess) {
                abort(403, 'You do not have permission to access this image.');
            }
        }

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'The requested image could not be found.');
        }

        return response()->file(Storage::disk('local')->path($path));
    }
}
