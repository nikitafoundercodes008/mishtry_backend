<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProviderDetail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProviderDetailController extends Controller
{
    
    
public function register(Request $request)
{

    $validator = Validator::make($request->all(), [
        'full_name' => 'nullable|string|max:60',
        'email' => 'nullable|email|unique:providers_details',
        'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|unique:providers_details|min:10|max:10',
        'designation' => 'required|string|max:255',
        'role' => 'nullable|in:1,2', // 1: provider, 2: handyman
        'select_commision' => 'required|in:1,2', // 1: freelance, 2: company
        'selected_provider' => 'nullable',
        'provider_id' => 'nullable|exists:providers_details,id',
    ]);


    if ($request->role == 2) {
        if (empty($request->provider_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a provider from the list.',
            ], 200);
        } else {

            $validator->addRules(['provider_id' => 'required|exists:providers_details,id']);
        }
    }

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }


    $data = $request->all();


    if ($request->role == 2) {
        $data['provider_id'] = $request->provider_id;
    }

    
    $providerDetail = ProviderDetail::create($data);
    $lastInsertedId = $providerDetail->id;
    $verification = DB::table('providers_details')->select('verification_status')->where('id' , $lastInsertedId)->first();
    $verification_status = $verification->verification_status;
    if ($providerDetail) {
        return response()->json([
            'success' => true,
            'message' => 'Registered successfully',
            'verification_status' =>$verification_status,
            'provider_detail' => $providerDetail,
        ], 200);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Failed to register. Please try again later.',
        ], 200); 
    }
}




public function sign_in(Request $request)
{
    // Step 1: Validate phone number format and length
    $validator = Validator::make($request->all(), [
        'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|size:10', // regex and size:10 ensures it must be exactly 10 digits
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => $validator->errors()->first(), // return the first validation error message
        ], 200);
    }

    // Step 2: Get the validated phone number
    $phone_number = $request->phone_number;

    // Step 3: Check if the user exists
    $user = ProviderDetail::where('phone_number', $phone_number)->first();

    if ($user) {
        // Check if the role is 1 (direct login)
        if ($user->role == 2) {
            return response()->json([
                'success' => true,
                'status' => $user->verification_status,
                'message' => 'User found and logged in successfully.',
                'id' => $user->id,
                'role' => $user->role,
            ], 200);
        }

        // Step 4: Handle the user's verification status if role is not 1
        switch ($user->verification_status) {
            case 2: // Verified
                return response()->json([
                    'success' => true,
                    'status' => $user->verification_status,
                    'message' => 'User found and logged in successfully.',
                    'id' => $user->id,
                    'role' => $user->role,
                ], 200);
                
            case 3: // Under review
                return response()->json([
                    'success' => false,
                    'status' => $user->verification_status,
                    'message' => 'Your account is under review. Please contact the admin.',
                ], 200);

            case 4: // Blocked
                return response()->json([
                    'success' => false,
                    'status' => $user->verification_status,
                    'message' => 'Your account is blocked. Please contact the admin.',
                ], 200);

            default: // Other statuses
                return response()->json([
                    'success' => false,
                    'message' => 'Your account status is not valid for login.',
                ], 200);
        }
    } else {
        // Step 5: If no user is found
        return response()->json([
            'success' => false,
            'message' => 'You are not registered. Please sign up first.',
        ], 200);
    }
}


    public function getServiceCount($provider_id)
    {
        $count = DB::table('services')
                    ->where('provider_id', $provider_id)
                    ->count();

        return response()->json([
            'success'=>true,
            'service_count' => $count],200);
    }
 
    
    
public function getbookingCount($provider_id)
{
    
    $count = DB::table('bookings')
        ->join('services', 'bookings.service_id', '=', 'services.id')
        ->where('services.provider_id', $provider_id)
        ->count();
    
    return response()->json([
        'success'=>true,
        'booking_count' => $count],200);
}

    
    
     public function getProviders(Request $request)
    {
        $search = $request->input('search');
        $query = ProviderDetail::where('role', 1)->select('id', 'full_name','image');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%');
                  
            });
        }

        $providers = $query->get();

        if ($providers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No providers found',
                'id'      => $providers->id,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Providers fetched successfully',
            'data' => $providers,
        ], 200);
    }
    
    
