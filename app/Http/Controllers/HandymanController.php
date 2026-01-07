<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class HandymanController extends Controller
{
      public function term_condition_handy()
    {
       
        $tm = DB::table('handy_terms_conditions')->select('id','description')->get();
        return response()->json([
            'success' => true,
            'data' => $tm,
        ], 200);
        
    }
      public function privacy_policy()
    {
       
        $pph = DB::table('handy_privacy_policy')->select('id','description')->get();
        return response()->json([
            'success' => true,
            'data' => $pph,
        ], 200);
        
    }
      public function help_support()
    {
       
        $hsh = DB::table('handy_help_support')->select('id','description')->get();
        return response()->json([
            'success' => true,
            'data' => $hsh,
        ], 200);
        
    }
    
  public function showProviderDetails($userId)
    {
        
        $user = DB::table('providers_details')->where('id', $userId)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 200);
        }

        $provider = DB::table('providers_details')
            ->where('id', $user->provider_id)
            ->first();

        if (!$provider) {
            return response()->json([
                'success'=>false,
                'message' => 'Provider not found'
                ], 200);
        }

        return response()->json([
            'success'=> true,
            'full_name' => $provider->full_name,
            'email' => $provider->email,
            'phone_number' => $provider->phone_number,
            'image' => $provider->image,
        ], 200);
    } 
    
public function handymanbooking($handymanId)
{
    // Check if the handymanId is valid
    if (!is_numeric($handymanId) || $handymanId <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid handyman ID'
        ], 200);
    }

    // Start the query to get the bookings
    $query = DB::table('bookings')
        ->leftJoin('services', 'bookings.service_id', '=', 'services.id')
        ->leftJoin('providers_details as provider', 'services.provider_id', '=', 'provider.id')
        ->leftJoin('user_details', 'bookings.user_id', '=', 'user_details.id')
        ->leftJoin('providers_details as handyman', 'bookings.handyman_id', '=', 'handyman.id')
        ->where('bookings.handyman_id', $handymanId); // Filter only by handymanId

    // Order the bookings by the latest one
    $services = $query->orderBy('bookings.id', 'desc')
        ->select(
            'bookings.id',
            'bookings.user_id',
            'bookings.service_id',
            'bookings.booking_date',
            'bookings.status',
            'bookings.address',
            'bookings.payment_through',
            'bookings.price',
            'bookings.discount',
            'bookings.sub_total',
            'bookings.tax',
            'bookings.total_amount',
            'services.name as service_name',
            'services.image as service_image',
            'user_details.full_name as user_name',
            'user_details.email',
            'user_details.phone',
            'user_details.image',
            'handyman.id as handyman_id',
            'handyman.full_name as handyman_name',
            'provider.id as provider_id',
            'provider.full_name as provider_name'
        
        )
        ->get();

    // Check if the bookings collection is empty
    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No bookings found for the provided handyman ID.'
        ], 200);
    }

    // Return the bookings if found
    return response()->json([
        'success' => true,
        'message' => 'Booking details retrieved successfully.',
        'data' => $services
    ], 200);
}

public function notification($houseman_id)
{
    $notifications = DB::table('notification')
        ->where('houseman_id', $houseman_id)
        ->get();

    if ($notifications->isEmpty()) {
        return response()->json([
            'success' => false,
            'data' => [],
            'message' => 'Notifications not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $notifications
    ], 200);
}

	


public function accepthandymanBooking(Request $request)
{
    $bookingId = $request->input('booking_id');
    $handymanId = $request->input('handyman_id');
    $status = $request->input('status'); 
    
    
    if (!in_array($status, [5, 6])) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid status value. Please provide either 5 (accept by handyman) or 6 (decline by handyman).'
        ], 200);
    }


    $booking = DB::table('bookings')
        ->join('providers_details', 'bookings.handyman_id', '=', 'providers_details.id')
        ->where('bookings.id', $bookingId)
        ->where('providers_details.id', $handymanId)
        ->first();

    
    if (!$booking) {
        return response()->json([
            'success' => false,
            'message' => 'Booking not found or does not belong to the handyman.'
        ], 200);
    }


    DB::table('bookings')
        ->where('id', $bookingId)
        ->update(['status' => $status]);


    if ($status == 5) {
        return response()->json([
            'success' => true,
            'message' => 'Booking accepted by handyman successfully.'
        ], 200);
    } elseif ($status == 6) {
        return response()->json([
            'success' => true,
            'message' => 'Booking declined by handyman successfully.'
        ], 200);
    }
}
    
}
