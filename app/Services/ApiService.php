<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiService {
    public static function validateRequest(Request $request, array $rules) {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseService::validationError($validator->errors()->first());
        }
    }

    public static function verifyFirebaseToken(string $token) {
        try{
            $verifiedToken = HelperService::verifyToken($token);
            if (empty($verifiedToken)) {
                APiResponseService::errorResponse('Invalid Firebase token');
            }
            return $verifiedToken;
        }catch(\Exception $e){
            ApiResponseService::errorResponse('Invalid Firebase token');
        }
    }

    public static function removeUserFromFirebase(string $token) {
        $verifiedToken = self::verifyFirebaseToken($token); // Verify token
        $firebaseId = $verifiedToken->claims()->get('sub');
        HelperService::removeUserFromFirebase($firebaseId); // Remove user from firebase
        return $firebaseId;
    }


    public static function getGeneralSystemSettings() {
        $settings = HelperService::systemSettings(array('system_color','currency_code','currency_symbol', 'tax_type'));
        $settings['active_payment_settings'] = self::getActivePaymentDetails();
        return $settings;
    }

    public static function getActivePaymentDetails() {
        // Get all payment gateway statuses and keys
        $paymentSettings = HelperService::systemSettings(array(
            'razorpay_status', 
            'stripe_status',
            'flutterwave_status'
        ));
        
        $paymentDetails = [];
        $counter = 0;
        
        // Check Razorpay
        if(!empty($paymentSettings['razorpay_status']) && $paymentSettings['razorpay_status'] == 1){
            $paymentDetails[$counter] = array(
                'payment_gateway' => 'razorpay'
            );
            $counter++;
        }
        
        // Check Stripe
        if(!empty($paymentSettings['stripe_status']) && $paymentSettings['stripe_status'] == 1){
            $paymentDetails[$counter] = array(
                'payment_gateway' => 'stripe'
            );
            $counter++;
        }
        
        // Check Flutterwave
        if(!empty($paymentSettings['flutterwave_status']) && $paymentSettings['flutterwave_status'] == 1){
            $paymentDetails[$counter] = array(
                'payment_gateway' => 'flutterwave'
            );
            $counter++;
        }
        
        return $paymentDetails;
    }
}