public function show(ProviderDetail $user)
{

    $userData = $user->only([
        'full_name', 'email', 'phone_number', 'country', 'state', 'city', 'address', 
        'designation', 'languages', 'reason', 'skills', 'why_choose_me', 'about_you', 
        'image','verification_status'
    ]);

    return response()->json([
        'success' => true,
        'data' => $userData
    ]);
}



public function model_update(Request $request)
{
    $userId = $request->input('id');
    $user = ProviderDetail::find($userId);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 200);
    }

    $validator = Validator::make($request->all(), [
        'full_name' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255|unique:providers_details,email,' . $user->id,
        'phone_number' => 'nullable|string|max:15',
        'image' => 'nullable|image|max:2048',
        'image_base64' => 'nullable|string',
        'country'=>'nullable|string',
        'state'=>'nullable|string',
        'city'=>'nullable|string',
        'address' => 'nullable|string|max:500',
        'designation' => 'nullable|string|max:255',  
        'languages' => 'nullable|string',
        'reason' => 'nullable|string|max:500',
        'skills' => 'nullable|string',
        'why_choose_me' => 'nullable|string|max:500',
        'about_you' => 'nullable|string|max:1000',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => $validator->errors()->first()
        ], 200); 
    }

    $baseUrl = 'https://handyman.mobileappdemo.net';
$input = collect($request->only(['full_name','email', 'phone_number','country','state','city', 'address', 'designation', 'languages','skills', 'reason',  'why_choose_me', 'about_you']))
    ->filter(function ($value) {
        return $value !== null;
    })->toArray();
    
if ($request->hasFile('image')) {
    if ($user->image && file_exists(public_path('image/' . basename($user->image)))) {
        unlink(public_path('image/' . basename($user->image)));
    }

    $file = $request->file('image');
    $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
    $file->move(public_path('image'), $fileName);

    $input['image'] = $baseUrl . '/public/image/' . $fileName; 
}

if ($request->input('image_base64')) {
    $imageData = base64_decode($request->input('image_base64'));
    $imageName = 'image/' . uniqid() . '.png';
    file_put_contents(public_path($imageName), $imageData);

    if ($user->image && file_exists(public_path($user->image))) {
        unlink(public_path($user->image));
    }

    $input['image'] = $baseUrl . '/public/' . $imageName; 
}

    $user->update($input);

    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user' => $user
    ], 200);
}


public function getHandymans(Request $request)
{
    $provider_id = $request->provider_id;

    if (!$provider_id) {
        return response()->json([
            'status' => false,
            'error' => 'Provider ID is required.'
        ], 200); 
    }

    $query = ProviderDetail::where('role', 2)
                           ->where('provider_id', $provider_id)
                           ->select('id', 'full_name', 'image', 'phone_number', 'email','provider_id')
                           ->get();
    
    if ($query->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No handymen found for this provider ID.'
        ], 200); 
    }

    // Return the handymen data
    return response()->json([
        'status' => true,
        'message' => 'Handymen fetched successfully.',
        'data' => $query 
        
    ], 200);
}






public function final_Booking($providerId)
{
    // Check if the providerId is valid
    if (!is_numeric($providerId) || $providerId <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid provider ID'
        ], 200);
    }

    $services = DB::table('bookings')
        ->leftJoin('services', 'bookings.service_id', '=', 'services.id')
        ->leftJoin('providers_details as provider', 'services.provider_id', '=', 'provider.id')
        ->leftJoin('user_details', 'bookings.user_id', '=', 'user_details.id')
        ->leftJoin('providers_details as handyman', 'bookings.handyman_id', '=', 'handyman.id')
        ->where('services.provider_id', $providerId)
        ->where('bookings.transaction_status', 2)
        ->orderBy('bookings.id', 'desc') 
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
            'provider.id as provider_id',
            'provider.full_name as provider_name',
            'handyman.id as handyman_id',
            'handyman.phone_number as handyman_phone',
            'handyman.full_name as handyman_name',
            'handyman.image as handyman_image'
        )
        ->get();

    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No bookings found for the provided provider ID.'
        ], 200);
    }

    
    return response()->json([
        'success' => true,
        'message' => 'Booking details retrieved successfully.',
        'data' => $services
    ], 200);
}


