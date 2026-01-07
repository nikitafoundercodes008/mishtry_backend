<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class TestingController extends Controller
{
    
// public function test(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'user_id' => 'required|exists:user_details,id',
//         'quantity' => 'nullable',
//         'service_id' => 'required',
//         'address' => 'required|string',
//         'description' => 'required|string',
//         'booking_date' => 'required',
//         'price' => 'nullable',
//         'discount' => 'nullable',
//         'sub_total' => 'nullable',
//         'tax' => 'nullable',
//         'total_amount' => 'nullable',
//         'payment_through' => 'nullable|in:1,2',
//         'handyman_id' => 'nullable',
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'errors' => $validator->errors(),
//             'message' => 'Booking Not Created',
//         ], 200);
//     }

//     // Generate a random transaction ID
//     $date = date('YmdHis');
//     $rand = rand(11111, 99999);
//     $transactionId = $date . $rand;

//     if ($request->payment_through == 1) {
//         $userId = $request->user_id;
//         $orderId = $transactionId;  // Using the generated transaction ID

//         // Get user details
//         $user = DB::table('user_details')->where('id', $userId)->first();

//         if ($user) {
//             // Prepare parameters for the external payment API request
//             $cash = round($request->total_amount);

//             $postParameter = [
//                 'user_token' => "33f7aaa7331a12e3c69c2289329e5f77",
//                 'orderid' => "$transactionId",
//                 'amount' => $cash,
//                 'name' => $user->full_name,
//                 'email' => $user->email,
//                 'mobile' => $user->phone,
//                 'remark1' => 'testremark',
//                 'remark2' => 'testremark2',
//                 'type' => $cash,
//                 'redirect_url' => "https://handyman.mobileappdemo.net/api/check_payment?transaction_id=$transactionId"
//             ];

//             // API URL for the payment gateway
//             $api_url = 'https://niyope.com/api/create-order';   

//             // Initialize cURL session
//             $ch = curl_init($api_url);

//             // Set cURL options
//             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//             curl_setopt($ch, CURLOPT_POST, true);
//             curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postParameter)); // Use $postParameter
//             curl_setopt($ch, CURLOPT_HTTPHEADER, [
//                 'Content-Type: application/x-www-form-urlencoded'
//             ]);

//             // Execute the cURL session and capture the response
//             $response = curl_exec($ch);

//             // Handle cURL errors
//             if (curl_errno($ch)) {
//                 return response()->json([
//                     'message' => 'cURL Error: ' . curl_error($ch),
//                     'success' => false
//                 ], 500);
//             }

//             // Close the cURL session
//             curl_close($ch);

//             // Decode the JSON response
//             $response = json_decode($response, true);
// dd($response);
//             // Check if the payment link is returned from the payment gateway
//             if (isset($response['payment_link']) && !empty($response['payment_link'])) {
//                 // Create booking record with transaction_id and payment link
//                 $booking = Booking::create(array_merge($request->only([
//                     'user_id', 'quantity', 'service_id', 'address', 'description', 'booking_date', 
//                     'price', 'discount', 'sub_total', 'tax', 'total_amount', 'payment_through' 
                    
//                 ]), [
//                     'transaction_id' => $transactionId,
//                     'redirect_url' => "https://handyman.mobileappdemo.net/api/check_payment?transaction_id=$transactionId"
//                 ]));
        
//                 return response()->json([
//                     'message' => 'Booking created successfully!',
//                     'success' => true,
//                     'payment_link' => $response['payment_link']
//                 ], 200);
//             } else {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Payment gateway error, no payment link returned!'
//                 ], 200);
//             }
//         } else {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'User not found!'
//             ], 200);
//         }
//     }
// }




//     public function checkPaymentt(Request $request)
//     {
     
//         $orderid = $request->input('transaction_id');
	
//         if ($orderid == "") {
//             return response()->json(['status' => 400, 'message' => 'Transaction Id is required']);
//         } else {
//             $match_order = DB::table('bookings')->where('transaction_id', $orderid)->where('transaction_status', 1)->first();

//             if ($match_order) {
//                 $uid = $match_order->user_id;
                
//                 $cash = $match_order->total_amount;
                
               
//                 $orderid = $match_order->transaction_id;
//                  $datetime=now();
             
//                  //UPDATE orders SET status='1' WHERE transaction_id='$orderid'
//                 //  DB::table('orders')->where('transaction_id', $orderid)->update(['status' => 1]); 
//               $update_payin = DB::table('bookings')->where('transaction_id', $orderid)->where('transaction_status', 1)->where('user_id', $uid)->update(['transaction_status' => 2]);
    
//                 if ($update_payin) {
                   
//                 return redirect()->away('https://handyman.mobileappdemo.net/public/payment_success.php');
                    
//                 } else {
//                     return response()->json(['success' => false, 'message' => 'Failed to update payment status!'],200);
//                 }
//             } else {
//                 return response()->json(['success' => false, 'message' => 'Order id not found or already processed'],200);
//             }
//         }
//     }
   
   
   
    public function createOrder(Request $request)
    {
    
        $validated = $request->validate([
            'customer_mobile' => 'required|integer',
            'user_token' => 'required|string',
            'amount' => 'required|numeric',
            'order_id' => 'required|string',
            'redirect_url' => 'required|url',
            'remark1' => 'nullable|string',
            'remark2' => 'nullable|string',
            'route' => 'required|integer',
        ]);

        
        $postData = [
            'customer_mobile' => $validated['customer_mobile'],
            'user_token' => $validated['user_token'],
            'amount' => $validated['amount'],
            'order_id' => $validated['order_id'],
            'redirect_url' => $validated['redirect_url'],
            'remark1' => $validated['remark1'] ?? null,
            'remark2' => $validated['remark2'] ?? null,
            'route' => $validated['route'],
        ];

        
        $response = Http::asForm()->post('https://niyope.com/api/create-order', $postData);

    
        if ($response->successful()) {
            return response()->json([
                'status' => true,
                'message' => 'Order Created Successfully',
                'result' => $response->json()
            ], 200);
        } else {
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
                'error' => $response->json()
            ], $response->status());
        }
    }
  
    
}