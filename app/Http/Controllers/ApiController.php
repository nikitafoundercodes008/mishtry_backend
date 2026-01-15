<?php
 
namespace App\Http\Controllers;
 
use App\Models\user_details;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


use Illuminate\Support\Facades\Storage; 
class ApiController extends Controller
{
public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = [
            'http://localhost:63232',
            'http://localhost:3000',
            'https://mishtiry.com',
            'https://admin.mishtiry.com',
        ];

        $origin = $request->headers->get('Origin');

        $response = $next($request);

        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
	
public function signup(Request $request)
{
    $validator = Validator::make($request->all(), [
        'full_name'  => 'required|string|max:255',
        'email'      => 'nullable|email|unique:user_details,email',
        'phone'      => 'required|regex:/^[0-9]{10}$/|unique:user_details,phone',
        'password'   => 'required|string|min:6',
        'role_id'    => 'required|integer',
        'username'   => 'nullable|string|max:255',
        'provideo_id' => 'nullable|integer',
        'select_commission' => 'nullable',
        'designation' => 'nullable|string|max:255',
		'fcm_tokens' =>  'nullable',
		'lat' =>  'nullable',
		'long' =>  'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }
    $now = Carbon::now();
    // ✅ Create new user
    $user = user_details::create([
        'full_name'        => $request->full_name,
        'email'            => $request->email,
        'phone'            => $request->phone,
        'username'         => $request->username,
        'password'         => $request->password, // Password hashed
        'role_id'          => $request->role_id,
        'provideo_id'      => $request->provideo_id,
        'select_commission'=> $request->select_commission,
        'designation'      => $request->designation,
		'fcm_tokens'      => $request->fcm_tokens,
        'lat'      => $request->lat,
        'long'      => $request->long,
        'status'           => 1,
        'created_at'       => $now,
        'updated_at'       => $now,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'User registered successfully',
        'id' => $user->id,
        'Documentsstatus' => 0,
    ], 200);
}
   
public function services_add(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'            => 'required',
        'category_id'     => 'required|integer',
        'subcategory_id'  => 'nullable',
		'subcategory_name'  => 'nullable',
        'city'            => 'required',
        'type'            => 'nullable',
        'status'          => 'required',
        'price'           => 'required|numeric',
		'mrp_price'       => 'nullable',
        'discount'        => 'required|numeric',
        'duration'        => 'required',
        'duration_mint'   => 'required',
        'description'     => 'required',
        'provider_id'     => 'required|integer',
        'handyman_id'     => 'nullable',
        'image'           => 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $imageUrl = null;

    if ($request->image) {

        $image = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        if ($imageData === false) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid base64 image'
            ], 200);
        }

        $imageName = time() . '.png';
        $imageDir  = public_path('image/');

        if (!file_exists($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        file_put_contents($imageDir . $imageName, $imageData);

        // ✅ SAME URL AS YOU MENTIONED
        $imageUrl = url('public/image/' . $imageName);
    }

    $id = DB::table('services')->insertGetId([
        'category_id'      => $request->category_id,
        'subcategory_id'   => $request->subcategory_id,
        'subcategory_name' => $request->subcategory_id,
        'name'             => $request->name,
        'description'      => $request->description,
        'image'            => $imageUrl,
        'price'            => $request->price,
		'mrp_price'        => $request->mrp_price,
        'discount'         => $request->discount,
        'duration'         => $request->duration,
        'duration_mint'    => $request->duration_mint,
        'handyman_id'      => $request->handyman_id,
        'provider_id'      => $request->provider_id,
        'status'           => $request->status,
        'city'             => $request->city, // ✅ FIXED
        'type'             => $request->type,
        'created_at'       => now(),
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Service Added Successfully',
        'id'      => $id,
        'image'   => $imageUrl
    ], 200);
}


public function categories_view(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provider_id' => 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $provider_id = $request->input('provider_id');

    // ✅ Start Query Builder
    $query = DB::table('categories')
        ->select('id', 'name', 'image', 'status', 'created_at', 'updated_at', 'provider_id', 'created_admin');

    // ✅ Filter if provider_id is passed
    if (!empty($provider_id)) {
        $query->where('provider_id', $provider_id);
    }

    // ✅ Fetch Data
    $categories = $query->get();

    if ($categories->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $categories
    ], 200);
}
	
public function subcategories_view(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provider_id' => 'nullable',
        'category_id' => 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $provider_id = $request->input('provider_id');
    $category_id = $request->input('category_id');

    
    $query = DB::table('subcategories')
        ->select(
            'id',
            'category_id',
            'name',
            'image',
            'status',
            'created_at',
            'updated_at',
            'provider_id',
            'created_admin'
        );

    // ✅ Filter by provider_id if provided
    if (!empty($provider_id)) {
        $query->where('provider_id', $provider_id);
    }

    // ✅ Filter by category_id if provided
    if (!empty($category_id)) {
        $query->where('category_id', $category_id);
    }

    // ✅ Get results
    $subcategories = $query->get();

    if ($subcategories->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $subcategories
    ], 200);
}
public function provider_handy_doc(Request $request)
{
    $validator = Validator::make($request->all(), [
        'aadhar_front'   => 'nullable',
        'aadhar_back'    => 'nullable',
        'aadhar_no'      => 'nullable',
        'pan_number'     => 'nullable',
        'pan_cart_image' => 'nullable',
        'passpost_image' => 'nullable',
        'user_id'    => 'nullable',
        
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // Function to save Base64 image
    function saveBase64Image($base64Image)
    {
        if (!$base64Image) {
            return null;
        }

        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        if ($imageData === false) {
            return null;
        }

        $imageName = time() . rand(1000, 9999) . '.png';
        $uploadPath = public_path('uploads/documents/');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        file_put_contents($uploadPath . $imageName, $imageData);

        return url('public/uploads/documents/' . $imageName);
    }

    // Save images
    $aadharFront  = saveBase64Image($request->aadhar_front);
    $aadharBack   = saveBase64Image($request->aadhar_back);
    $panCardImage = saveBase64Image($request->pan_cart_image);
    $passportImg  = saveBase64Image($request->passpost_image);

    // Document status
    $status = 1;

    // Insert into database
    $id = DB::table('provider_handy_doc')->insertGetId([
        'aadhar_front'  => $aadharFront,
        'aadhar_back'   => $aadharBack,
        'aadhar_no'     => $request->aadhar_no,
        'pan_number'    => $request->pan_number,
        'pan_cart_image'=> $panCardImage,
        'passpost_image'=> $passportImg,
        'user_id'   => $request->user_id,
        'status'        => $status,
        'created_at'    => now()
    ]);

    return response()->json([
        'status'           => true,
        'message'          => 'Documents uploaded successfully. Please wait, admin approval pending.',
        'Documentsstatus'  => $status,
    ], 200);
}

public function signup(Request $request)
{
    $validator = Validator::make($request->all(), [
        'full_name'  => 'required|string|max:255',
        'email'      => 'nullable|email|unique:user_details,email',
        'phone'      => 'required|regex:/^[0-9]{10}$/|unique:user_details,phone',
        'password'   => 'required|string|min:6',
        'role_id'    => 'required|integer',
        'username'   => 'nullable|string|max:255',
        'provideo_id' => 'nullable|integer',
        'select_commission' => 'nullable',
        'designation' => 'nullable|string|max:255',
		'fcm_tokens' =>  'nullable',
		'lat' =>  'nullable',
		'long' =>  'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }
    $now = Carbon::now();
    // ✅ Create new user
    $user = user_details::create([
        'full_name'        => $request->full_name,
        'email'            => $request->email,
        'phone'            => $request->phone,
        'username'         => $request->username,
        'password'         => $request->password, // Password hashed
        'role_id'          => $request->role_id,
        'provideo_id'      => $request->provideo_id,
        'select_commission'=> $request->select_commission,
        'designation'      => $request->designation,
		'fcm_tokens'      => $request->fcm_tokens,
        'lat'      => $request->lat,
        'long'      => $request->long,
        'status'           => 1,
        'created_at'       => $now,
        'updated_at'       => $now,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'User registered successfully',
        'id' => $user->id,
        'Documentsstatus' => 0,
    ], 200);
}

public function monthly_revenue(Request $request)
{
    // 🔹 Validate request
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer',
        'month'   => 'required',
        'year'    => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $user_id = $request->user_id;
    $month   = $request->month;
    $year    = $request->year;

    // 🔹 Fetch transactions for the given month/year
    $data = DB::table('transaction_details')
        ->selectRaw('
            COALESCE(SUM(payin),0) as total_payin,
            COALESCE(SUM(payout),0) as total_payout
        ')
        ->where('user_id', $user_id)
        ->whereMonth('date', $month)   // Month from database column
        ->whereYear('date', $year)     // Year from database column
        ->first();

    // 🔹 No transactions found
    if (!$data || ($data->total_payin == 0 && $data->total_payout == 0)) {
        return response()->json([
            'status' => false,
            'message' => 'No transactions found for selected month.'
        ], 200);
    }

    $netRevenue = $data->total_payin - $data->total_payout;

    return response()->json([
        'status' => true,
        'message' => 'Monthly revenue data',
        'data' => [
            'month'        => date('F Y', strtotime("$year-$month-01")), // eg: January 2026
            'total_payin'  => (float) $data->total_payin,
            'total_payout' => (float) $data->total_payout,
            'net_revenue'  => (float) $netRevenue
        ]
    ], 200);
}

public function categories_delete(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $id = $request->input('id');

    // 🔍 Check category exists
    $category = DB::table('categories')->where('id', $id)->first();

    if (!$category) {
        return response()->json([
            'success' => false,
            'message' => 'Category not found'
        ], 200);
    }

    // 🗑 Delete category
    DB::table('categories')->where('id', $id)->delete();

    return response()->json([
        'success' => true,
        'message' => 'Category deleted successfully'
    ], 200);
}
	
public function handyman_on_off(Request $request)
{
    $userId = $request->input('id');

    $user = user_details::find($userId);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 200);
    }

    // Toggle status (1 -> 0, 0 -> 1)
    $user->on_off = ($user->on_off == 1) ? 0 : 1;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Status updated successfully',
        'on_off'  => $user->on_off,
        'user'    => $user
    ], 200);
}


public function provideo_view(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $user_id = $request->user_id;

    // ✅ Correct Query
    $services = DB::table('user_details')
        ->where('id', $user_id)
        ->get();

    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $services
    ], 200);
}
	
 
public function help_desk_add(Request $request)
{
    $validator = Validator::make($request->all(), [
        'subject' => 'required|string',
        'description' => 'required|string',
		'user_id' => 'required',
        'image' => 'nullable' // base64 image optional
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $imageUrl = null;

    // ✅ Base64 Image Upload (if provided)
    if (!empty($request->image)) {
        $imageData = base64_decode($request->image);

        if ($imageData === false) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid base64 image'
            ], 200);
        }

        $imageName = time() . '.png';
        $imageDir = public_path('uploads/helpdesk/');
        $imagePath = $imageDir . $imageName;

        if (!file_exists($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        file_put_contents($imagePath, $imageData);
        $imageUrl = url('uploads/helpdesk/' . $imageName);
    }

    // ✅ Insert into DB
    $id = DB::table('help_support')->insertGetId([
        'subject'      => $request->subject,
        'description'  => $request->description,
        'user_id'      => $request->user_id,
        'image'        => $imageUrl,
		'status'        =>1,
      
        'created_at'   => now(),
      
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Help desk ticket added successfully.',
        'id' => $id,
    ], 200);
}
	
public function filter_by(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provider_id' => 'required|integer',
        'type'        => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $provider_id = $request->provider_id;
    $type        = $request->type;

    
    $data = collect();

    switch ($type) {

        // 🔹 Type 1 → Shops
        case 1:
            $data = DB::table('shops')
                ->select('shop_image', 'shop_name')
                ->where('providers_id', $provider_id)
                ->get();
            break;

        // 🔹 Type 2 → Services
        case 2:
            $data = DB::table('services')
                ->select('name', 'image')
                ->where('provider_id', $provider_id)
                ->get();
            break;

        // 🔹 Type 3 → Shops (future / another category)
        case 3:
            $data = DB::table('shops')
                ->select('shop_image', 'shop_name')
                ->where('providers_id', $provider_id)
                ->get();
            break;

        // 🔹 Type 4 → Shops (future / another category)
        case 4:
            $data = DB::table('shops')
                ->select('shop_image', 'shop_name')
                ->where('providers_id', $provider_id)
                ->get();
            break;

        default:
            return response()->json([
                'success' => false,
                'message' => 'Invalid type'
            ], 200);
    }

    if ($data->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data'    => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data'    => $data
    ], 200);
}

public function categories_viewm(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provider_id' => 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $provider_id = $request->input('provider_id');

    // ✅ Start Query Builder
    $query = DB::table('categories')
        ->select('id', 'name', 'image', 'status', 'created_at', 'updated_at', 'provider_id', 'created_admin');

    // ✅ Filter if provider_id is passed
    if (!empty($provider_id)) {
        $query->where('provider_id', $provider_id);
    }

    // ✅ Fetch Data
    $categories = $query->get();

    if ($categories->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $categories
    ], 200);
}
	