public function acceptBooking(Request $request)
{
    $bookingId = $request->input('booking_id');
    $providerId = $request->input('provider_id');
    $status = $request->input('status'); 
    
    
    if (!in_array($status, [1, 4])) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid status value. Please provide either 1 (accept) or 4 (decline).'
        ], 400);
    }


    $booking = DB::table('bookings')
        ->join('services', 'bookings.service_id', '=', 'services.id')
        ->where('bookings.id', $bookingId)
        ->where('services.provider_id', $providerId)
        ->first();

    
    if (!$booking) {
        return response()->json([
            'success' => false,
            'message' => 'Booking not found or does not belong to the provider.'
        ], 200);
    }


    DB::table('bookings')
        ->where('id', $bookingId)
        ->update(['status' => $status]);


    if ($status == 1) {
        return response()->json([
            'success' => true,
            'message' => 'Booking accepted successfully.'
        ], 200);
    } elseif ($status == 4) {
        return response()->json([
            'success' => true,
            'message' => 'Booking declined successfully.'
        ], 200);
    }
}



public function assignHandyman(Request $request)
{
    $bookingId = $request->input('booking_id');
    $handymanId = $request->input('handyman_id');

    // Get the booking record
    $booking = DB::table('bookings')->where('id', $bookingId)->first();

    // Check if the booking exists
    if (!$booking) {
        return response()->json([
            'success' => false,
            'message' => 'Booking not found or not accepted.'
        ], 200);  // Return 404 as booking is not found
    }

    // Check if a handyman is already assigned and if it is not the same as the new one
    if ($booking->handyman_id && $booking->handyman_id != $handymanId) {
        // Update with the new handyman
        DB::table('bookings')
            ->where('id', $bookingId)
            ->update([
                'handyman_id' => $handymanId,
                'status' => 2,  // Assuming status 2 means accepted or in progress
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Handyman updated successfully.'
        ], 200);
    }

    // Otherwise, just assign the handyman
    DB::table('bookings')
        ->where('id', $bookingId)
        ->update([
            'handyman_id' => $handymanId,
            'status' => 2,  // Assuming status 2 means accepted or in progress
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Handyman assigned successfully.'
    ], 200);
}



public function providerservice(Request $request)
{
    $providerId = $request->input('provider_id');
    if (!$providerId) {
        return response()->json([
            'success' => false,
            'message' => 'Provider ID is required.',
        ], 400);  
    }

    $service = DB::table('services')
        ->where('provider_id', $providerId)
        ->select('id', 'category_id', 'subcategory_id', 'subcategory_name', 'name', 'description', 'image', 'price', 'discount', 'duration')
        ->get();

    if ($service->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No services found.',
        ], 200);
    }

    return response()->json([
        'success' => true,
        'data' => $service,
    ], 200);
}

