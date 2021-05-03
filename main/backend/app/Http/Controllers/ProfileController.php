<?php

namespace App\Http\Controllers;

use App\Helpers\ExceptionHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ProfileController extends Controller
{

    /**
     * ProfileController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            // Get the values that should be updated.
            $updates = $request->updates;
            if (!is_array($updates) || count($updates) === 0) {
                return ExceptionHelper::customSingleError('Invalid data provided.', 400);
            }

            // Whitelist of values that may be updated.
            $whitelist = [
                'name' => 'name',
            ];

            // Check that only whitelisted will be updated.
            $error = FALSE;
            foreach ($updates as $key => $value) {
                if (!array_key_exists($key, $whitelist)) {
                    $error = TRUE;
                    break;
                }

                $user->$key = $value;
            }
            if ($error) {
                return ExceptionHelper::customSingleError('Some of the provided fields may not be updated.', 401);
            }

            $user->save();
            return $user;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }


}
