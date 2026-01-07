<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;




class LocationController extends Controller
{
    public function getCountries(Request $request)
{
    $search = $request->input('search');
    $query = DB::table('country')->select('id', 'name', 'phone_code');

    if (!empty($search)) {
        $query->where('name', 'LIKE', "%{$search}%")
              ->orWhere('phone_code', 'LIKE', "%{$search}%");
    }
    $countries = $query->get();

    if ($countries->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No countries found.'
        ], 200);
    } else {
        return response()->json([
            'success' => true,
            'data' => $countries
        ], 200);
    }
}


   public function getStates(Request $request)
{
    $query = DB::table('states')->select('id', 'name');

    
    if ($request->has('name') && !empty($request->name)) {
        $search = $request->name;
        $query->where('name', 'LIKE', '%' . $search . '%');
    }

    $states = $query->get();

    if ($states->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No states found.'
        ], 200);
    } else {
        return response()->json([
            'success' => true,
            'data' => $states
        ], 200);
    }
}

 public function serviceslist(Request $request)
{
    $subcategory_id = $request->input('subcategory_id'); // optional
    $name = $request->input('name'); // optional

    // ✅ Base query
    $query = DB::table('services')
        ->select(
            'id',
            'category_id',
            'subcategory_id',
            'subcategory_name',
            'name',
            'description',
            'image',
            'price',
            'discount',
            'duration',
            'provider_id',
            'status',
            'created_at',
            'updated_at',
            'duration_mint',
            'ciry',
            'type',
            'handyman_id',
            'address',
            'user_id',
            'tax'
        );

    // ✅ Apply subcategory filter only if provided
    if (!empty($subcategory_id)) {
        $query->where('subcategory_id', $subcategory_id);
    }

    // ✅ Apply name filter if provided
    if (!empty($name)) {
        $query->where('name', 'LIKE', '%' . $name . '%');
    }

    // ✅ Fetch data
    $services = $query->get();

    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No services found.',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $services
    ], 200);
}

   public function getCities(Request $request, $stateId)
{
    $query = DB::table('cities')
        ->where('state_id', $stateId)
        ->select('id', 'name', 'state_id');

    // Agar name parameter bheja gaya ho to search kare
    if ($request->has('name') && !empty($request->name)) {
        $search = $request->name;
        $query->where('name', 'LIKE', '%' . $search . '%');
    }

    $cities = $query->get();

    if ($cities->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No cities found.'
        ], 200);
    } else {
        return response()->json([
            'success' => true,
            'data' => $cities
        ], 200);
    }
}

    
    
public function work(Request $request)
{
    
    $validated = $request->validate([
        'job_title' => 'required|string|max:30',
        'job_description' => 'required|string|max:50',
        'Estimated_price' => 'required',
        'user_id' => 'required|integer|exists:user_details,id', 
    ]);

    $data = DB::table('new_job')->insertGetId([
        'job_title' => $request->job_title,
        'job_description' => $request->job_description,
        'Estimated_price' => $request->Estimated_price,
        'user_id' => $request->user_id,
    ]);

    if ($data) {
        return response()->json([
            'success' => true,
            'message' => 'Job Post created successfully',
            // 'service_id' => $data,
        ], 200); 
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create the Job Post.',
        ], 200);  
    }
}


public function view_work($userid)
{
    
    $view = DB::table('new_job')
        ->select('id', 'job_title', 'job_description', 'Estimated_price')
        ->where('user_id', $userid)
        ->get();

    return response()->json([
        'success' => true,
        'data' => $view,
    ], 200);
}