public function storeservice(Request $request)
{
$validator = Validator::make($request->all(), [
    'name' => 'required|string|max:255',
    'category_id' => 'required|exists:categories,id',
    'subcategory_id' => 'required|exists:subcategories,id',
    'subcategory_name' => 'required|exists:subcategories,name',
    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    'image_base64' => 'nullable|string',
    'price' => 'required|numeric',
    'discount' => 'nullable|numeric',
    'duration' => 'nullable|date_format:H:i',
    'provider_id' => 'required|exists:providers_details,id',
    'description' => 'nullable|string',
]);


    // Return validation errors if any
    if ($validator->fails()) {
        return response()->json($validator->errors(), 200);
    }


    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images', 'public');
        $imagePath = 'https://handyman.foundercode.org/public/' . basename($imagePath); 
    }

    if ($request->input('image_base64')) {
        $imageData = base64_decode($request->input('image_base64'));
        $imageName = 'image/' . uniqid() . '.png'; 
        file_put_contents(public_path($imageName), $imageData);

        $imagePath = 'https://handyman.mobileappdemo.net/public/' . $imageName;
    }


    $serviceData = [
        'name' => $request->input('name'),
        'category_id' => $request->input('category_id'),
        'subcategory_id' => $request->input('subcategory_id'),
        'subcategory_name'=>$request->input('subcategory_name'),
        'image' => $imagePath,
        'price' => $request->input('price'),
        'discount' => $request->input('discount'),
        'duration' => $request->input('duration'),
        'provider_id'=>$request->input('provider_id'),
        'description' => $request->input('description'),
    
    ];
    
 
    $insertedId = DB::table('services')->insertGetId($serviceData);

    if ($insertedId) {

        $insertedData = DB::table('services')->find($insertedId);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => $insertedData,
        ], 200);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create service',
        ], 200);
    }

}
   
   public function removeservice($id)
    {
        $service = DB::table('services')->where('id', $id)->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 200);
        }

        $deleted = DB::table('services')->where('id', $id)->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete service'
            ], 200);
        }
    } 
    
 

public function serviceupdate(Request $request)
{
    $serviceId = $request->input('id');
    
    $service = DB::table('services')->where('id', $serviceId)->first();

    if (!$service) {
        return response()->json(['error' => 'service not found'], 200);
    }

    $validator = Validator::make($request->all(), [
        'name' => 'nullable|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'subcategory_id' => 'nullable|exists:subcategories,id',
        'subcategory_name'=>'nullable|exists:subcategories,name',
        'image' => 'nullable|image|max:2048',
        'image_base64' => 'nullable|string',
        'price'=>'nullable|numeric',
        'discount'=>'nullable|numeric',
        'duration'=>'nullable|date_format:H:i',
        'description'=>'nullable|string',
     
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => $validator->errors()->first()
        ], 200); 
    }

    $baseUrl = 'https://handyman.mobileappdemo.net';
    
    // Filter the input fields
    $input = collect($request->only(['name','category_id', 'subcategory_id','subcategory_name','price','discount','duration', 'description', 'designation']))
        ->filter(function ($value) {
            return $value !== null;
        })->toArray();
    
    // Handle image file upload
    if ($request->hasFile('image')) {
        // Delete the old image if it exists
        if ($service->image && file_exists(public_path('image/' . basename($service->image)))) {
            unlink(public_path('image/' . basename($service->image)));
        }

        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('image'), $fileName);

        $input['image'] = $baseUrl . '/public/image/' . $fileName; 
    }

    // Handle base64 image upload
    if ($request->input('image_base64')) {
        $imageData = base64_decode($request->input('image_base64'));
        $imageName = 'image/' . uniqid() . '.png';
        file_put_contents(public_path($imageName), $imageData);

        // Delete the old image if it exists
        if ($service->image && file_exists(public_path($service->image))) {
            unlink(public_path($service->image));
        }

        $input['image'] = $baseUrl . '/public/' . $imageName; 
    }

    DB::table('services')
        ->where('id', $serviceId)
        ->update($input);
        
    $updatedservice = DB::table('services')->where('id', $serviceId)->first();

    return response()->json([
        'success' => true,
        'message' => 'service updated successfully',
        'user' => $updatedservice
    ], 200);
}

