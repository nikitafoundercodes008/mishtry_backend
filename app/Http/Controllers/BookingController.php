<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    
public function paidbooking(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:user_details,id',
        'quantity' => 'nullable',
        'service_id' => 'required',
        'address' => 'required|string',
        'description' => 'required|string',
        'booking_date' => 'required',
        'price' => 'nullable',
        'discount' => 'nullable',
        'sub_total' => 'nullable',
        'tax' => 'nullable',
        'total_amount' => 'nullable',
        'payment_through' => 'nullable|in:1,2',
        'handyman_id' => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
            'message' => 'Booking Not Created',
        ], 200);
    }

    // Generate a random transaction ID
    $date = date('YmdHis');
    $rand = rand(11111, 99999);
    $transactionId = $date . $rand;

    if ($request->payment_through == 1) {
        $userId = $request->user_id;
        $orderId = $transactionId;  // Using the generated transaction ID

        // Get user details
        $user = DB::table('user_details')->where('id', $userId)->first();

        if ($user) {
            // Prepare parameters for the external payment API request
           $cash = round($request->total_amount);
           
            $postParameter = [
                'merchantid' => "INDIANPAY00INDIANPAY0066",
                'orderid' => $orderId,
                'amount' => $cash,
                'name' => $user->full_name,
                'email' => $user->email,
                'mobile' => $user->phone,
                'remark' => 'payIn',
                'type' => $cash,
                'redirect_url' =>"https://handyman.mobileappdemo.net/api/check_payment?transaction_id=$transactionId"
            ];

            // Call payment gateway via cURL
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://indianpay.co.in/admin/paynow', // Payment gateway API URL
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postParameter),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Cookie: ci_session=1ef91dbbd8079592f9061d5df3107fd55bd7fb83' // If necessary, replace with correct session or token
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($response, true);
            // dd($response);
        //  dd($response); die();
            // Check if payment link is returned from the payment gateway
            if (isset($response['payment_link']) && !empty($response['payment_link'])) {
                // Create booking record with transaction_id
                $booking = Booking::create(array_merge($request->all(), ['transaction_id' => $transactionId, 
                                    'redirect_url' => "https://handyman.mobileappdemo.net/api/check_payment?transaction_id=$transactionId" ]));
        
                return response()->json([
                    'message' => 'Booking created successfully!',
                    'success' => true,
                    'payment_link' => $response['payment_link']
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway error, no payment link returned!'
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found!'
            ], 200);
        }
    }

if ($request->payment_through == 2) {
    $userId = $request->user_id;
    $totalAmount = $request->total_amount;

    $user = DB::table('user_details')->where('id', $userId)->first();

    if ($user) {
        // Check if wallet balance is sufficient
        if ($user->wallet_amount >= $totalAmount) {
            // Create booking record with transaction_id
            $booking = Booking::create(array_merge($request->all(), ['transaction_id' => $transactionId]));

            // Deduct the wallet amount
            $user->wallet_amount -= $totalAmount;
            DB::table('user_details')->where('id', $userId)->update(['wallet_amount' => $user->wallet_amount]);

            // Update transaction status in bookings table
            DB::table('bookings')->where('id', $booking->id)->update(['transaction_status' => 2]);

            return response()->json([
                'message' => 'Booking created successfully and wallet amount deducted!',
                'success' => true,
                'data' => $booking
            ], 200);
        } else {
            // Insufficient wallet balance
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance. Please recharge your wallet.'
            ], 200);
        }
    } else {
        return response()->json([
            'success' => false,
            'message' => 'User not found!'
        ], 200);
    }
}

// If no payment is required, simply create the booking and set status to successful
$booking = Booking::create(array_merge($request->all(), ['transaction_id' => $transactionId]));

// If no payment was made, set the transaction status to successful
DB::table('bookings')->where('id', $booking->id)->update(['transaction_status' => 2]);

