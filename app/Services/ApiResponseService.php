<?php

namespace App\Services;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApiResponseService
{
    /**
     * @param $permission
     * @return Application|RedirectResponse|Redirector|true
     */
    public static function noPermissionThenRedirect($permission)
    {
        if (!Auth::user()->can($permission)) {
            return redirect(route('home'))->withErrors([
                'message' => trans("You Don't have enough permissions")
            ])->send();
        }
        return true;
    }

    /**
     * @param $permission
     * @return true
     */
    public static function noPermissionThenSendJson($permission)
    {
        if (!Auth::user()->can($permission)) {
            self::errorResponse("You Don't have enough permissions");
        }
        return true;
    }


    /**
     * If User don't have any of the permission that is specified in Array then Json Response will be sent
     * @param array $permissions
     * @return true
     */
    public static function noAnyPermissionThenSendJson(array $permissions)
    {
        if (!Auth::user()->canany($permissions)) {
            self::errorResponse("You Don't have enough permissions");
        }
        return true;
    }

    /**
     * @param string|null $message
     * @param null $data
     * @param array $customData
     * @param null $code
     * @param string|null $redirectUrl
     * @return void
     */
    public static function successResponse(string|null $message = "Success", $data = null, array $customData = array(), $code = null, string|null $redirectUrl = null): void
    {
        $response = [
            'error'   => false,
            'message' => trans($message),
            'data'    => !empty($data) ? $data : (object)array(),
            'code'    => $code ?? config('constants.RESPONSE_CODE.SUCCESS')
        ];

        if ($redirectUrl) {
            $response['redirect_url'] = $redirectUrl;
        }

        response()->json(array_merge($response, $customData), $code ?? config('constants.RESPONSE_CODE.SUCCESS'))->send();
        exit();
    }

    /**
     *
     * @param string $message - Pass the Translatable Field
     * @param null $data
     * @param string $code
     * @param null $e
     * @param string|null $redirectUrl
     * @return void
     */
    public static function errorResponse(string $message = 'Error Occurred', $data = null, $code=null, $exception=null, string|null $redirectUrl = null)
    {
        $response = [
            'error'   => true,
            'message' => trans($message),
            'data'    => !empty($data) ? $data : (object)array(),
            'code'    => $code ?? config('constants.RESPONSE_CODE.ERROR')
        ];

        if ($redirectUrl) {
            $response['redirect_url'] = $redirectUrl;
        }

        if (config('app.debug') && !empty($exception) && is_object($exception)) {
            $response['debug'] = [
                'message'=> $exception->getMessage(),
                'file'=> $exception->getFile(),
                'line'=> $exception->getLine(),
                'trace'=> $exception->getTrace()
            ];
        }
        response()->json($response, $code ?? config('constants.RESPONSE_CODE.ERROR'))->send();
        exit();
    }


    /**
     * @param string $message
     * @param null $data
     * @return void
     */
    public static function validationError(string $message = 'Error Occurred', $data = null)
    {
        self::errorResponse($message, $data, config('constants.RESPONSE_CODE.VALIDATION_ERROR'));
    }

    /**
     * Log an exception to the system logs
     *
     * @param \Throwable|\Exception $e The exception to log
     * @param string $logMessage Additional context for the log
     * @return void
     */
    public static function logErrorResponse($e, string $logMessage = 'Error occurred')
    {
        Log::error($logMessage . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    public static function unauthorizedResponse($message = "Unauthorized.")
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], 403);
    }
}