public function shopupdate(Request $request)
{
    $serviceId = $request->input('id');

    $service = DB::table('shops')->where('id', $serviceId)->first();

    if (!$service) {
        return response()->json(['error' => 'Shop not found'], 200);
    }

    $validator = Validator::make($request->all(), [
        // Add validations if needed
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => $validator->errors()->first()
        ], 200);
    }

    $baseUrl = 'https://handyman.mobileappdemo.net';

    // Only allowed fields to update
    $input = collect($request->only([
        'shop_name',
        'number',
        'state',
        'city',
        'address',
        'latitude',
        'long',
        'shopstarttime',
        'shop_end_time',
        'email',
        'select_service_id',
        'reg_number',
    ]))->filter(function ($value) {
        return $value !== null;
    })->toArray();

    // Handle file upload
    if ($request->hasFile('shop_image')) {

        // Delete old image
        if ($service->shop_image && file_exists(public_path('image/' . basename($service->shop_image)))) {
            unlink(public_path('image/' . basename($service->shop_image)));
        }

        $file = $request->file('shop_image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('image'), $fileName);

        $input['shop_image'] = $baseUrl . '/public/image/' . $fileName;
    }

    // Base64 image upload
    if ($request->image_base64) {
        $imageData = base64_decode($request->image_base64);
        $imageName = 'image/' . uniqid() . '.png';
        file_put_contents(public_path($imageName), $imageData);

        // Delete old image
        if ($service->shop_image && file_exists(public_path($service->shop_image))) {
            unlink(public_path($service->shop_image));
        }

        $input['shop_image'] = $baseUrl . '/public/' . $imageName;
    }

    // Update record
    DB::table('shops')
        ->where('id', $serviceId)
        ->update($input);

    $updatedservice = DB::table('shops')->where('id', $serviceId)->first();

    return response()->json([
        'success' => true,
        'message' => 'Shop updated successfully',
        'data' => $updatedservice
    ], 200);
}

public function packageupdate(Request $request)
{
    $serviceId = $request->input('id');

    $service = DB::table('packages')->where('id', $serviceId)->first();

    if (!$service) {
        return response()->json(['error' => 'Package not found'], 200);
    }

    $validator = Validator::make($request->all(), [
        // Add validations if needed
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error'    => $validator->errors()->first()
        ], 200);
    }

    $baseUrl = 'https://handyman.mobileappdemo.net';

    // Allowed fields to update
    $input = collect($request->only([
        'packages_name',
        'select_service_id',
        'packages_price',
        'start_date',
        'end_date',
        'status',
        'providers_id',
        'user_id'
    ]))->filter(function ($value) {
        return $value !== null;
    })->toArray();

    // Handle Normal Image File Upload
    if ($request->hasFile('image')) {

        // Delete old image
        if ($service->image && file_exists(public_path('image/' . basename($service->image)))) {
            unlink(public_path('image/' . basename($service->image)));
        }

        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('image'), $fileName);

        $input['image'] = $baseUrl . '/public/image/' . $fileName;
    }

    // Base64 Image Upload
    if ($request->image_base64) {

        $imageData = base64_decode($request->image_base64);
        $imageName = 'image/' . uniqid() . '.png';
        file_put_contents(public_path($imageName), $imageData);

        // Delete old image
        if ($service->image && file_exists(public_path($service->image))) {
            unlink(public_path($service->image));
        }

        $input['image'] = $baseUrl . '/public/' . $imageName;
    }

    // Update Package
    DB::table('packages')
        ->where('id', $serviceId)
        ->update($input);

    $updatedservice = DB::table('packages')->where('id', $serviceId)->first();

    return response()->json([
        'success' => true,
        'message' => 'Updated successfully',
        'data'    => $updatedservice
    ], 200);
}


 public function handyregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:providers_details,email',
            'phone_number' => 'required|string|max:15|unique:providers_details,phone_number',
            'designation' => 'nullable|string|max:255',
            'select_commission' => 'required|in:1,2',
            'address' => 'nullable|string',
            'provider_id' => 'required|exists:providers_details,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 200);
        }

        $handyman = ProviderDetail::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'designation' => $request->designation,
            'select_commission' => $request->select_commission,
            'address' => $request->address,
            'provider_id' => $request->provider_id,
            'role' => 2,  
        ]);

        return response()->json([
            'success'=>true,
            'message' => 'Handyman registered successfully!',
            'data' => $handyman,
        ], 200);
    }
}   
    