public function destroy_work($id)
{
    $jw = DB::table('new_job')->where('id', $id)->first();

    if (!$jw) {
        return response()->json([
            'status' => false,
            'message' => 'work not found.',
        ], 200);
    }

    DB::table('new_job')->where('id', $id)->delete();

    return response()->json([
        'status' => true,
        'message' => 'deleted successfully.',
    ], 200);
}


 public function showWalletAmount(Request $request, $user_id)
    {
        
        $userDetail = DB::table('transaction_details')
            ->select('wallet_amount')
            ->where('user_id', $user_id)
            ->first();

        if (!$userDetail) {
            return response()->json([
                'success'=>false,
                'message' => 'User not found'
                ], 200);
        }

        return response()->json([
            'success'=>true,
            'message'=>'wallet amount of user',
            'user_id' => $user_id,
            'wallet_amount' => '₹' . $userDetail->wallet_amount,
        ],200);
    }
    
  public function walletHistory(Request $request, $user_id)
{
    $transactions = DB::table('transaction_details')
        ->select('id', 'wallet_amount', 'payin', 'payout','type', 'created_at')
        ->where('user_id', $user_id)
        ->orderBy('created_at', 'desc')
        ->get();

    if ($transactions->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No transactions found for this user'
        ], 200);
    }
    $latestTransaction = $transactions->first();

    return response()->json([
        'success' => true,
        'user_id' => $user_id,
        'wallet_amount' => '₹' . ($latestTransaction->wallet_amount ?? '0.00'), 
        'transactions' => $transactions,
    ], 200);
}


public function Favorite(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'service_id' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors occurred.',
            'errors' => $validator->errors()
        ], 200);
    }

    $user_id = $request->user_id;
    $service_id = $request->service_id;

    // ✅ Check if already exists
    $existingFavorite = DB::table('favorites')
        ->where('user_id', $user_id)
        ->where('service_id', $service_id)
        ->first();

    if ($existingFavorite) {
        // ✅ Toggle status
        $newStatus = $existingFavorite->status == 1 ? 0 : 1;

        DB::table('favorites')
            ->where('id', $existingFavorite->id)
            ->update(['status' => $newStatus]);

        $message = $newStatus == 1 ? 'Service added to favorites.' : 'Service removed from favorites.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $newStatus,
        ], 200);
    } else {
        // ✅ Add new favorite with status = 1
        DB::table('favorites')->insert([
            'user_id' => $user_id,
            'service_id' => $service_id,
            'status' => 1,
           
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service added to favorites.',
            'status' => 1,
        ], 200);
    }
}




public function showFavorite($user_id)
{
    // ✅ Fetch favorite services with provider info
    $favorites = DB::table('favorites')
        ->leftJoin('services', 'favorites.service_id', '=', 'services.id')
        ->leftJoin('user_details', 'services.provider_id', '=', 'user_details.id')
        ->select(
            'favorites.service_id',
            'favorites.status',
            'services.name as name',
            'services.image as image',
            'services.price',
            'services.discount',
            'services.duration',
            'services.description',
            'user_details.full_name as provider_name',
            'user_details.image as provider_image'
        )
        ->where('favorites.user_id', $user_id)
        ->where('favorites.status', 1) // ✅ Only active favorites
        ->get();

    if ($favorites->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No favorite services found.',
            'favorites' => []
        ], 200);
    }

    // ✅ Add "is_added_to_favorite" flag (always 1 since only active ones shown)
    $favorites = $favorites->map(function ($item) {
        $item->is_added_to_favorite = 1;
        return $item;
    });

    return response()->json([
        'success' => true,
        'message' => 'Favorite services retrieved successfully.',
        'user_id' => $user_id,
        'favorites' => $favorites
    ], 200);
}



public function removeFavorite( Request $request,$user_id, $service_id)
{

    $favorite = DB::table('favorites')
        ->where('user_id', $user_id)
        ->where('service_id', $service_id)
        ->first();

    if (!$favorite) {
        return response()->json([
            'success' => false,
            'message' => 'Favorite not found.',
        ], 200);
    }

    DB::table('favorites')
        ->where('user_id', $user_id)
        ->where('service_id', $service_id)
        ->delete();

    return response()->json([
        'success' => true,
        'message' => 'Service removed from favorites.',
    ], 200);
}


}