public function shops_add(Request $request)
{
    $validator = Validator::make($request->all(), [
        'mobile'          => 'required',
        'shop_name'          => 'required',
        'state'              => 'nullable',
        'city'               => 'nullable',
        'address'            => 'required',
        'latitude'           => 'required',
        'long'               => 'required',
        'shopstarttime'      => 'required',
        'shop_end_time'      => 'required',
        'email'              => 'required',
        'reg_number'         => 'required',
        'select_service_id'  => 'required', // multiple allowed
        'providers_id'       => 'required',
        'shop_image'         => 'nullable' // base64 image optional
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $imageUrl = null;

    // ✅ Base64 Image Upload (if provided)
    if (!empty($request->shop_image)) {
        $imageData = base64_decode($request->shop_image);

        if ($imageData === false) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid base64 image'
            ], 200);
        }

        $imageName = time() . '.png';
        $imageDir = public_path('uploads/helpdesk/');
        $imagePath = $imageDir . $imageName;

        if (!file_exists($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        file_put_contents($imagePath, $imageData);
        $imageUrl = url('public/uploads/helpdesk/' . $imageName);
    }

    // ✅ Handle multi select_service_id (array allowed)
    $select_service_id = is_array($request->select_service_id)
        ? json_encode($request->select_service_id)
        : $request->select_service_id;

    // ✅ Insert into DB
    $id = DB::table('shops')->insertGetId([
        'number'             => $request->mobile,
        'shop_name'          => $request->shop_name,
        'state'              => $request->state,
        'city'               => $request->city,
        'address'            => $request->address,
        'latitude'           => $request->latitude,
        'long'               => $request->long,
        'shopstarttime'      => $request->shopstarttime,
        'shop_end_time'      => $request->shop_end_time,
        'email'              => $request->email,
        'reg_number'         => $request->reg_number,
        'select_service_id'  => $select_service_id, 
        'providers_id'       => $request->providers_id,
        'shop_image'         => $imageUrl,
        'created_at'         => now(),
        'updated_at'         => now(),
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Shop added successfully.',
        'id' => $id,
    ], 200);
}
	
public function pricing_add(Request $request)
{
    $validator = Validator::make($request->all(), [
        'services_list' => 'nullable|array',
        'services_id'   => 'nullable|array',
        'amount'        => 'nullable|numeric',
        'month'         => 'nullable|numeric',
        'provider_id'   => 'required',
        'off'           => 'nullable|numeric',
        'plan_type'     => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

   
    $existingPlan = DB::table('pricring_plan')
        ->where('provider_id', $request->provider_id)
        ->where('plan_type', $request->plan_type)
        ->first();

    $data = [
        'services_list' => $request->services_list 
                            ? json_encode($request->services_list) 
                            : null,

        'services_id'   => $request->services_id 
                            ? json_encode($request->services_id) 
                            : null,

        'amount'        => $request->amount,
        'month'         => $request->month,
        'off'           => $request->off,
        'updated_at'    => now(),
    ];

    if ($existingPlan) {
        // 🔁 UPDATE
        DB::table('pricring_plan')
            ->where('id', $existingPlan->id)
            ->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Pricing plan updated successfully.',
            'id' => $existingPlan->id
        ], 200);

    } else {
        // ➕ INSERT
        $data['provider_id'] = $request->provider_id;
        $data['plan_type']   = $request->plan_type;
        $data['created_at']  = now();

        $id = DB::table('pricring_plan')->insertGetId($data);

        return response()->json([
            'status' => true,
            'message' => 'Pricing plan added successfully.',
            'id' => $id
        ], 200);
    }
}

public function packages_add(Request $request)
{
    $validator = Validator::make($request->all(), [
        'packages_name'      => 'nullable',
        'select_service_id'  => 'nullable',
        'packages_price'     => 'nullable',
        'start_date'         => 'nullable',
        'end_date'           => 'nullable',
        'status'             => 'nullable',
        'providers_id'       => 'nullable',
        'shop_image'         => 'nullable' // correct field
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $imageUrl = null;

    // Base64 Image Upload
    if (!empty($request->shop_image)) {

        $imageData = base64_decode($request->shop_image);

        if ($imageData === false) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid base64 image'
            ], 200);
        }

        $imageName = time() . '.png';
        $imageDir  = public_path('uploads/helpdesk/');
        $imagePath = $imageDir . $imageName;

        if (!file_exists($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        file_put_contents($imagePath, $imageData);

        $imageUrl = url('public/uploads/helpdesk/' . $imageName);
    }

    // Handle array service IDs
    $select_service_id = is_array($request->select_service_id)
        ? json_encode($request->select_service_id)
        : $request->select_service_id;

    // Insert into DB
    $id = DB::table('packages')->insertGetId([
        'image'             => $imageUrl,
        'packages_name'     => $request->packages_name,
        'select_service_id' => $select_service_id,
        'packages_price'    => $request->packages_price,
        'start_date'        => $request->start_date,
        'end_date'          => $request->end_date,
        'status'            => $request->status ?? 1,
        'providers_id'      => $request->providers_id,
        'user_id'           => session()->get('user_id'),
        'created_at'        => now()
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Package added successfully.',
        'id' => $id
    ], 200);
}
public function packages_list(Request $request)
{
    // 🔹 Validation
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // 🔹 Get single package
    $package = DB::table('packages')
        ->where('id', $request->id)
        ->first();

    if (!$package) {
        return response()->json([
            'status'  => false,
            'message' => 'No package found.',
			'data' => ""
        ], 200);
    }
    $serviceIds = json_decode($package->select_service_id, true);
    if (empty($serviceIds)) {
        $services = [];
    } else {
        $services = DB::table('services')
            ->leftJoin('categories', 'categories.id', '=', 'services.category_id')
            ->leftJoin('subcategories', 'subcategories.id', '=', 'services.subcategory_id')
            ->whereIn('services.id', $serviceIds)
            ->select(
                'services.id',
                'services.name',
                'services.image',
                'services.description',
                'services.price',
                'services.category_id',
                'services.subcategory_id',
                'categories.name as category_name',
                'categories.image as category_image',
                'subcategories.name as subcategory_name',
                'subcategories.image as subcategory_image'
            )
            ->get();
    }
    $finalData = [
        'id'             => $package->id,
        'image'          => $package->image,
        'packages_name'  => $package->packages_name,
        'packages_price' => $package->packages_price,
        'start_date'     => $package->start_date,
        'end_date'       => $package->end_date,
        'status'         => $package->status,
        'providers_id'   => $package->providers_id,
        'created_at'     => $package->created_at,
        'services'       => $services
    ];
    return response()->json([
        'status'  => true,
        'message' => 'Package details',
        'data'    => $finalData
    ], 200);
}


	
public function services_view(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provider_id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $data = DB::table('services')
        ->join('subcategories', 'services.subcategory_id', '=', 'subcategories.id')
        ->select(
            'services.id',
            'services.category_id',
            'services.subcategory_id',
            'services.name',
            'services.description',
            'services.image',
            'services.price',
            'services.mrp_price',
            'services.discount',
            'services.duration',
            'services.duration_mint',
            'services.provider_id',
            'services.status',
            'services.city',
            'services.type',
            'services.handyman_id',
            'services.address',
            'services.user_id',
            'services.tax',
            'services.services_id',
            'services.created_at',
            'services.updated_at',
            'subcategories.name as subcategory_name'
        )
        ->where('services.provider_id', $request->provider_id)
        ->get();

    if ($data->isEmpty()) {
        return response()->json([
            'status'  => false,
            'message' => 'Services not found.'
        ], 200);
    }

    return response()->json([
        'status'  => true,
        'message' => 'Services Data',
        'data'    => $data
    ], 200);
}

public function Pricing_view()
{
    $data = DB::table('pricring_plan')
        ->select(
            'id',
            'services_list',
            'amount',
            'month',
            'provider_id',
            'off',
            'plan_type',
            'created_at',
            'updated_at'
        )
        ->get();

    return response()->json([
        'status'  => true,
        'message' => 'Services Data',
        'data'    => $data
    ], 200);
}
	
public function packages_delete(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // Try to delete
    $deleted = DB::table('packages')->where('id', $request->id)->delete();

    if ($deleted == 0) {
        return response()->json([
            'status' => false,
            'message' => 'Package not found.'
        ], 200);
    }

    return response()->json([
        'status' => true,
        'message' => 'Package deleted successfully.'
    ], 200);
}
	
public function pricring_delete(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }
    $deleted = DB::table('pricring_plan')->where('id', $request->id)->delete();
    if ($deleted == 0) {
        return response()->json([
            'status' => false,
            'message' => 'Pricring not found.'
        ], 200);
    }

    return response()->json([
        'status' => true,
        'message' => 'Pricring deleted successfully.'
    ], 200);
}	
	
public function slotslist(Request $request)
{
    $validator = Validator::make($request->all(), [
        'providers_id' => 'required|integer',
        'week_day'     => 'nullable|string' // or integer if you store 1–7
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'data'    => []
        ], 422);
    }

    $providers_id = $request->providers_id;
    $week_day     = $request->week_day;

    $query = DB::table('slots')
        ->select(
            'id',
            'handymans_id',
            'week_day',
            'status',
            'updated_at',
            'start_time',
            'end_time',
            'duration_mint',
            'providers_id',
            'slot',
            'duration_id'
        )
        ->where('providers_id', $providers_id);

    // ✅ Apply week_day filter only if provided
    if (!empty($week_day)) {
        $query->where('week_day', $week_day);
    }

    $slots = $query->get();

    if ($slots->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No slots found',
            'data'    => []
        ], 200);
    }

    // ✅ Decode slot JSON
    $slots = $slots->map(function ($item) {
        $item->slot = $item->slot
            ? json_decode($item->slot, true)
            : [];
        return $item;
    });

    return response()->json([
        'success' => true,
        'message' => 'Slots fetched successfully',
        'data'    => $slots
    ], 200);
}

	
	
public function slotslistProviders(Request $request)
    {
        // ✅ Validation
        $validator = Validator::make($request->all(), [
            'week_day'     => 'nullable|string',
            'providers_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => []
            ], 422);
        }

        $week_day     = $request->input('week_day');
        $providers_id = $request->input('providers_id');

        // ✅ Query slots
        $query = DB::table('slots_providers')
            ->where('providers_id', $providers_id)
            ->select(
                'id',
                'week_day',
                'status',
                'providers_id',
                'updated_at',
                'duration_mint',
                'start_time',
                'end_time'
            );

        if (!empty($week_day)) {
            $query->where('week_day', $week_day);
        }

        $slots = $query->get();

        if ($slots->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No slots found',
                'data'    => []
            ], 200);
        }

        // ✅ Generate intervals based on start_time, end_time, and duration_mint
        $slots = $slots->map(function ($item) {
            $generatedSlots = [];
            $duration = isset($item->duration_mint) ? (int) $item->duration_mint : 60;

            if (!empty($item->start_time) && !empty($item->end_time)) {
                $startTime = strtotime($item->start_time);
                $endTime   = strtotime($item->end_time);

                while ($startTime < $endTime) {
                    $slotStart = date('H:i', $startTime);
                    $slotEnd   = date('H:i', strtotime("+{$duration} minutes", $startTime));

                    if (strtotime($slotEnd) <= $endTime) {
                        $generatedSlots[] = [
                            'id'         => (string) Str::uuid(),
                            'start_time' => $slotStart,
                            'end_time'   => $slotEnd
                        ];
                    }

                    $startTime = strtotime("+{$duration} minutes", $startTime);
                }
            }

            $item->generated_slots = $generatedSlots;
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Slots fetched successfully',
            'data'    => $slots
        ], 200);
    }
public function addon_service_add(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'        => 'required|string',
        
        'price'       => 'required|numeric',
        'status'      => 'required',
        'provider_id' => 'required|integer',
		'services_id' => 'required',
        'image'       => 'required' 
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    
    $imageData = base64_decode($request->image);

    if ($imageData === false) {
        return response()->json([
            'status'  => false,
            'message' => 'Invalid base64 image'
        ], 200);
    }

    $imageName = time() . '.png';
    $imageDir  = public_path('public/image/');
    $imagePath = $imageDir . $imageName;
    if (!file_exists($imageDir)) {
        mkdir($imageDir, 0777, true);
    }

    file_put_contents($imagePath, $imageData);
    $imageUrl = url('uploads/services/' . $imageName);

    
    $id = DB::table('addon_service')->insertGetId([
        'name'        => $request->name,
        'price'       => $request->price,
        'status'      => $request->status,
        'provider_id' => $request->provider_id,
		'services_id' => $request->services_id,
        'image'       => $imageUrl,
        'date'  => now(),
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Added Successfully',
        'id'      => $id,
    ], 200);
}