return response()->json([
    'message' => 'Booking created successfully!',
    'success' => true,
    'data' => $booking
], 200);
}



    public function checkPayment(Request $request)
    {
     
        $orderid = $request->input('transaction_id');
	
        if ($orderid == "") {
            return response()->json(['status' => 400, 'message' => 'Transaction Id is required']);
        } else {
            $match_order = DB::table('bookings')->where('transaction_id', $orderid)->where('transaction_status', 1)->first();

            if ($match_order) {
                $uid = $match_order->user_id;
                
                $cash = $match_order->total_amount;
                
               
                $orderid = $match_order->transaction_id;
                 $datetime=now();
             
                 //UPDATE orders SET status='1' WHERE transaction_id='$orderid'
                //  DB::table('orders')->where('transaction_id', $orderid)->update(['status' => 1]); 
              $update_payin = DB::table('bookings')->where('transaction_id', $orderid)->where('transaction_status', 1)->where('user_id', $uid)->update(['transaction_status' => 2]);
    
                if ($update_payin) {
                   
                return redirect()->away('https://handyman.mobileappdemo.net/public/payment_success.php');
                    
                } else {
                    return response()->json(['success' => false, 'message' => 'Failed to update payment status!'],200);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Order id not found or already processed'],200);
            }
        }
    }






public function showBooking($userId)
{
    if (!is_numeric($userId)  || $userId <= 0 ) {
        return response()->json([
            'success'=>false,
            'error' => 'Invalid input data'], 200);
    }
    
    $services = DB::table('bookings')
        ->join('services', 'bookings.service_id', '=', 'services.id') 
        ->join('providers_details', 'services.provider_id', '=', 'providers_details.id')
        ->leftJoin('providers_details as handyman', 'bookings.handyman_id', '=', 'handyman.id')
        ->where('bookings.user_id', $userId)
        ->orderBy('bookings.id', 'desc')  
        ->select(
            'bookings.id',
            'bookings.user_id',
            'bookings.service_id',
            'bookings.booking_date',
            'bookings.status',
            'bookings.payment_through',
            'bookings.price',
            'bookings.discount',
            'bookings.sub_total',
            'bookings.tax',
            'bookings.total_amount',
            'services.name as service_name',  
            'services.image as service_image', 
            'providers_details.id as provider_id',
            'providers_details.phone_number as provider_number',
            'providers_details.full_name as provider_name', 
            'providers_details.image as provider_image',
            'providers_details.email',
            'providers_details.address',
            'providers_details.phone_number',
            'handyman.full_name as handyman_name',
            'handyman.image as handyman_image'
            
        )
        ->get();

    if ($services->isEmpty()) {
        return response()->json(['message' => 'No bookings found for the given user and service.'], 200);  
    }

    return response()->json([
        'success'=>true,
        'message' => 'Booking details retrieved successfully.',
        'data' => $services
    ], 200);
}


    
    
    
    
      
public function walletrecharge(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:user_details,id',
        'balance' => 'required|numeric', // Ensure balance is a positive number
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
            'message' => 'Wallet recharge not done',
        ], 200);
    }

    // Generate a random transaction ID
    $date = date('YmdHis');
    $rand = rand(11111, 99999);
    $transactionId = $date . $rand;

    // Get user details
    $userId = $request->user_id;
    $balanceToAdd = intval($request->balance);
    $user = DB::table('user_details')->where('id', $userId)->first();
    

    if ($user) {
        
        // Prepare parameters for the external payment API request
        $postParameter = [
            'merchantid' => "04",
            'orderid' => $transactionId,
            'amount' => $balanceToAdd,
            'name' => $user->full_name,
            'email' => $user->email,
            'mobile' => $user->phone,
            'remark' => 'payIn',
            'type' => $balanceToAdd,
            'redirect_url' =>'https://handyman.mobileappdemo.net' . "api/checkPayment?order_id=$transactionId"
        ];

        // Call payment gateway via cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://indianpay.co.in/admin/paynow', // Payment gateway API URL
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postParameter),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Cookie: ci_session=739qplpmk816i1sq0v0lj6uod8q498d5'
            ],
        ]);
        
        
        
        $response = curl_exec($curl);
        curl_close($curl);
        

        $response = json_decode($response, true);
            

        // Check if payment link is returned from the payment gateway
        if (isset($response['payment_link']) && !empty($response['payment_link'])) {
            // Insert the booking record with transaction_id, initially with status 1 (Pending)
            $data = array_merge($request->all(), ['transaction_id' => $transactionId,]);
        
            DB::table('payins')->insert($data);

            return response()->json([
                'message' => 'Wallet recharge initiated. Please complete the payment.',
                'success' => true,
                'payment_link' => $response['payment_link']
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway error, no payment link returned!'
            ], 200);
        }
    } else {
        return response()->json([
            'success' => false,
            'message' => 'User not found!'
        ], 200);
    }
}




public function walletcheckPayment(Request $request)
{
    $transactionId = $request->input('transaction_id');

    // Check if payment is successful by fetching the status from payins table
    $payment = DB::table('payins')->where('transaction_id', $transactionId)->first();

    if ($payment) {
        
        if ($payment->status == 1) {
            // Update the payment status to '2' (successful)
            DB::table('payins')->where('transaction_id', $transactionId)->update([
                'status' => 2
            ]);
        }

        // Check if the payment status is '2' (payment is successful)
        if ($payment->status == 2) {
            // Update wallet balance for the user
            $user = DB::table('user_details')->where('id', $payment->user_id)->first();

            if ($user) {
                $newWalletAmount = $user->wallet_amount + $payment->balance;
                DB::table('user_details')->where('id', $user->id)->update([
                    'wallet_amount' => $newWalletAmount
                ]);

                return response()->json([
                    'message' => 'Payment successful! Wallet recharged.',
                    'success' => true,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found!'
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed or not completed yet.'
            ], 200);
        }
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Transaction not found.'
        ], 200);
    }
}


