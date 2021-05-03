<?php

namespace App\Helpers;

/**
 * Class to help with throwing exeptions.
 */
class ExceptionHelper
{

    /**
     * Return a custom error message to the client.
     *
     * @param string $error
     *   The custom error message.
     * @param int $status_code
     *   The status code.
     *
     * @return \Illuminate\Http\JsonResponse
     *   The response Object.
     */
    public static function customSingleError(string $error,int $status_code)
    {
        return response()->json(
            [
                "error" => $error
            ],
            $status_code
        );
    }
}