public function address_users(Request $request)
{
    $validator = Validator::make($request->all(), [
        'address'   => 'required|string|max:255',
        'user_id'   => 'required',
        'lat'       => 'required|numeric',
        'long'      => 'required|numeric',
        'name'      => 'nullable',
        'house_no'  => 'nullable',
        'mobile'    => 'nullable',
        'landkmark' => 'nullable',
        'type'      => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    try {
        $userId = $request->user_id;

        // Step 1: Reset all other addresses of this user to default = 0
        DB::table('address')
            ->where('user_id', $userId)
            ->update(['default' => 0]);

        // Step 2: Insert new address as default = 1
        $id = DB::table('address')->insertGetId([
            'address'   => $request->address,
			'mobile'   => $request->mobile,
            'user_id'   => $userId,
            'lat'       => $request->lat,
            'long'      => $request->long,
            'name'      => $request->name,
            'house_no'  => $request->house_no,
            'landkmark' => $request->landkmark,
            'type'      => $request->type,
            'date'      => now(),
            'default'   => 1,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Address Added Successfully',
            'id'      => $id,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong',
            'error'   => $e->getMessage()
        ], 500);
    }
}

	

public function reviews_add(Request $request)
{
    // Validate incoming request
    $validator = Validator::make($request->all(), [
        'rate'       => 'required',
        'user_id'    => 'required',
        'handymanid' => 'required',
        'comment'    => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // Insert review into database
    $id = DB::table('reviews')->insertGetId([
        'rate'       => $request->rate,
        'user_id'    => $request->user_id,
        'handymanid' => $request->handymanid,
        'status'     => 1, // assuming default status
        'created_at' => now(),
        'comment'    => $request->comment ?? '',
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Added Successfully',
        'id'      => $id,
    ], 200);
}	
  public function Slider_view(Request $request)
{
    try {
        $allowedOrigins = [
            'https://admin.mishtiry.com',
            'https://mishtiry.com',
            'http://localhost:62339',
        ];

        $origin = $request->headers->get('Origin');

        $headers = [
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        if (in_array($origin, $allowedOrigins)) {
            $headers['Access-Control-Allow-Origin'] = $origin;
        }

        // ✅ Handle preflight request
        if ($request->getMethod() === 'OPTIONS') {
            return response()->json([
                'success' => true
            ])->withHeaders($headers);
        }

        $slider = DB::table('sliders')
            ->select('id', 'image')
            ->get(); // ✅ semicolon added

        return response()->json([
            'success' => true,
            'data' => $slider,
        ])->withHeaders($headers);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

	
	public function duration_view()
{
    $slider = DB::table('duration')->select('*')->get();

    return response()->json([
        'success' => true,
        'data' => $slider,
    ], 200);
}

	
   public function zone_Management()
    {
       
        $slider = DB::table('zone_Management')->select('*')->get();

      
        return response()->json([
            'success' => true,
            'data' => $slider,
        ], 200);
        
    }	
		
	 public function faqs_list()
    {
       
        $slider = DB::table('faqs')->select('*')->get();

      
        return response()->json([
            'success' => true,
            'data' => $slider,
        ], 200);
        
    }	
	
	
	public function commission_list()
{
    $data = DB::table('regcommission')
        ->orderBy('id', 'asc')
        ->get([
            'id',
            'name',
			'type'
            
        ]);

    return response()->json([
        'success' => true,
        'count'   => $data->count(),
        'data'    => $data
    ], 200);
}
	
public function service_view(Request $request)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'provider_id' => 'required|integer',
        'limit'       => 'nullable',
        'offset'      => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $provider_id = $request->provider_id;

    // ✅ Convert limit/offset to integer with default
    $limit  = (int) ($request->input('limit') ?: 10);   // default 10
    $offset = (int) ($request->input('offset') ?: 0);   // default 0

    // Query Builder with JOIN
    $services = DB::table('services')
        ->select(
            'services.id',
            'services.category_id',
            'services.subcategory_id',
            'services.name',
            'services.description',
            'services.image',
            'services.price',
            'services.discount',
            'services.duration',
            'services.provider_id',
            'services.status',
            'services.created_at',
            'services.updated_at',
            'services.duration_mint',
            'services.city',
            'services.type',
            'services.handyman_id',
            'services.address',
            'services.user_id',
            'services.tax',
            'services.mrp_price',
            'services.services_id',
            'services.ciry',
            'subcategories.name as subcategory_name'
        )
        ->join('subcategories', 'subcategories.id', '=', 'services.subcategory_id')
        ->where('services.provider_id', $provider_id)
        ->offset($offset)
        ->limit($limit)
        ->get();

    // No data case
    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data'    => []
        ], 200);
    }

    // Success response
    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data'    => $services
    ], 200);
}


public function help_support_view(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $user_id = $request->user_id;
  

    $services = DB::table('help_support')
        ->where('user_id', $user_id)
        ->get();

    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $services
    ], 200);
}	
public function bookings_view(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'nullable|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $user_id = $request->input('user_id');

    // ✅ Build query using Query Builder properly
    $query = DB::table('bookings')
        ->select(
            'bookings.*',
            'services.image',
            'services.name as servicesname',
            'user_details.full_name',
            'user_details.email',
            'user_details.image as provider_image',
            'user_details.language',
            'user_details.skill',
		    'user_details.address as provider_address',
		   'user_details.phone as provider_phone'
		    
        )
        ->leftJoin('services', 'services.id', '=', 'bookings.service_id')
        ->leftJoin('user_details', 'bookings.provideo_id', '=', 'user_details.id'); // ✅ Added join

    // ✅ Apply filter only if user_id is provided
    if (!empty($user_id)) {
        $query->where('bookings.user_id', $user_id);
    }

    // ✅ Fetch data
    $bookings = $query->orderBy('bookings.id', 'desc')->get();

    if ($bookings->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $bookings
    ], 200);
}
public function bookings_houseman_view(Request $request)
{
    $validator = Validator::make($request->all(), [
        'handyman_id' => 'nullable|integer'
    ]);
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }
    $handyman_id = $request->input('handyman_id');  
    $query = DB::table('bookings')
        ->select(
            'bookings.*',
            'services.image',
            'services.name as servicesname',
            'user_details.full_name',
            'user_details.email',
            'user_details.image as provider_image',
            'user_details.language',
            'user_details.skill',
            'user_details.address as provider_address',
            'user_details.phone as provider_phone'
        )
        ->leftJoin('services', 'services.id', '=', 'bookings.service_id')
        ->leftJoin('user_details', 'bookings.provideo_id', '=', 'user_details.id');
    // Apply filter if handyman_id present
    if (!empty($handyman_id)) {
        $query->where('bookings.handyman_id', $handyman_id);  // <-- FIXED
    }
    $bookings = $query->orderBy('bookings.id', 'desc')->get();
    if ($bookings->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $bookings
    ], 200);
}	
public function bookings_details(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'nullable|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $id = $request->input('id');

    // ✅ Build query using Query Builder properly
    $query = DB::table('bookings')
        ->select(
            'bookings.*',
            'services.image',
            'services.name as servicesname',
            'user_details.full_name',
            'user_details.email',
            'user_details.image as provider_image',
            'user_details.language',
            'user_details.skill',
		    'user_details.address as provider_address',
		   'user_details.phone as provider_phone'
		    
        )
        ->leftJoin('services', 'services.id', '=', 'bookings.service_id')
        ->leftJoin('user_details', 'bookings.provideo_id', '=', 'user_details.id'); // ✅ Added join

    // ✅ Apply filter only if user_id is provided
    if (!empty($user_id)) {
        $query->where('bookings.id', $id);
    }

    // ✅ Fetch data
    $bookings = $query->orderBy('bookings.id', 'desc')->get();

    if ($bookings->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $bookings
    ], 200);
}	

public function services_details(Request $request)
{
    /* ===========================
       ✅ VALIDATION
       =========================== */
    $validator = Validator::make($request->all(), [
        'servicesid' => 'required|integer',
		 'providers_id' => 'nullable|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $servicesid = $request->servicesid;
	$providers_id = $request->providers_id;

    /* ===========================
       ✅ MAIN SERVICE QUERY
       =========================== */
    $service = DB::table('services as s')
        ->leftJoin('favorites as f', 'f.service_id', '=', 's.id')
        ->leftJoin('user_details as u', 'u.id', '=', 's.provider_id')
        ->leftJoin('team as t', 't.providers_id', '=', 's.provider_id')
        ->leftJoin('user_details as handy', 'handy.id', '=', 't.handymans_id')
        ->leftJoin('user_details as provide', 'provide.id', '=', 't.providers_id')
        ->where('s.id', $servicesid)
        ->select(
            's.*',
            'f.status as favoritesstatus',

            'u.zone_management',
            'u.full_name as providername',
            'u.phone as providermobile',
            'u.email as provideremail',
            'u.image as providerimage',
            'u.language as providerlanguage',
            'u.created_at as providerdate',
            't.handymans_id',
            't.providers_id',
            'handy.full_name as handyman_name',
            'handy.email as handyman_email',
            'handy.phone as handyman_phone',
            'handy.image as handyman_image'
        )
        ->first();

    if (!$service) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

   
   
  $zone = $service->zone_management;
    if (empty($zone)) {
        $service->zone_management = [];
    } else {
        $decodedZone = json_decode($zone, true);
        if (is_string($decodedZone)) {
            $decodedZone = json_decode($decodedZone, true);
        }

        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedZone)) {
            $cleanZone = [];
            foreach ($decodedZone as $item) {
                if (is_array($item)) {
                    foreach ($item as $value) {
                        $cleanZone[] = $value;
                    }
                } else {
                    $cleanZone[] = $item;
                }
            }
            $service->zone_management = $cleanZone;
        } else {
            $service->zone_management = [];
        }
    }

    /* ===========================
       ✅ ADDON SERVICES
       =========================== */
    $addonServices = DB::table('addon_service')
        ->select(
            'image',
            'name',
            'services_id',
            'price',
            'status',
            'date',
            'provider_id'
        )
        ->where('services_id', $servicesid)
        ->get();

    $service->addon_services = $addonServices->isEmpty() ? [] : $addonServices;

    
    $providerId = $service->provider_id;

    $shops = DB::table('shops as sh')
        ->leftJoin('services as s', function ($join) {
            $join->whereRaw(
                "JSON_CONTAINS(sh.select_service_id, JSON_QUOTE(CAST(s.id AS CHAR)))"
            );
        })
        ->leftJoin('user_details as provide', 'provide.id', '=', 'sh.providers_id')
        ->where('sh.providers_id', $providerId)
        ->select(
            'sh.id',
            'sh.shop_image',
            'sh.shop_name',
            'sh.number',
            'sh.state',
            'sh.city',
            'sh.address',
            'sh.latitude',
            'sh.long',
            'sh.shopstarttime',
            'sh.shop_end_time',
            'sh.email',
            'sh.status',
            'sh.date',

            // Service
            's.id as service_id',
            's.name as service_name',
            's.description as service_description',
            's.image as service_image',
            's.price',
            's.discount',

            // Provider
            'provide.full_name as team_provider_name',
            'provide.email as team_provider_email',
            'provide.phone as team_provider_phone',
            'provide.image as team_provider_image',
		   'provide.language as providerlanguage',
		   'provide.created_at as providerdate'
        )
        ->get();

    $shopData = [];

    foreach ($shops as $row) {
        $shopData[] = [
            'id' => $row->id,
            'shop_name' => $row->shop_name,
            'shop_image' => $row->shop_image,
            'number' => $row->number,
            'state' => $row->state,
            'city' => $row->city,
            'address' => $row->address,
            'latitude' => $row->latitude,
            'long' => $row->long,
            'shopstarttime' => $row->shopstarttime,
            'shop_end_time' => $row->shop_end_time,
            'email' => $row->email,
            'status' => $row->status,
            'date' => $row->date,
           
            'service' => [
                'id' => $row->service_id,
                'name' => $row->service_name,
                'description' => $row->service_description,
                'image' => $row->service_image,
                'price' => $row->price,
                'discount' => $row->discount,
				
            ],
           'team' => [
        'name' => $row->team_provider_name,
        'email' => $row->team_provider_email,
        'phone' => $row->team_provider_phone,
        'image' => $row->team_provider_image,
    ]

            
        ];
    }

    $service->shop = empty($shopData) ? [] : $shopData;

    /* ===========================
       ✅ FINAL RESPONSE
       =========================== */
    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $service
    ], 200);
}