public function rechargehistory($userId)
{
    
    $payins = DB::table('payins')->where('user_id', $userId)->orderBy('created_at', 'desc')->get(['balance', 'created_at', 'status']);

    if ($payins->isEmpty()) {
        return response()->json(['message' => 'Payin record not found'], 200);
    } else {
        return response()->json([
            'success' => true,
            'data' => $payins, 
        ], 200);
    }
}

public function paidbookingFive(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:user_details,id',
        'quantity' => 'nullable',
        'service_id' => 'required',
        'address' => 'required|string',
        'description' => 'required|string',
        'booking_date' => 'required',
        'price' => 'nullable',
        'discount' => 'nullable',
        'sub_total' => 'nullable',
        'tax' => 'nullable',
        'total_amount' => 'nullable',
        'payment_through' => 'nullable|in:1,2',
        'handyman_id' => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
            'message' => 'Booking Not Created',
        ], 200);
    }

    // Generate a random transaction ID
    $date = date('YmdHis');
    $rand = rand(11111, 99999);
    $transactionId = $date . $rand;

    if ($request->payment_through == 1) {
        $userId = $request->user_id;
        $orderId = $transactionId;  // Using the generated transaction ID

        // Get user details
        $user = DB::table('user_details')->where('id', $userId)->first();

        if ($user) {
            // Prepare parameters for the external payment API request
           $cash = round($request->total_amount);
           
            $postParameter = [
                'merchantid' => "INDIANPAY00INDIANPAY0066",
                'orderid' => $orderId,
                'amount' => $cash,
                'name' => $user->full_name,
                'email' => $user->email,
                'mobile' => $user->phone,
                'remark' => 'payIn',
                'type' => $cash,
                'redirect_url' =>"https://handyman.mobileappdemo.net/api/check_payment?transaction_id=$transactionId"
            ];

            // Call payment gateway via cURL
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://indianpay.co.in/admin/paynow', // Payment gateway API URL
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postParameter),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Cookie: ci_session=1ef91dbbd8079592f9061d5df3107fd55bd7fb83' // If necessary, replace with correct session or token
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($response, true);
            // dd($response);
        //  dd($response); die();
            // Check if payment link is returned from the payment gateway
            if (isset($response['payment_link']) && !empty($response['payment_link'])) {
                // Create booking record with transaction_id
                $booking = Booking::create(array_merge($request->all(), ['transaction_id' => $transactionId, 
                                    'redirect_url' => "https://handyman.mobileappdemo.net/api/check_payment?transaction_id=$transactionId" ]));
        
                return response()->json([
                    'message' => 'Booking created successfully!',
                    'success' => true,
                    'payment_link' => $response['payment_link']
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway error, no payment link returned!'
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found!'
            ], 200);
        }
    }

if ($request->payment_through == 2) {
    $userId = $request->user_id;
    $totalAmount = $request->total_amount;

    $user = DB::table('user_details')->where('id', $userId)->first();

    if ($user) {
        // Check if wallet balance is sufficient
        if ($user->wallet_amount >= $totalAmount) {
            // Create booking record with transaction_id
            $booking = Booking::create(array_merge($request->all(), ['transaction_id' => $transactionId]));

            // Deduct the wallet amount
            $user->wallet_amount -= $totalAmount;
            DB::table('user_details')->where('id', $userId)->update(['wallet_amount' => $user->wallet_amount]);

            // Update transaction status in bookings table
            DB::table('bookings')->where('id', $booking->id)->update(['transaction_status' => 2]);

            return response()->json([
                'message' => 'Booking created successfully and wallet amount deducted!',
                'success' => true,
                'data' => $booking
            ], 200);
        } else {
            // Insufficient wallet balance
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance. Please recharge your wallet.'
            ], 200);
        }
    } else {
        return response()->json([
            'success' => false,
            'message' => 'User not found!'
        ], 200);
    }
}

// If no payment is required, simply create the booking and set status to successful
$booking = Booking::create(array_merge($request->all(), ['transaction_id' => $transactionId]));

// If no payment was made, set the transaction status to successful
DB::table('bookings')->where('id', $booking->id)->update(['transaction_status' => 2]);

return response()->json([
    'message' => 'Booking created successfully!',
    'success' => true,
    'data' => $booking
], 200);
}

}