public function shops_details(Request $request)
{
    $validator = Validator::make($request->all(), [
        'providers_id' => 'required|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $providerId = $request->providers_id;

    $shops = DB::table('shops as sh')
        ->leftJoin('services as s', function ($join) {
            $join->whereRaw(
                "JSON_CONTAINS(sh.select_service_id, JSON_QUOTE(CAST(s.id AS CHAR)))"
            );
        })
        ->where('sh.providers_id', $providerId)
        ->select(
            'sh.id',
            'sh.shop_image',
            'sh.shop_name',
            'sh.number',
            'sh.state',
            'sh.city',
            'sh.address',
            'sh.latitude',
            'sh.long',
            'sh.shopstarttime',
            'sh.shop_end_time',
            'sh.email',
            'sh.select_service_id',
            'sh.reg_number',
            'sh.providers_id',
            'sh.status',
            'sh.date',
            'sh.created_at',
            'sh.updated_at',

            's.id as service_id',
            's.name as service_name',
            's.description as service_description',
            's.image as service_image',
            's.price',
            's.discount'
        )
        ->get();

    if ($shops->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $shops
    ], 200);
}


		

public function add_categories(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'         => 'required',
        'provider_id'  => 'nullable',
        'image_base64' => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error'   => $validator->errors()->first()
        ], 200);
    }

    $baseUrl = 'https://admin.mishtiry.com/public';
    $now = Carbon::now('Asia/Kolkata');
    $imageUrl = null;

   
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/services'), $fileName);
        $imageUrl = $baseUrl . '/uploads/services/' . $fileName;
    }

    
    elseif ($request->filled('image_base64')) {
        $imageData = base64_decode($request->input('image_base64'));
        $imageName = uniqid() . '.png';
        $uploadPath = public_path('uploads/services/');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        file_put_contents($uploadPath . $imageName, $imageData);
        $imageUrl = $baseUrl . '/uploads/services/' . $imageName;
    }

   
    $id = DB::table('categories')->insertGetId([
        'name'       => $request->name,
		'provider_id'=> $request->provider_id,
        'image'      => $imageUrl,
        'status'     => '1',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

  
    $newCategory = DB::table('categories')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'message' => 'Category added successfully',
        'data'    => $newCategory
    ], 200);
}
	
public function add_sub_categories(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'         => 'required',
        'category_id'  => 'required',
		'provider_id'  => 'nullable',
        'image_base64' => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error'   => $validator->errors()->first()
        ], 200);
    }

    $baseUrl = 'https://admin.mishtiry.com/public';
    $now = Carbon::now('Asia/Kolkata');
    $imageUrl = null;

    // ✅ File Upload
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/services'), $fileName);
        $imageUrl = $baseUrl . '/uploads/services/' . $fileName;
    }

    // ✅ Base64 Image Upload
    elseif ($request->filled('image_base64')) {
        $imageData = base64_decode($request->input('image_base64'));
        $imageName = uniqid() . '.png';
        $uploadPath = public_path('uploads/services/');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        file_put_contents($uploadPath . $imageName, $imageData);
        $imageUrl = $baseUrl . '/uploads/services/' . $imageName;
    }

    // ✅ Insert New Category
    $id = DB::table('subcategories')->insertGetId([
        'name'       => $request->name,
		'provider_id'=> $request->provider_id,
        'image'      => $imageUrl,
        'status'     => '1',
		'category_id' => $request->category_id,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // ✅ Fetch newly created record
    $newCategory = DB::table('subcategories')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'message' => ' added successfully',
        'data'    => $newCategory
    ], 200);
}

	

// Controller
public function bookings_status(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'nullable|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 200);
    }

    $id = $request->input('id');

    $query = DB::table('bookings');

    if (!empty($id)) {
        $query->where('bookings.id', $id);
    }

    $bookings = $query->orderBy('bookings.id', 'desc')->get();

    if ($bookings->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => [],
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $bookings,
    ], 200);
}


public function bookingshistory(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'nullable|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $user_id = $request->input('user_id');

    // ✅ Build query using Query Builder properly
    $query = DB::table('bookings')
        ->select(
            'bookings.*',
            'services.image',
            'services.name as servicesname',
            'user_details.full_name',
            'user_details.email',
            'user_details.image as provider_image',
            'user_details.language',
            'user_details.skill',
	     	'user_details.address as provider_address',
		   'user_details.phone as provider_phone'
        )
        ->leftJoin('services', 'services.id', '=', 'bookings.service_id')
        ->leftJoin('user_details', 'bookings.provideo_id', '=', 'user_details.id');

    // ✅ Apply filters
    if (!empty($user_id)) {
        $query->where('bookings.user_id', $user_id)
              ->where('bookings.status', 3); // ✅ Correct chaining
    } else {
        // Optional: If you want all completed bookings even without user_id
        $query->where('bookings.status', 3);
    }

    // ✅ Fetch data
    $bookings = $query->orderBy('bookings.id', 'desc')->get();

    if ($bookings->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $bookings
    ], 200);
}
public function booking_cancel(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $bookingId = $request->input('id');

    // ✅ Check if booking exists
    $booking = DB::table('bookings')->where('id', $bookingId)->first();

    if (!$booking) {
        return response()->json([
            'success' => false,
            'message' => 'No booking found with the given ID',
            'data' => []
        ], 200);
    }

    // ✅ Update booking status to 7 (Cancelled)
    DB::table('bookings')
        ->where('id', $bookingId)
        ->update(['status' => 7]);

    // ✅ Return success response
    return response()->json([
        'success' => true,
        'message' => 'Booking cancelled successfully',
        'data' => [
            'id' => $bookingId,
            'status' => 7
        ]
    ], 200);
}

	
public function service_view_users(Request $request)
{
    $validator = Validator::make($request->all(), [
        'category_id'    => 'required|integer',
        'subcategory_id' => 'nullable|integer',
        'limit'          => 'nullable|integer|min:1',
        'offset'         => 'nullable|integer|min:0',
        'namesearch'     => 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $category_id    = (int) $request->category_id;
    $subcategory_id = $request->subcategory_id;
    $limit          = (int) $request->input('limit', 10);
    $offset         = (int) $request->input('offset', 0);

    // Base Query
    $query = DB::table('services')
        ->select(
            'services.*',
            'user_details.zone_management',
            'favorites.status as favorites_status',
            'user_details.faqs',
            'user_details.skill',
            'user_details.about',
            'reviews.rate'
        )
        ->leftJoin('user_details', 'user_details.id', '=', 'services.handyman_id')
        ->leftJoin('reviews', 'reviews.handymanid', '=', 'user_details.id')
        ->leftJoin('favorites', 'favorites.service_id', '=', 'services.id');

    // Filter category
    if ($category_id !== 0) {
        $query->where('services.category_id', $category_id);
    }

    // Filter subcategory
    if (!empty($subcategory_id)) {
        $query->where('services.subcategory_id', $subcategory_id);
    }

    // 🔥 Search by subcategory_name (LIKE) — ONLY CHANGE
    if (!empty($request->namesearch)) {
        $query->where('services.subcategory_name', 'LIKE', '%' . $request->namesearch . '%');
    }

    // ORDER BY
    $query->orderBy('services.subcategory_id', 'ASC');

    // Pagination
    $services = $query->offset($offset)->limit($limit)->get();

    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    // Decode JSON fields
    $services->transform(function ($item) {
        $jsonFields = ['skill', 'zone_management', 'faqs'];

        foreach ($jsonFields as $field) {
            $value = $item->$field;

            if (!empty($value)) {
                if (strpos($value, '["id":') !== false) {
                    $value = str_replace('["id":', '[{"id":', $value);
                    if (substr($value, -1) === ']') {
                        $value = preg_replace('/"\]$/', '"}]', $value);
                    }
                }

                if (self::checkJson($value)) {
                    $item->$field = json_decode($value);
                } else {
                    $item->$field = [];
                }
            } else {
                $item->$field = [];
            }
        }
        return $item;
    });

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $services
    ], 200);
}

public function service_random(Request $request)
{
    $services = DB::table('services')
        ->select(
            'services.*',
            'user_details.zone_management',
            'favorites.status as favorites_status',
            'user_details.faqs',
            'user_details.skill',
            'user_details.about',
            DB::raw('(SELECT AVG(rate) 
                      FROM reviews 
                      WHERE reviews.handymanid = services.handyman_id
                     ) as avg_rating')
        )
        ->leftJoin('user_details', 'user_details.id', '=', 'services.handyman_id')
        ->leftJoin('favorites', 'favorites.service_id', '=', 'services.id')
        ->inRandomOrder()
        ->limit(10)
        ->get();

    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $services
    ], 200);
}

public function service_view_filter(Request $request)
{
    $validator = Validator::make($request->all(), [
        'category_id' => 'required|integer',
        'limit'       => 'nullable|integer',
        'offset'      => 'nullable|integer',
        'servicesid'  => 'nullable|integer',
        'provider_id' => 'nullable|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 200);
    }

    $category_id = (int) $request->category_id;
    $limit       = $request->input('limit', 10);
    $offset      = $request->input('offset', 0);
    $servicesid  = $request->input('servicesid');
    $provider_id = $request->input('provider_id');

    // ✅ Base query
    $query = DB::table('services')
        ->select(
            'services.*',
            'user_details.zone_management',
            'favorites.status as favorites_status',
            'user_details.faqs',
            'user_details.skill',
            'user_details.about',
            'reviews.rate'
        )
        ->leftJoin('user_details', 'user_details.id', '=', 'services.handyman_id')
        ->leftJoin('reviews', 'reviews.handymanid', '=', 'user_details.id')
        ->leftJoin('favorites', 'favorites.service_id', '=', 'services.id');

    // ✅ Apply filters conditionally
    if ($category_id !== 0) {
        $query->where('services.subcategory_id', $category_id);
    }

    if (!empty($servicesid)) {
        $query->where('services.id', $servicesid);
    }

    if (!empty($provider_id)) {
        $query->where('services.provider_id', $provider_id);
    }

    // ✅ Pagination
    $services = $query->offset($offset)->limit($limit)->get();

    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => [],
        ], 200);
    }

    // ✅ Decode malformed JSON fields
    $services->transform(function ($item) {
        $jsonFields = ['skill', 'zone_management', 'faqs'];

        foreach ($jsonFields as $field) {
            $value = $item->$field ?? '';

            if (!empty($value)) {
                // Fix malformed data like ["id": "1", "name": "cleaning"]
                if (strpos($value, '["id":') !== false) {
                    $value = str_replace('["id":', '[{"id":', $value);

                    if (substr($value, -1) === ']') {
                        $value = preg_replace('/"\]$/', '"}]', $value);
                    }
                }

                // Decode JSON if valid
                $item->$field = self::checkJson($value) ? json_decode($value) : [];
            } else {
                $item->$field = [];
            }
        }

        return $item;
    });

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $services,
    ], 200);
}


// ✅ Renamed helper
private static function checkJson($string)
{
    if (!is_string($string)) {
        return false;
    }

    json_decode($string);
    return (json_last_error() === JSON_ERROR_NONE);
}


// ✅ Helper to check valid JSON
private static function isJson($string)
{
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}


	

public function addon_view(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provider_id' => 'required|integer',
        'limit' => 'nullable|integer|min:1',
        'offset' => 'nullable|integer|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $provider_id = $request->provider_id;
    $limit = $request->input('limit', 10);   // default 10
    $offset = $request->input('offset', 0);  // default 0

    $services = DB::table('addon_service')
        ->where('provider_id', $provider_id)
        ->offset($offset)
        ->limit($limit)
        ->get();

    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $services
    ], 200);
}
public function banklist(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $userid = $request->userid;

    $banks = DB::table('bank_accounts')
        ->where('userid', $userid)
        ->orderBy('id', 'desc') // latest first (optional)
        ->get();

    if ($banks->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data'    => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data'    => $banks
    ], 200);
}
public function coupons_list()
{
   
    $coupons = DB::table('coupons')
        ->orderBy('id', 'desc') // latest first
        ->get();

    if ($coupons->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data'    => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data'    => $coupons
    ], 200);
}

	
	
public function verifyid_view(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provider_id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $provider_id = $request->provider_id;

    $services = DB::table('verify_id')
        ->where('provider_id', $provider_id)
        ->get();

    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $services
    ], 200);
}
public function settings(Request $request)
{
    $validator = Validator::make($request->all(), [
        'type' => 'required',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }
    $setting = DB::table('settings')
        ->select('id', 'name', 'value', 'status', 'type', 'modal_type', 'created_at')
        ->where('type', $request->type)
        ->first();  

    return response()->json([
        'success' => true,
        'data' => $setting,
    ], 200);
}
	
public function services_book(Request $request)
{
    $validator = Validator::make($request->all(), [
        'service_id'   => 'required|string',
        'handyman_id'  => 'required|string',
        'address'      => 'required|string',
        'quantity'     => 'nullable|integer',
        'coupons_id'   => 'nullable|integer',
        'slot_id'      => 'nullable|integer',
        'price'        => 'nullable|numeric',
        'user_id'      => 'required|integer',
        'total_amount' => 'required|numeric',
        'payment_mode' => 'nullable|numeric',
        'tax'          => 'nullable|numeric',
		'addon_id'     => 'nullable',
		'provideo_id'     => 'nullable|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $totalAmount = $request->input('total_amount');
    $couponDiscount = 0;

    /* ------------------------------
        COUPON VALIDATION START
    ------------------------------ */

    if ($request->coupons_id) {

        $coupon = DB::table('coupons')
            ->select('id','code','title','description','min_order_amount','amount','start_date','end_date')
            ->where('id', $request->coupons_id)
            ->first();

        if ($coupon) {

            // Current Date
            $today = date('Y-m-d');

            if ($coupon->end_date >= $today) {  // coupon valid
                if ($totalAmount >= $coupon->min_order_amount) {

                    // Apply discount amount
                    $couponDiscount = $coupon->amount;

                    // Reduce total amount
                    $totalAmount = $totalAmount - $couponDiscount;

                    if ($totalAmount < 0) {
                        $totalAmount = 0; // safety
                    }
                }
            }
        }
    }

    /* ------------------------------
        COUPON VALIDATION END
    ------------------------------ */

    // Get Commission %
    $commission = DB::table('commission')
        ->select('id', 'commission_providers', 'commission_handymans', 'commission_admin')
        ->first();

    if (!$commission) {
        return response()->json([
            'status'  => false,
            'message' => 'Commission rates not found.'
        ], 200);
    }

    // Now commissions will apply on updated totalAmount
    $providerCommission = ($totalAmount * $commission->commission_providers) / 100;
    $handymanCommission = ($totalAmount * $commission->commission_handymans) / 100;
    $adminCommission    = ($totalAmount * $commission->commission_admin) / 100;

    $finalAmount = $totalAmount - ($providerCommission + $handymanCommission + $adminCommission);

    // Insert booking
    $id = DB::table('bookings')->insertGetId([
        'service_id'          => $request->input('service_id'),
        'payment_mode'        => $request->input('payment_mode'),
        'handyman_id'         => $request->input('handyman_id'),
        'address'             => $request->input('address'),
        'quantity'            => $request->input('quantity'),
        'coupons_id'          => $request->input('coupons_id'),
        'slot_id'             => $request->input('slot_id'),
        'price'               => $request->input('price'),
        'user_id'             => $request->input('user_id'),
        'tax'                 => $request->input('tax'),
		'addon_id'            => $request->input('addon_id'),
		'provideo_id'         => $request->input('provideo_id'),
        'handymans_commission'=> $handymanCommission,
        'providers_commission'=> $providerCommission,
        'admin_commission'    => $adminCommission,
        'coupon_discount'     => $couponDiscount,   // NEW FIELD (if you want)
        'total_amount'        => $totalAmount,
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Service booked successfully.',
        'id'      => $id,
        'data'    => [
            'total_amount'        => $totalAmount,
            'coupon_discount'     => $couponDiscount,
            'provider_commission' => $providerCommission,
            'handyman_commission' => $handymanCommission,
            'admin_commission'    => $adminCommission,
            'final_amount'        => $finalAmount,
        ]
    ], 200);
}

	

public function housman_view()
{
    $slider = DB::table('user_details')
                ->where('role_id', 1)
                ->get();

    return response()->json([
        'success' => true,
        'data' => $slider, // agar data na hoga to [] aayega
    ], 200);
}	
    
    public function refund_policy ()
    {
       
        $refund = DB::table('refund_policy')->select('id','description')->first();

        // Return the sliders as a JSON response
        return response()->json([
            'success' => true,
            'data' => $refund,
        ], 200);
        
    }
    
    
    public function policy(Request $request)
{
    // ✅ Validate type
    $validator = Validator::make($request->all(), [
        'type' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $type = $request->type;

    // ✅ Fetch SINGLE record
    $data = DB::table('settings')
        ->select(
            'id',
            'name',
            'value',
            'status',
            'type',
            'modal_type'
        )
        ->where('type', $type)
        ->where('status', 1)
        ->first(); // 🔥 IMPORTANT

    if (!$data) {
        return response()->json([
            'success' => false,
            'message' => 'No policy found',
            'data'    => null
        ], 200);
    }

    return response()->json([
        'success' => true,
        'data'    => $data
    ], 200);
}

    
      public function about_app ()
    {
       
        $about = DB::table('about_app')->select('id','heading','moto','image','description')->get();

        // Return the sliders as a JSON response
        return response()->json([
            'success' => true,
            'data' => $about,
        ], 200);
        
    }

   public function term_condition()
    {
       
        $tm = DB::table('terms_condition')->select('id','description')->get();

        // Return the sliders as a JSON response
        return response()->json([
            'success' => true,
            'data' => $tm,
        ], 200);
        
    }

 public function help_support()
    {
       
        $hs = DB::table('help_support')->select('id','title','description','phone','email')->get();

        // Return the sliders as a JSON response
        return response()->json([
            'success' => true,
            'data' => $hs,
        ], 200);
        
    }


 public function storeblog(Request $request)
   {
  $validator = Validator::make($request->all(), [
    'title' => 'required|string|max:255',
    'description' => 'required|string|max:255',
     'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
     'image_base64' => 'nullable|string',
      'publish_date' => 'nullable',
     'provider_id' => 'required|exists:providers_details,id',
    
]);


    // Return validation errors if any
    if ($validator->fails()) {
        return response()->json($validator->errors(), 200);
    }


    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images', 'public');
        $imagePath = 'https://handyman.mobileappdemo.net/public/' . basename($imagePath); 
    }

    if ($request->input('image_base64')) {
        $imageData = base64_decode($request->input('image_base64'));
        $imageName = 'image/' . uniqid() . '.png'; 
        file_put_contents(public_path($imageName), $imageData);

        $imagePath = 'https://handyman.mobileappdemo.net/public/' . $imageName;
    }


    $serviceData = [
        'title' => $request->input('title'),
        'description' => $request->input('description'),
        'image' => $imagePath,
        'publish_date' => $request->input('publish_date'),
        'provider_id'=>$request->input('provider_id'),
        
    
    ];
    
 
    $insertedId = DB::table('blogs')->insertGetId($serviceData);

    if ($insertedId) {

        $insertedData = DB::table('blogs')->find($insertedId);

        return response()->json([
            'success' => true,
            'message' => 'blogs created successfully',
            'data' => $insertedData,
        ], 200);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create blogs',
        ], 200);
    }

}

public function address_servics(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provider_id' => 'required',
        'address' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $provider_id = $request->provider_id;
    $address = $request->address;
    $date = now();

    // Insert query with DB::table
    $id = DB::table('address_services')->insertGetId([
        'provider_id' => $provider_id,
        'address'     => $address,
        'status'      => 1,
        'created_at'  => $date
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Successfully inserted',
        'id' => $id,
    ], 200);
}

public function address_update(Request $request)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'id'        => 'required',
        'address'   => 'nullable',
        'lat'       => 'nullable',
        'long'      => 'nullable',
        'house_no'  => 'nullable',
        'date'      => 'nullable',
        'type'      => 'nullable',
        'landmark'  => 'nullable',
        'mobile'    => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    try {
        // ✅ Build dynamic update array
        $updateData = [];
        $fields = ['address', 'lat', 'long', 'house_no', 'date', 'type', 'landmark', 'mobile'];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->$field;
            }
        }

        if (empty($updateData)) {
            return response()->json([
                'status' => false,
                'message' => 'No data provided to update.'
            ], 422);
        }

        // ✅ Perform update
        DB::table('address')
            ->where('id', $request->id)
            ->update($updateData);

        return response()->json([
            'status' => true,
            'message' => 'Address updated successfully'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}

public function address_default(Request $request)
{
    // Validate input
    $validator = Validator::make($request->all(), [
        'id' => 'required',
        'user_id' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    try {
        $userId = $request->user_id;
        $addressId = $request->id;

      
        $check = DB::table('address')
            ->where('id', $addressId)
            ->where('user_id', $userId)
            ->first();

        if (!$check) {
            return response()->json([
                'status' => false,
                'message' => 'Address not found for this user.'
            ], 404);
        }

      
        DB::table('address')
            ->where('user_id', $userId)
            ->update(['default' => 0]);

      
        DB::table('address')
            ->where('id', $addressId)
            ->update(['default' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Default address updated successfully.'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}
	
public function address_on_off(Request $request)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer|exists:address_services,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    try {
        // Pehle current status nikaal lo
        $currentStatus = DB::table('address_services')
            ->where('id', $request->id)
            ->value('status');

        // Agar record nahi mila
        if ($currentStatus === null) {
            return response()->json([
                'status' => false,
                'message' => 'Record not found'
            ], 404);
        }

        // Toggle status: 1->0, 0->1
        $newStatus = $currentStatus == 1 ? 0 : 1;

        DB::table('address_services')
            ->where('id', $request->id)
            ->update([
                'status' => $newStatus,
            ]);

        return response()->json([
            'status' => true,
            'message' => ' successfully',
            'new_status' => $newStatus
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}

	
	
public function address_view(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $data = DB::table('address')
                ->where('user_id', $request->user_id)
                ->get();

    return response()->json([
        'status' => true,
        'message' => 'Data fetched successfully',
        'data' => $data,
    ], 200);
}

public function address_delete(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer|exists:address,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200); // 422 = Unprocessable Entity
    }

    // Delete the record
    DB::table('address')->where('id', $request->id)->delete();

    return response()->json([
        'status' => true,
        'message' => 'Address deleted successfully.'
    ], 200);
}

	
public function address_servics_delete(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // pehle check kar lo record exist karta hai ya nahi
    $record = DB::table('address')->where('id', $request->id)->first();

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'Record not found'
        ], 404);
    }

    // delete kar do
    DB::table('address')->where('id', $request->id)->delete();

    return response()->json([
        'status' => true,
        'message' => 'Record deleted successfully'
    ], 200);
}

public function services_delete(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // pehle check kar lo record exist karta hai ya nahi
    $record = DB::table('services')->where('id', $request->id)->first();

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'Record not found'
        ], 404);
    }

    // delete kar do
    DB::table('services')->where('id', $request->id)->delete();

    return response()->json([
        'status' => true,
        'message' => 'Record deleted successfully'
    ], 200);
}
public function account_delete(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // pehle check kar lo record exist karta hai ya nahi
    $record = DB::table('user_details')->where('id', $request->id)->first();

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'Record not found'
        ], 404);
    }

    // delete kar do
    DB::table('user_details')->where('id', $request->id)->delete();

    return response()->json([
        'status' => true,
        'message' => 'Account Deleted Successfully'
    ], 200);
}	
	
public function addon_delete(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // pehle check kar lo record exist karta hai ya nahi
    $record = DB::table('addon_service')->where('id', $request->id)->first();

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'Record not found'
        ], 404);
    }

    // delete kar do
    DB::table('addon_service')->where('id', $request->id)->delete();

    return response()->json([
        'status' => true,
        'message' => 'Record deleted successfully'
    ], 200);
}

public function verifyid_delete(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // pehle check kar lo record exist karta hai ya nahi
    $record = DB::table('verify_id')->where('id', $request->id)->first();

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'Record not found'
        ], 404);
    }

    // delete kar do
    DB::table('verify_id')->where('id', $request->id)->delete();

    return response()->json([
        'status' => true,
        'message' => 'Record deleted successfully'
    ], 200);
}
	

public function getblogs(Request $request)
{
    $provider_id = $request->provider_id;

    if (!$provider_id) {
        return response()->json([
            'status' => false,
            'error' => 'Provider ID is required.'
        ], 200); 
    }

    $query = DB::table('blogs')
                           ->where('provider_id', $provider_id)
                           ->select('id', 'title', 'description', 'image', 'publish_date','provider_id')
                           ->get();
    
    if ($query->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No blogs found for this provider ID.'
        ], 200); 
    }

    return response()->json([
        'status' => true,
        'message' => 'blogs fetched successfully.',
        'data' => $query 
        
    ], 200);
}






public function blog()
{
    // Fetch blogs with author details including the author's image
    $blogs = DB::table('blogs')
        ->join('providers_details', 'blogs.provider_id', '=', 'providers_details.id') // Join blogs with authors table
        ->select(
            'blogs.id', 
            'blogs.title', 
            'providers_details.full_name as author_name', // Fetch author's name
            'providers_details.image as author_image', // Fetch author's image
            'blogs.description', 
            'blogs.image as blog_image', // Fetch blog's image
            'blogs.views', 
            'blogs.publish_date'
        )
        ->get();

    // Increment views for each blog
    foreach ($blogs as $blog) {
        DB::table('blogs')
            ->where('id', $blog->id)
            ->increment('views', 1); 
    }

    // Fetch updated blogs with joined author details
    $updatedBlogs = DB::table('blogs')
    
        ->join('providers_details', 'blogs.provider_id', '=', 'providers_details.id') // Ensure the correct column is used here
        ->select(
            'blogs.id', 
            'blogs.title', 
            'providers_details.full_name as author_name', 
            'providers_details.image as author_image', 
            'blogs.description', 
            'blogs.image as blog_image', 
            'blogs.views', 
            'blogs.publish_date'
        )
        
        ->get();

    // Check if $updatedBlogs is empty
    if ($updatedBlogs->isNotEmpty()) {
        return response()->json([
            'success' => true,
            'data' => $updatedBlogs,
        ], 200);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'No blogs found.',
        ], 200);
    }
}

 public function removeblogs($id)
    {
        $blogs = DB::table('blogs')->where('id', $id)->first();

        if (!$blogs) {
            return response()->json([
                'success' => false,
                'message' => 'blogs not found'
            ], 200);
        }

        $deleted = DB::table('blogs')->where('id', $id)->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'blogs deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete blogs'
            ], 200);
        }
    } 
public function teamlist(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provideo_id' => 'required|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'data' => []
        ], 422);
    }

    $provideo_id = $request->provideo_id;

    $team = DB::table('bookings')
        ->join('user_details', 'bookings.handyman_id', '=', 'user_details.id')
        ->select(
            'bookings.id as booking_id',
            'bookings.handyman_id',
            'user_details.full_name',
            'user_details.email',
            'user_details.phone',
            'user_details.image'
        )
        ->where('bookings.provideo_id', $provideo_id)
        ->get();

    if ($team->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No team found for this provider',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Team fetched successfully',
        'data' => $team
    ], 200);
}
	
public function reviews_list_handyman(Request $request)
{
    $validator = Validator::make($request->all(), [
        'handyman_id' => 'required|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'data' => []
        ], 422);
    }

    $handyman_id = $request->handyman_id;

    $reviews = DB::table('reviews')
        ->join('services', 'reviews.services_id', '=', 'services.id')
        ->where('reviews.handymanid', $handyman_id)
        ->select(
            'reviews.id',
            'reviews.rate',
            'reviews.user_id',
            'reviews.handymanid',
            'reviews.status',
            'reviews.created_at',
            'reviews.comment',
            'reviews.services_id',
            'services.name',
            'services.image',
            'services.subcategory_name',
            'services.description',
            'services.price'
        )
        ->orderBy('reviews.id', 'DESC')
        ->get();

    if ($reviews->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No reviews found for this handyman',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Fetched successfully',
        'data' => $reviews
    ], 200);
}

	
public function home_provider($provider_id)
{
    $total_booking = DB::table('bookings')
        ->where('provideo_id', $provider_id)
        ->where('status', 3)
        ->count();

    $total_service = DB::table('services')
        ->where('provider_id', $provider_id)
        ->count();

    $remaining_payout = DB::table('services')
         ->where('provider_id', $provider_id)
        ->count();
        

    $total_revenue = DB::table('services')
		 ->where('provider_id', $provider_id)
        ->count();
        

    return response()->json([
        'success' => true,
        'Total_cash_in_hand'=>'200',
        'message' => 'Provider Home Stats',
        'total_booking'     => $total_booking,
        'total_service'     => $total_service,
        'remaining_payout'  => $remaining_payout,
        'total_revenue'     => $total_revenue,
    ], 200);
}
public function home_houseman($handyman_id)
{
    // Total bookings of this handyman
    $total_booking = DB::table('bookings')
        ->where('handyman_id', $handyman_id)
        ->count();

    // Total completed bookings (status = 3)
    $total_complete_booking = DB::table('bookings')
        ->where('handyman_id', $handyman_id)
        ->where('status', 3)
        ->count();

    // Remaining payout = SUM(price) - SUM(paid_amount)
    $remaining_payout = DB::table('bookings')
        ->where('handyman_id', $handyman_id)
        ->where('status', 3)
        ->count();

    // Total revenue earned from completed bookings
    $total_revenue = DB::table('bookings')
        ->where('handyman_id', $handyman_id)
        ->where('status', 3)
        ->count();

    return response()->json([
        'success' => true,
        'Total_cash_in_hand' => 0, // static? change if needed
        'message' => 'Houseman Home Stats',

        'total_booking'        => $total_booking,
        'total_complete_booking' => $total_complete_booking,
        'remaining_payout'     => $remaining_payout,
        'total_revenue'        => $total_revenue,
    ], 200);
}

 public function reviews_delete($id)
    {
        $blogs = DB::table('reviews')->where('id', $id)->first();

        if (!$blogs) {
            return response()->json([
                'success' => false,
                'message' => 'reviews not found'
            ], 200);
        }

        $deleted = DB::table('reviews')->where('id', $id)->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Reviews deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reviews'
            ], 200);
        }
    } 
	public function shops_delete($id)
    {
        $blogs = DB::table('shops')->where('id', $id)->first();

        if (!$blogs) {
            return response()->json([
                'success' => false,
                'message' => 'reviews not found'
            ], 200);
        }

        $deleted = DB::table('shops')->where('id', $id)->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Reviews deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reviews'
            ], 200);
        }
    } 

public function slots_add(Request $request)
{
    // 🔹 Validation
    $validator = Validator::make($request->all(), [
        'id'            => 'nullable|integer',
        'providers_id'  => 'required|integer',
        'week_day'      => 'required',          
        'start_time'    => 'required|string',  
        'end_time'      => 'required|string',  
        'duration_mint' => 'required|integer|min:1',
        'slot'          => 'nullable',
		'duration_id'   => 'nullable',
        'status'        => 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // 🔹 Variables
    $providerId = $request->providers_id;
    $duration   = (int) $request->duration_mint;
    $status     = $request->status ?? 1;

    // 🔹 Normalize week_day → ARRAY
    $weekDays = is_array($request->week_day)
        ? $request->week_day
        : [$request->week_day];

    // 🔹 Parse Time
    try {
        $startTimeOriginal = Carbon::createFromFormat('h:i A', $request->start_time);
        $endTimeOriginal   = Carbon::createFromFormat('h:i A', $request->end_time);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid time format. Use h:i AM/PM'
        ], 200);
    }

    if ($startTimeOriginal >= $endTimeOriginal) {
        return response()->json([
            'success' => false,
            'message' => 'Start time must be less than end time'
        ], 200);
    }

    // 🔹 Generate Slots (ONCE – reuse for all days)
    $slotsArray = [];
    $typeId = 1;
    $startTime = $startTimeOriginal->copy();
    $endTime   = $endTimeOriginal->copy();

    while ($startTime->copy()->addMinutes($duration) <= $endTime) {

        $slotStart = $startTime->copy();
        $slotEnd   = $startTime->copy()->addMinutes($duration);

        $slotsArray[] = [
            'type_id'    => (string) $typeId,
            'title'      => 'Slot ' . $typeId,
            'start_time' => $slotStart->format('h:i A'),
            'end_time'   => $slotEnd->format('h:i A'),
            'duration'   => (string) $duration,
            'is_booked'  => false
        ];

        $typeId++;
        $startTime->addMinutes($duration);
    }

    if (empty($slotsArray)) {
        return response()->json([
            'success' => false,
            'message' => 'No slots generated'
        ], 200);
    }

    // 🔹 Insert / Update per week day
    $responseData = [];

    foreach ($weekDays as $weekDay) {

        $data = [
            'providers_id'  => $providerId,
            'week_day'      => $weekDay,
            'start_time'    => $request->start_time,
            'end_time'      => $request->end_time,
			 'duration_id'      => $request->duration_id,
            'duration_mint' => $duration,
            'slots'         => json_encode($slotsArray),
            'slot'          => is_array($request->slot)
                                ? json_encode($request->slot)
                                : $request->slot,
            'status'        => $status,
            'updated_at'    => now(),
        ];

        if (!empty($request->id)) {

            DB::table('slots')
                ->where('id', $request->id)
                ->update($data);

            $slotId  = $request->id;
            $message = 'Slots updated successfully';

        } else {

            $data['created_at'] = now();
            $slotId = DB::table('slots')->insertGetId($data);
            $message = 'Slots added successfully';
        }

        $responseData[] = [
            'id'           => (string) $slotId,
            'providers_id' => (string) $providerId,
            'week_day'     => $weekDay,
            'total_slots'  => count($slotsArray)
        ];
    }

    // 🔹 Final Response
    return response()->json([
        'success' => true,
        'message' => $message,
        'data'    => $responseData
    ], 200);
}

	
public function slots_list(Request $request)
{
    // 🔹 Validation
    $validator = Validator::make($request->all(), [
        'type'          => 'required|integer|in:1,2',
        'duration_id'            => 'required_if:type,1|integer',
        'duration_id' => 'required_if:type,2|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'data'    => null
        ], 422);
    }

    $type = $request->type;

    // 🔹 BASE QUERY
    $query = DB::table('slots')
        ->select(
            'id',
            'week_day',
            'status',
            'created_at',
            'updated_at',
            'start_time',
            'end_time',
            'duration_mint',
            'providers_id',
            'slot'
        );

    // =========================
    // 🔹 TYPE 1 → BY ID
    // =========================
  if ($type == 1) {

    $query->where('duration_id', $request->duration_id);

    // week_day tabhi lagao jab request me aaye
    if ($request->filled('week_day')) {
        $query->where('week_day', $request->week_day);
    }

    $slotData = $query->first();

    if (!$slotData) {
        return response()->json([
            'success' => false,
            'message' => 'No slots found',
            'data'    => [ 'slot' => []]
        ], 200);
    }

    // ✅ Safe JSON decode
    $slotData->slot = !empty($slotData->slot)
        ? json_decode($slotData->slot, true)
        : [];

    return response()->json([
        'success' => true,
        'message' => 'Fetched successfully',
        'data'    => $slotData
    ], 200);
}

    // =========================
    // 🔹 TYPE 2 → BY DURATION
    // =========================
    if ($type == 2) {

        $query->where('duration_id', $request->duration_id);
        $slotList = $query->get();

        if ($slotList->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No slots found',
                'data'    => []
            ], 200);
        }

        // Decode slot JSON for all records
        $slotList->transform(function ($item) {
            $item->slot = !empty($item->slot)
                ? json_decode($item->slot, true)
                : [];
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Fetched successfully',
            'data'    => $slotList
        ], 200);
    }
}

public function reviewslist($user_id)
{
    // Fetch reviews for the given user
    $reviews = DB::table('reviews')
        ->join('user_details', 'user_details.id', '=', 'reviews.handymanid')
        ->select(
            'reviews.id',
            'reviews.rate',
            'reviews.user_id',
            'reviews.handymanid',
            'reviews.status',
            'reviews.created_at',
            'reviews.comment',
            'reviews.services_id',
            'user_details.full_name',
            'user_details.image'
        )
        ->where('reviews.user_id', $user_id)
        ->get();

    if ($reviews->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No reviews found',
            'data'    => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'All reviews',
        'data'    => $reviews
    ], 200);
}


public function shopslist($providers_id)
{
    $shops = DB::table('shops')
        ->leftJoin('cities', 'cities.id', '=', 'shops.city')
        ->leftJoin('states', 'states.id', '=', 'shops.state')
        ->where('shops.providers_id', $providers_id)
        ->select(
            'shops.*',
            'cities.name as cityname',
            'states.name as statename'
        )
        ->get();

    if ($shops->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No shops found',
            'data'    => []
        ], 200);
    }

    // Decode service IDs and attach service details
    foreach ($shops as $shop) {
        $serviceIds = json_decode($shop->select_service_id, true);

        if (is_array($serviceIds) && !empty($serviceIds)) {
            $services = DB::table('services')
                ->whereIn('id', $serviceIds)
                ->select('id', 'name', 'image')
                ->get();
        } else {
            $services = [];
        }

        $shop->services = $services;
    }

    return response()->json([
        'success' => true,
        'message' => 'All shops for provider',
        'data'    => $shops
    ], 200);
}
public function login(Request $request)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'email' => 'required',
        'password' => 'required|string',
        'role_id' => 'required|integer',
        'fcm_tokens' => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => $validator->errors()->first(),
        ], 422);
    }

    // Get user
    $user = user_details::where([
                ['email', $request->email],
                ['role_id', $request->role_id],
                ['status', 1]
            ])->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found or inactive.',
        ], 404);
    }

    // Password check
    if ($request->password !== $user->password) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid password.',
        ], 401);
    }

    // Update FCM token
    if ($request->filled('fcm_tokens')) {
        $user->update([
            'fcm_tokens' => $request->fcm_tokens
        ]);
    }

    // 🔥 If role_id = 3 → No document check required
    if ($request->role_id == 3) {
        return response()->json([
            'success' => true,
            'message' => 'User logged in successfully.',
            'user' => $user,
            'document_status' => null,
        ], 200);
    }

    // Role-wise document check
    $column = 'user_id';

    $doc = DB::table('provider_handy_doc')
            ->where($column, $user->id)
            ->first();

    // Agar document hi nahi mila
    if (!$doc) {
        return response()->json([
            'success' => false,
            'message' => 'Please upload your documents. Approval pending.',
            'document_status' => 0,
        ], 403);
    }

    // Agar document verify nahi hua (status != 2)
    if ($doc->status != 2) {
        return response()->json([
            'success' => false,
            'message' => 'Your documents are not verified yet.',
            'document_status' => $doc->status,
        ], 403);
    }

    // Login success + Only status return
    return response()->json([
        'success' => true,
        'message' => 'User logged in successfully.',
        'user' => $user,
        'document_status' => $doc->status, // ✔️ Only status
    ], 200);
}

    
public function show($id)
{
    $user = DB::selectOne("
        SELECT 
            ud.id,
            ud.full_name,
            ud.email,
            ud.phone,
            ud.image,
            ud.address,
            ud.country,
            ud.state,
            ud.city,
            ud.wallet_amount,
            ud.created_at,
            ud.updated_at,
            ud.username,
            ud.password,
            ud.status,
            ud.role_id,
            ud.categories,
            ud.designation,
            ud.select_commission,
            ud.language,
            ud.skill,
            ud.about,
            ud.reasons,
            ud.zone_management,
            ud.provideo_id,
            ud.verification_status,
            ud.faqs,
            ud.fcm_tokens,
            ud.lat,
            ud.long,
            rc.name AS regcommissionname,
            rc.type AS regcommissiontype
        FROM user_details ud
        LEFT JOIN regcommission rc 
            ON ud.select_commission = rc.id
        WHERE ud.id = ?
        LIMIT 1
    ", [$id]);

    // ❌ User not found
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'No user found',
            'data'    => null
        ], 200);
    }

    // ✅ skill JSON decode
    $user->skill = !empty($user->skill)
        ? json_decode($user->skill, true)
        : [];

    // ✅ optional: frontend-friendly defaults
    $user->regcommissionname = $user->regcommissionname ?? '';
    $user->regcommissiontype = $user->regcommissiontype ?? '';

    return response()->json([
        'success' => true,
        'message' => 'User fetched successfully',
        'data'    => $user
    ], 200);
}

public function language_view()
{
    $languages = DB::table('language')->get();

    if ($languages->isEmpty()) {
        return response()->json([
            'success' => false,
            'data' => [],
            'message' => 'No languages found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $languages
    ], 200);
}

public function Change_password(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required',
        'old_password' => 'required',
        'password' => 'required',
        'confirm_password' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    // user fetch karo
    $user = DB::table('user_details')->where('id', $request->id)->first();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found'
        ], 404);
    }

    // ✅ old password check (DB vs request)
    if ($user->password !== $request->old_password) {
        return response()->json([
            'status' => false,
            'message' => 'Old password is incorrect'
        ], 400);
    }

    // new & confirm match
    if ($request->password !== $request->confirm_password) {
        return response()->json([
            'status' => false,
            'message' => 'Password and Confirm Password do not match'
        ], 400);
    }

    // old & new same na ho
    if ($request->old_password === $request->password) {
        return response()->json([
            'status' => false,
            'message' => 'New password cannot be same as old password'
        ], 400);
    }

    // update password
    DB::table('user_details')
        ->where('id', $request->id)
        ->update([
            'password' => $request->password,
        ]);

    return response()->json([
        'status' => true,
        'message' => 'Password changed successfully'
    ], 200);
}
	
	
public function profile_update(Request $request)
{
    // 🔹 User find
    $user = user_details::find($request->id);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 200);
    }

    // 🔹 Validation
    $validator = Validator::make($request->all(), [
        'full_name'    => 'nullable|string',
        'email'        => 'nullable|email',
        'phone'        => 'nullable|string',
        'address'      => 'nullable|string',
        'country'      => 'nullable|string',
        'state'        => 'nullable|string',
        'city'         => 'nullable|string',
        'designation'  => 'nullable|string',
        'language'     => 'nullable|string',
        'about'        => 'nullable|string',
        'skill'        => 'nullable|array',
        'image'        => 'nullable|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // 🔹 Update data
    $data = $request->only([
        'full_name',
        'email',
        'phone',
        'address',
        'country',
        'state',
        'city',
        'designation',
        'language',
        'about'
    ]);

    // 🔹 Skill JSON
    if ($request->filled('skill')) {
        $data['skill'] = json_encode($request->skill);
    }

    /* ================= BASE64 IMAGE PROCESSING ================= */
    if ($request->filled('image')) {
        $imageInput = $request->input('image');
        
        // Debug: Check what you're receiving
        \Log::info('Image input type: ' . gettype($imageInput));
        \Log::info('Image input first 100 chars: ' . substr($imageInput, 0, 100));
        
        // Check if it's a valid base64 image string
        if (str_starts_with($imageInput, 'data:image/')) {
            // 🔹 Folder path
            $folderPath = public_path('image');
            
            // 🔹 Create folder if not exists
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            
            // 🔹 Delete old image if exists
            if (!empty($user->image) && filter_var($user->image, FILTER_VALIDATE_URL)) {
                $oldFileName = basename($user->image);
                $oldImagePath = $folderPath . '/' . $oldFileName;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            try {
                // 🔹 Extract image extension and data
                $parts = explode(';base64,', $imageInput);
                
                if (count($parts) != 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid base64 format. Missing "base64," separator'
                    ], 200);
                }
                
                $imageTypeAux = explode('image/', $parts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                
                // 🔹 Decode base64
                $imageData = base64_decode($parts[1]);
                
                if ($imageData === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid base64 image data - decoding failed'
                    ], 200);
                }
                
                // 🔹 Generate unique filename
                $fileName = time() . '_' . rand(1000, 9999) . '.' . $imageType;
                $filePath = $folderPath . '/' . $fileName;
                
                // 🔹 Save image file
                if (file_put_contents($filePath, $imageData)) {
                    // 🔹 Store URL in database
                    $data['image'] = 'https://admin.mishtiry.com/public/image/' . $fileName;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to save image file'
                    ], 200);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing image: ' . $e->getMessage()
                ], 200);
            }
        } else {
            // If it's not base64, check if it's a URL
            if (filter_var($imageInput, FILTER_VALIDATE_URL)) {
                $data['image'] = $imageInput;
            } else {
                // Try to handle raw base64 without data URI prefix
                if (base64_decode($imageInput, true) !== false) {
                    // It's raw base64, add default image type
                    $folderPath = public_path('image');
                    
                    if (!file_exists($folderPath)) {
                        mkdir($folderPath, 0777, true);
                    }
                    
                    // Delete old image if exists
                    if (!empty($user->image) && filter_var($user->image, FILTER_VALIDATE_URL)) {
                        $oldFileName = basename($user->image);
                        $oldImagePath = $folderPath . '/' . $oldFileName;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    $imageData = base64_decode($imageInput);
                    $fileName = time() . '_' . rand(1000, 9999) . '.png';
                    $filePath = $folderPath . '/' . $fileName;
                    
                    if (file_put_contents($filePath, $imageData)) {
                        $data['image'] = 'https://admin.mishtiry.com/public/image/' . $fileName;
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid image format. Expected: 1. data:image/...base64,... OR 2. Valid URL OR 3. Raw base64 string'
                    ], 200);
                }
            }
        }
    }

    /* ================= END IMAGE PROCESSING ================= */

    // 🔹 Update user
    $user->update($data);

    // 🔹 Decode skill for response
    if ($user->skill) {
        $user->skill = json_decode($user->skill);
    }

    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user'    => $user
    ], 200);
}

	


public function available_status(Request $request)
{
    $userId = $request->input('userid');
    $user = user_details::find($userId);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    // Toggle status
    if ($user->status == 1) {
        $user->status = 2; // Online
        $msg = "Handyman available status: Online";
    } else {
        $user->status = 1; // Offline
        $msg = "Handyman available status: Offline";
    }

    $user->save();

    return response()->json([
        'success' => true,
        'message' => $msg,
        'status'  => $user->status
    ], 200);
}

	
	
	
public function verify_id(Request $request)
{
    $provider_id = $request->input('provider_id');
    $type = $request->input('type');
    $date = date('Y-m-d');
    $baseUrl = 'https://admin.mishtiry.com/public/';

    $input = [
        'type'        => $type,
        'provider_id' => $provider_id,
        'status'      => 1,
        'created_at'  => $date
    ];

    // File Upload
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('image'), $fileName);

        $input['image'] = $baseUrl . '/image/' . $fileName;
        \Log::info("Image uploaded (file): " . $input['image']);
    } 
    // Base64 Upload (allow "image" or "image_base64")
    elseif ($request->input('image') || $request->input('image_base64')) {
        $base64Image = $request->input('image') ?? $request->input('image_base64');
        
        // agar "data:image/png;base64,...." ke sath aaye to usko clean karo
        if (strpos($base64Image, 'base64,') !== false) {
            $base64Image = explode('base64,', $base64Image)[1];
        }

        $imageData = base64_decode($base64Image);
        $imageName = 'image/' . uniqid() . '.png';
        file_put_contents(public_path($imageName), $imageData);

        $input['image'] = $baseUrl . '/' . $imageName;
        \Log::info("Image uploaded (base64): " . $input['image']);
    }

    // Insert
    $id = DB::table('verify_id')->insertGetId($input);

    // Fetch
    $user = DB::table('verify_id')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'message' => 'Added successfully',
        'user'    => $user
    ], 200);
}

public function add_bank(Request $request)
{
    $provider_id   = $request->input('provider_id');
    $bankname      = $request->input('bankname');
    $branchname    = $request->input('branchname');
    $account_no    = $request->input('account_no');
    $ifsc          = $request->input('ifsc');
    $status        = $request->input('status');
   
    $date          = date('Y-m-d H:i:s');

    $input = [
        'userid'       => $provider_id,   // ✅ table me `userid` column hai
        'bankname'     => $bankname,
        'branchname'   => $branchname,    // ✅ key sahi kar diya
        'accountnumber'=> $account_no,
        'ifsc'         => $ifsc,
        'status'       => $status,
        'created_at'   => $date,
        'updated_at'   => $date,
    ];

    // ✅ Insert
    $id = DB::table('bank_accounts')->insertGetId($input);

    // ✅ Fetch
    $user = DB::table('bank_accounts')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'message' => 'Added successfully',
        'user'    => $user
    ], 200);
}
	
public function bankupdate(Request $request)
{
    $id            = $request->input('id');
    $bankname      = $request->input('bankname');
    $branchname    = $request->input('branchname');
    $account_no    = $request->input('account_no');
    $ifsc          = $request->input('ifsc');
    $status        = $request->input('status');
   

    // Update values
    $input = [
        'bankname'      => $bankname,
        'branchname'    => $branchname,
        'accountnumber' => $account_no,
        'ifsc'          => $ifsc,
        'status'        => $status,
        
    ];

    // ✅ Update query
    DB::table('bank_accounts')
        ->where('id', $id)
        ->update($input);

    // ✅ Fetch updated record
    $user = DB::table('bank_accounts')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'message' => 'Successfully',
        'user'    => $user
    ], 200);
}

public function ifsc(Request $request)
{ 
    $validator = Validator::make($request->all(), [
        'ifsc' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    $ifsc = $request->ifsc; 

    // External API call
    $response = Http::get("https://ifsc.razorpay.com/{$ifsc}");

    if ($response->failed()) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid IFSC code or API error',
            'data' => null
        ], 404);
    }

    $data = $response->json();

    return response()->json([
        'status' => true,
        'message' => 'IFSC details fetched successfully',
        'data' => $data
    ]);
}
public function verify_id_update(Request $request)
{
    $id = $request->input('id');
    $type = $request->input('type');
    $date = date('Y-m-d');
    $baseUrl = 'https://admin.mishtiry.com/public/';

    $input = [
        'type'   => $type,
        'status' => 2,
    ];

    // File Upload
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('image'), $fileName);

        $input['image'] = $baseUrl . 'image/' . $fileName;
        \Log::info("Image uploaded (file): " . $input['image']);
    } 
    // Base64 Upload (allow "image" or "image_base64")
    elseif ($request->input('image') || $request->input('image_base64')) {
        $base64Image = $request->input('image') ?? $request->input('image_base64');
        
        if (strpos($base64Image, 'base64,') !== false) {
            $base64Image = explode('base64,', $base64Image)[1];
        }

        $imageData = base64_decode($base64Image);
        $imageName = 'image/' . uniqid() . '.png';
        file_put_contents(public_path($imageName), $imageData);

        $input['image'] = $baseUrl . $imageName;
        \Log::info("Image uploaded (base64): " . $input['image']);
    }

    // ✅ Update record
    DB::table('verify_id')
        ->where('id', $id)   // jis provider ka record update karna hai
        ->update($input);

    // Fetch updated record
    $user = DB::table('verify_id')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'message' => 'Updated successfully',
        'user'    => $user
    ], 200);
}
	
public function addon_update(Request $request)
{
    // Get record by ID
    $id = $request->input('id');
    $addon = DB::table('addon_service')->where('id', $id)->first();

    if (!$addon) {
        return response()->json(['error' => 'Record not found'], 200);
    }

    // Validation
    $validator = Validator::make($request->all(), [
        'name'        => 'nullable|string',
        'image'       => 'nullable', // base64 ya file dono allow
        'price'       => 'nullable|numeric',
        'provider_id' => 'nullable|integer',
        'add_service' => 'nullable|string',
        'status'      => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error'   => $validator->errors()->first()
        ], 200);
    }

    $baseUrl = 'https://admin.mishtiry.com/public/';
    $updateData = [];

    // ✅ Normal Fields
    foreach (['name', 'price', 'provider_id', 'add_service', 'status'] as $field) {
        if ($request->filled($field)) {
            $updateData[$field] = $request->$field;
        }
    }

    // ✅ File Upload (if file type image)
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/services'), $fileName);

        $updateData['image'] = $baseUrl . '/uploads/services/' . $fileName;
    }

    // ✅ Base64 Image Upload
    if ($request->input('image_base64')) {
        $imageData = base64_decode($request->input('image_base64'));
        $imageName = uniqid() . '.png';
        $imagePath = public_path('uploads/services/' . $imageName);

        if (!file_exists(public_path('uploads/services'))) {
            mkdir(public_path('uploads/services'), 0777, true);
        }

        file_put_contents($imagePath, $imageData);

        $updateData['image'] = $baseUrl . '/uploads/services/' . $imageName;
    }

    // ✅ Update record
    DB::table('addon_service')->where('id', $id)->update($updateData);

    // ✅ Fetch updated record
    $updated = DB::table('addon_service')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'message' => 'Updated successfully',
        'data'    => $updated
    ], 200);
}
public function handyman_update(Request $request)
{
    $userId = $request->input('id');
    $user = user_details::find($userId);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 200);
    }
	
    $validator = Validator::make($request->all(), [
        'full_name'   => 'nullable|string',
        'email'       => 'nullable|email',
        'phone'       => 'nullable|string|max:15',
        'image'       => 'nullable|image|max:2048',
        'address'     => 'nullable|string',
        'country'     => 'nullable|string',
        'state'       => 'nullable|string',
        'city'        => 'nullable|string',
        'designation' => 'nullable',
		'select_commission' => 'nullable',
        'zone_management' => 'nullable|array', 
        'language'    => 'nullable',
        'about'       => 'nullable',
        'skill'       => 'nullable|array', 
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => $validator->errors()->first()
        ], 200);
    }

    $baseUrl = 'https://admin.mishtiry.com';
    $input = collect($request->only([
        'full_name', 'email', 'phone', 'address',
        'country', 'state', 'city', 'designation',
        'about', 'skill','select_commission','zone_management'
    ]))->filter(function ($value) {
        return $value !== null; 
    })->toArray();
    if (isset($input['skill']) && is_array($input['skill'])) {
        $input['skill'] = json_encode($input['skill']);
    }
    if ($request->hasFile('image')) {
        if ($user->image && file_exists(public_path('image/' . basename($user->image)))) {
            unlink(public_path('image/' . basename($user->image)));
        }

        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension(); 
        $file->move(public_path('image'), $fileName); 

        $input['image'] = '/public/image/' . $fileName;
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
    if ($user->skill) {
        $user->skill = json_decode($user->skill);
    }
    if (isset($input['zone_management']) && is_array($input['zone_management'])) {
    $input['zone_management'] = json_encode($input['zone_management']);
    }
	
    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user' => $user
    ], 200);
}	
public function categories_update(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id'          => 'required',
        'name'        => 'nullable',
        'image'       => 'nullable',
        'image_base64'=> 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $id = $request->input('id');
    $baseUrl = 'https://admin.mishtiry.com';

    // 🔍 Fetch category
    $category = DB::table('categories')->where('id', $id)->first();

    if (!$category) {
        return response()->json([
            'success' => false,
            'message' => 'Category not found'
        ], 200);
    }

    $updateData = [];

    // 📝 Update name
    if ($request->name) {
        $updateData['name'] = $request->name;
    }

    // 🖼 If image uploaded (file)
    if ($request->hasFile('image')) {

        // Delete old image
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        $file = $request->file('image');
        $fileName = uniqid().'.'.$file->getClientOriginalExtension();
        $file->move(public_path('image'), $fileName);

        $updateData['image'] = $baseUrl . '/public/image/' . $fileName;
    }

    // 🖼 Base64 image
    if ($request->image_base64) {

        $imageData = base64_decode($request->image_base64);
        $fileName = 'image/' . uniqid() . '.png';
        file_put_contents(public_path($fileName), $imageData);

        // Delete old image
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        $updateData['image'] = $baseUrl . '/public/' . $fileName;
    }

    // 🔄 Update Database
    DB::table('categories')->where('id', $id)->update($updateData);

    // Return updated category
    $updatedCategory = DB::table('categories')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'message' => 'Category updated successfully',
        'data'    => $updatedCategory
    ], 200);
}
public function subcategories_update(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id'          => 'required',
        'name'        => 'nullable',
        'image'       => 'nullable',
        'image_base64'=> 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $id = $request->input('id');
    $baseUrl = 'https://admin.mishtiry.com';

    // 🔍 Fetch category
    $category = DB::table('subcategories')->where('id', $id)->first();

    if (!$category) {
        return response()->json([
            'success' => false,
            'message' => 'Data not found'
        ], 200);
    }

    $updateData = [];

    // 📝 Update name
    if ($request->name) {
        $updateData['name'] = $request->name;
    }

    // 🖼 If image uploaded (file)
    if ($request->hasFile('image')) {

        // Delete old image
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        $file = $request->file('image');
        $fileName = uniqid().'.'.$file->getClientOriginalExtension();
        $file->move(public_path('image'), $fileName);

        $updateData['image'] = $baseUrl . '/public/image/' . $fileName;
    }

    // 🖼 Base64 image
    if ($request->image_base64) {

        $imageData = base64_decode($request->image_base64);
        $fileName = 'image/' . uniqid() . '.png';
        file_put_contents(public_path($fileName), $imageData);

        // Delete old image
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        $updateData['image'] = $baseUrl . '/public/' . $fileName;
    }

    // 🔄 Update Database
    DB::table('subcategories')->where('id', $id)->update($updateData);

    // Return updated category
    $updatedCategory = DB::table('subcategories')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'message' => 'Category updated successfully',
        'data'    => $updatedCategory
    ], 200);
}
	
public function reviews_update(Request $request)
{
    // Validate incoming request
    $validator = Validator::make($request->all(), [
        'id'      => 'required|integer|exists:reviews,id',
        'rate'    => 'nullable|numeric|min:1|max:5',
        'comment' => 'nullable|string|max:1000'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // Update review in database
    $updated = DB::table('reviews')
        ->where('id', $request->id)
        ->update([
            'rate'      => $request->rate,
            'comment'   => $request->comment,
           
        ]);

    if ($updated) {
        return response()->json([
            'status'  => true,
            'message' => 'Update Successfully',
            'id'      => $request->id,
        ], 200);
    } else {
        return response()->json([
            'status'  => false,
            'message' => 'No changes were made.',
        ], 200);
    }
}
	
public function services_update(Request $request)
{
    $id = $request->input('id');

    // Check service exist
    $service = DB::table('services')->where('id', $id)->first();
    if (!$service) {
        return response()->json(['error' => 'Service not found'], 200);
    }

    // Validation rules
    $validator = Validator::make($request->all(), [
        'category_id'     => 'nullable|integer',
        'subcategory_name'=> 'nullable|string|max:255',
        'name'            => 'nullable|string|max:255',
        'ciry'            => 'nullable|string|max:100',
        'image'           => 'nullable',
        'image_base64'    => 'nullable',
        'type'            => 'nullable|string|max:50',
        'status'          => 'nullable|string|max:20',
        'price'           => 'nullable|numeric',
        'discount'        => 'nullable|numeric',
        'duration'        => 'nullable|string',
        'duration_mint'   => 'nullable|integer',
        'description'     => 'nullable|string',
        'provider_id'     => 'nullable|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => $validator->errors()->first()
        ], 200);
    }

    $baseUrl = 'https://admin.mishtiry.com';

    // Collect input fields
    $input = $request->only([
        'category_id', 'subcategory_name', 'name', 'ciry', 'type', 'status',
        'price', 'discount', 'duration', 'duration_mint', 'description', 'provider_id'
    ]);

    // Purani image ka local path nikalna
    $oldImagePath = $service->image ? public_path('image/' . basename($service->image)) : null;

    // ✅ File Upload Image Update
    if ($request->hasFile('image')) {
        // Purani image delete karo
        if ($oldImagePath && file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }

        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension(); 
        $file->move(public_path('image'), $fileName); 

        $input['image'] = $baseUrl . '/public/image/' . $fileName; 
    }

    // ✅ Base64 Image Upload
    if ($request->input('image_base64')) {
        if ($oldImagePath && file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }

        $imageData = base64_decode($request->input('image_base64'));
        $fileName = uniqid() . '.png'; 
        file_put_contents(public_path('image/' . $fileName), $imageData);

        $input['image'] = $baseUrl . '/public/image/' . $fileName; 
    }

    // Update in DB
    $input['updated_at'] = now();
    DB::table('services')->where('id', $id)->update($input);

    // Fetch updated record
    $updatedService = DB::table('services')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'message' => 'Service updated successfully',
        'data' => $updatedService
    ], 200);
}


public function test_mail(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provider_id' => 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $provider_id = $request->input('provider_id');

    // ✅ Start Query Builder
    $query = DB::table('categories')
        ->select('id', 'name', 'image', 'status', 'created_at', 'updated_at', 'provider_id', 'created_admin');

    // ✅ Filter if provider_id is passed
    if (!empty($provider_id)) {
        $query->where('provider_id', $provider_id);
    }

    // ✅ Fetch Data
    $categories = $query->get();

    if ($categories->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $categories
    ], 200);
}



public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = [
            'http://localhost:63232',
            'http://localhost:3000',
            'https://mishtiry.com',
            'https://admin.mishtiry.com',
        ];

        $origin = $request->headers->get('Origin');

        $response = $next($request);

        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
	
public function signup(Request $request)
{
    $validator = Validator::make($request->all(), [
        'full_name'  => 'required|string|max:255',
        'email'      => 'nullable|email|unique:user_details,email',
        'phone'      => 'required|regex:/^[0-9]{10}$/|unique:user_details,phone',
        'password'   => 'required|string|min:6',
        'role_id'    => 'required|integer',
        'username'   => 'nullable|string|max:255',
        'provideo_id' => 'nullable|integer',
        'select_commission' => 'nullable',
        'designation' => 'nullable|string|max:255',
		'fcm_tokens' =>  'nullable',
		'lat' =>  'nullable',
		'long' =>  'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }
    $now = Carbon::now();
    // ✅ Create new user
    $user = user_details::create([
        'full_name'        => $request->full_name,
        'email'            => $request->email,
        'phone'            => $request->phone,
        'username'         => $request->username,
        'password'         => $request->password, // Password hashed
        'role_id'          => $request->role_id,
        'provideo_id'      => $request->provideo_id,
        'select_commission'=> $request->select_commission,
        'designation'      => $request->designation,
		'fcm_tokens'      => $request->fcm_tokens,
        'lat'      => $request->lat,
        'long'      => $request->long,
        'status'           => 1,
        'created_at'       => $now,
        'updated_at'       => $now,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'User registered successfully',
        'id' => $user->id,
        'Documentsstatus' => 0,
    ], 200);
}
   
public function services_add(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'            => 'required',
        'category_id'     => 'required|integer',
        'subcategory_id'  => 'nullable',
		'subcategory_name'  => 'nullable',
        'city'            => 'required',
        'type'            => 'nullable',
        'status'          => 'required',
        'price'           => 'required|numeric',
		'mrp_price'       => 'nullable',
        'discount'        => 'required|numeric',
        'duration'        => 'required',
        'duration_mint'   => 'required',
        'description'     => 'required',
        'provider_id'     => 'required|integer',
        'handyman_id'     => 'nullable',
        'image'           => 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $imageUrl = null;

    if ($request->image) {

        $image = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        if ($imageData === false) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid base64 image'
            ], 200);
        }

        $imageName = time() . '.png';
        $imageDir  = public_path('image/');

        if (!file_exists($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        file_put_contents($imageDir . $imageName, $imageData);

        // ✅ SAME URL AS YOU MENTIONED
        $imageUrl = url('public/image/' . $imageName);
    }

    $id = DB::table('services')->insertGetId([
        'category_id'      => $request->category_id,
        'subcategory_id'   => $request->subcategory_id,
        'subcategory_name' => $request->subcategory_id,
        'name'             => $request->name,
        'description'      => $request->description,
        'image'            => $imageUrl,
        'price'            => $request->price,
		'mrp_price'        => $request->mrp_price,
        'discount'         => $request->discount,
        'duration'         => $request->duration,
        'duration_mint'    => $request->duration_mint,
        'handyman_id'      => $request->handyman_id,
        'provider_id'      => $request->provider_id,
        'status'           => $request->status,
        'city'             => $request->city, // ✅ FIXED
        'type'             => $request->type,
        'created_at'       => now(),
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Service Added Successfully',
        'id'      => $id,
        'image'   => $imageUrl
    ], 200);
}

}









 





