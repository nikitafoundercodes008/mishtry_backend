<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash; 


use Illuminate\Http\Request;

class DashboardController extends Controller
{
   public function dashboard()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $providerPendingCount=DB::table('documents')->where('status', 1)->count(); 
    $userCount = DB::table('user_details')->where('role_id', 3)->count();
    $serviceCount = DB::table('services')->count();
    $providerCount = DB::table('user_details')->where('role_id', 2)->count();
    $handymanCount = DB::table('user_details')->where('role_id', 1)->count();
    $bookingCount = DB::table('bookings')
        ->join('services', 'bookings.service_id', '=', 'services.id')
        ->count();
  $taxSum = round(DB::table('bookings')->whereNotNull('tax')->sum('tax'));
  
  $totalAmount = round(DB::table('bookings')->whereNotNull('total_amount')->sum('total_amount'));
 
    return view('dashboard', compact('userCount', 'serviceCount', 'bookingCount', 'providerCount', 'handymanCount','providerPendingCount','taxSum', 
        'totalAmount'));
    
}

    
   public function users()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

   
    $users = DB::table('user_details')
        ->where('role_id', 3)
        ->orderBy('id', 'desc')
        ->paginate(6);

    return view('user.users', compact('users'));
}
 public function users_one($id)
{
	
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $usersss = DB::table('user_details')
        ->where('id', $id)
        ->first();

    if (!$usersss) {
        abort(404, 'User not found');
    }

    return view('user.users', compact('usersss'));
}


 public function reviews()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $users = DB::table('reviews')
        ->join('user_details as u', 'reviews.user_id', '=', 'u.id')
        ->join('user_details as h', 'reviews.handymanid', '=', 'h.id')
        ->join('services', 'reviews.services_id', '=', 'services.id')
        ->select(
            'reviews.id',
            'reviews.rate',
            'reviews.user_id',
            'reviews.handymanid',
            'reviews.status',
            'reviews.created_at',
            'reviews.comment',
            'reviews.services_id',
            'u.full_name as user_full_name',
            'u.image as user_image',
            'h.full_name as handyman_full_name',
            'h.image as handyman_image',
            'services.name as service_name',
            'services.image as service_image'
        )
        ->orderBy('reviews.id', 'desc')
        ->paginate(6);

    return view('user.reviews', compact('users'));
}
public function transaction_details()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $users = DB::table('transaction_details')
        ->join('user_details', 'transaction_details.user_id', '=', 'user_details.id')
        ->select(
            'transaction_details.id',
            'transaction_details.user_id',
            'transaction_details.wallet_amount',
            'transaction_details.payin',
            'transaction_details.payout',
            'transaction_details.created_at',
            'transaction_details.updated_at',
            'transaction_details.type',
            'transaction_details.paymode',
            'transaction_details.status',
            'user_details.full_name',
            'user_details.phone'
        )
        ->paginate(6); // pagination working!

    return view('user.transaction_details', compact('users'));
}

public function transaction_details_users(Request $request, $id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    // 👇 route se aaya hua user id
    $users = DB::table('transaction_details')
        ->join('user_details', 'transaction_details.user_id', '=', 'user_details.id')
        ->where('transaction_details.user_id', $id)   // ✅ FIX
        ->select(
            'transaction_details.id',
            'transaction_details.user_id',
            'transaction_details.wallet_amount',
            'transaction_details.payin',
            'transaction_details.payout',
            'transaction_details.created_at',
            'transaction_details.updated_at',
            'transaction_details.type',
            'transaction_details.paymode',
            'transaction_details.status',
            'user_details.full_name',
            'user_details.phone'
        )
        ->orderBy('transaction_details.created_at', 'desc')
        ->paginate(6);

    return view('user.transaction_details', compact('users'));
}

	
 public function couponlist(Request $request)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    
    $query = DB::table('coupons')
        ->select(
            'id',
            'code',
            'title',
            'description',
            'discount_type',
            'discount_value',
            'min_order_amount',
            'max_discount',
            'start_date',
             'status',
            'end_date',
            'created_at'
        );

    // Search functionality (optional)
    if ($request->has('search')) {
        $search = $request->get('search');
        $query->where(function($q) use ($search) {
            $q->where('code', 'LIKE', "%{$search}%")
              ->orWhere('title', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    $users = $query->orderBy('created_at', 'desc')
                  ->paginate(10);

    return view('user.couponlist', compact('users'));
}
	
	

  public function reviews_delete(String $id){
     $product=DB::table('reviews')->where('id',$id)->delete();
     return redirect('/reviews')->with('error','successfully');
 }  
    
    public function destroy($id)
{     
    
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    // Find the slider by id
    $user = DB::table('user_details')->find($id);

    if (!$user) {
        return redirect()->back()->with('error', 'Slider not found.');
    }

    // Delete the slider
    DB::table('user_details')->where('id', $id)->delete();

    return redirect()->back()->with('success', 'Slider deleted successfully!');
}
    
 public function editadmin()
    {
        // Check if user_id exists in session
        if (!session()->has('user_id')) {
            return redirect('/')->withErrors([
                'login' => 'You must be logged in to access the dashboard.',
            ]);
        }

        $user_id = session('user_id');

        // Fetch single admin record
        $user = DB::table('admin')->where('id', $user_id)->first();

        if ($user) {
            return view('service.edit_profile', ['user' => $user]);
        } else {
            return redirect('/')->withErrors([
                'login' => 'Admin not found.',
            ]);
        }
    }
public function booking_count($providerId)
{
    $bookingCount = DB::table('bookings')
        ->where('provider_id', $providerId)
        ->count();

    return response()->json(['count' => $bookingCount]);
}
    // Handle profile update
    public function updateProfile(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/')->withErrors([
                'login' => 'You must be logged in to access the dashboard.',
            ]);
        }

        $user_id = session('user_id');

        $request->validate([
            'email' => 'required|email',
            'password' => 'nullable|min:6',
        ]);

        $updateData = [
            'email' => $request->email,
        ];

        // Only update password if entered
       if ($request->filled('password')) {
    $updateData['password'] = $request->password;
}


        DB::table('admin')->where('id', $user_id)->update($updateData);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
public function settings()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $settings = DB::table('settings')
        ->select('id', 'name', 'value', 'status', 'type', 'modal_type', 'created_at')
        ->orderBy('created_at', 'desc')
        ->paginate(12); // Increased for better card view

    return view('user.settings', compact('settings'));
}

public function updateSetting(Request $request, $id)
{
    $request->validate([
        'value' => 'required',
        'status' => 'required|in:0,1',
    ]);

    // Get current setting to determine type
    $currentSetting = DB::table('settings')->where('id', $id)->first();
    
    // Update setting
    DB::table('settings')
        ->where('id', $id)
        ->update([
            'value' => $request->value,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

    return redirect()->route('settings')->with('success', 'Setting updated successfully!');
}



public function getSetting($id)
{
    $setting = DB::table('settings')->where('id', $id)->first();
    return response()->json($setting);
}
public function storeCoupon(Request $request)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors(['login' => 'You must be logged in.']);
    }

    $request->validate([
        'code' => 'required|unique:coupons,code|max:50',
        'title' => 'required|max:100',
        'description' => 'nullable',
        'discount_type' => 'required|in:percent,fixed',
        'discount_value' => 'required|numeric|min:0',
        'min_order_amount' => 'nullable|numeric|min:0',
        'max_discount' => 'nullable|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'status' => 'required|in:active,inactive',
    ]);

    DB::table('coupons')->insert([
        'code' => strtoupper($request->code),
        'title' => $request->title,
        'description' => $request->description,
        'discount_type' => $request->discount_type,
        'discount_value' => $request->discount_value,
        'min_order_amount' => $request->min_order_amount ?? 0,
        'max_discount' => $request->max_discount ?? 0,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'status' => $request->status,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('couponlist')->with('success', 'Coupon created successfully!');
}

// Update Coupon
public function updateCoupon(Request $request, $id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors(['login' => 'You must be logged in.']);
    }

    $request->validate([
        'code' => 'required|max:50|unique:coupons,code,'.$id,
        'title' => 'required|max:100',
        'description' => 'nullable',
        'discount_type' => 'required|in:percent,fixed',
        'discount_value' => 'required|numeric|min:0',
        'min_order_amount' => 'nullable|numeric|min:0',
        'max_discount' => 'nullable|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'status' => 'required|in:active,inactive',
    ]);

    DB::table('coupons')
        ->where('id', $id)
        ->update([
            'code' => strtoupper($request->code),
            'title' => $request->title,
            'description' => $request->description,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'max_discount' => $request->max_discount ?? 0,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

    return redirect()->route('coupon.list')->with('success', 'Coupon updated successfully!');
}

// Delete Coupon
public function deleteCoupon($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors(['login' => 'You must be logged in.']);
    }

    DB::table('coupons')->where('id', $id)->delete();

    return redirect()->route('coupon.list')->with('success', 'Coupon deleted successfully!');
}

public function deleteSetting($id)
{
    DB::table('settings')->where('id', $id)->delete();
    return redirect()->route('settings')->with('success', 'Setting deleted successfully!');
}
 
 public function commission()
    {
        if (!session()->has('user_id')) {
            return redirect('/')->withErrors([
                'login' => 'You must be logged in to access the dashboard.',
            ]);
        }

        $users = DB::table('commission')
            ->select(
                'id',
                'commission_providers',
                'commission_handymans',
                'commission_admin',
                'status',
                'updated_at'
            )
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('user.commission', compact('users'));
    }


public function updateCommission(Request $request, $id)
{
    $request->validate([
        'commission_providers' => 'required|numeric|min:0|max:100',
        'commission_handymans' => 'required|numeric|min:0|max:100',
        'commission_admin' => 'required|numeric|min:0|max:100',
        'status' => 'required|in:0,1',
    ]);

    // Check if total commission exceeds 100%
    $total = $request->commission_providers + $request->commission_handymans + $request->commission_admin;
    if ($total > 100) {
        return redirect()->back()->with('error', 'Total commission cannot exceed 100%');
    }

    DB::table('commission')
        ->where('id', $id)
        ->update([
            'commission_providers' => $request->commission_providers,
            'commission_handymans' => $request->commission_handymans,
            'commission_admin' => $request->commission_admin,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

    return redirect()->route('commission')->with('success', 'Commission updated successfully!');
}

public function deleteCommission($id)
{
    DB::table('commission')->where('id', $id)->delete();
    return redirect()->route('commission')->with('success', 'Commission deleted successfully!');
}
    public function providers()
    
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    
    
    $providers = DB::table('user_details')->orderBy('id', 'desc')
                ->where('role_id', 2) 
                ->paginate(5);

    return view('user.providers', compact('providers'));
    
}

public function handymanslist($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $handymans = DB::table('user_details as u')
        ->leftJoin('user_details as p', 'u.provideo_id', '=', 'p.id')
        ->select(
            'u.id',
            'u.full_name',
            'u.email',
            'u.phone',
            'u.image',
            'u.address',
            'u.country',
            'u.state',
            'u.city',
            'u.wallet_amount',
            'u.created_at',
            'u.updated_at',
            'u.username',
            'u.status',
            'u.role_id',
            'u.categories',
            'u.designation',
            'u.select_commission',
            'u.language',
            'u.skill',
            'u.about',
            'u.reasons',
            'u.zone_management',
            'u.provideo_id',
            'u.verification_status',
            'u.faqs',
            'u.fcm_tokens',
            'u.lat',
            'u.long',
            'p.full_name as provider_name',
            'p.phone as provider_mobile'
        )
        ->where('u.provideo_id', $id)
        ->where('u.role_id', 1) // optional: only handymen
        ->orderBy('u.id', 'desc')
        ->paginate(5);

    return view('user.handymans', compact('handymans'));
}


 public function handymans()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $handymans = DB::table('user_details as u')
        ->leftJoin('user_details as p', 'u.provideo_id', '=', 'p.id')
        ->select(
            'u.id',
            'u.full_name',
            'u.email',
            'u.phone',
            'u.image',
            'u.address',
            'u.country',
            'u.state',
            'u.city',
            'u.wallet_amount',
            'u.created_at',
            'u.updated_at',
            'u.username',
            'u.status',
            'u.role_id',
            'u.categories',
            'u.designation',
            'u.select_commission',
            'u.language',
            'u.skill',
            'u.about',
            'u.reasons',
            'u.zone_management',
            'u.provideo_id',
            'u.verification_status',
            'u.faqs',
            'u.fcm_tokens',
            'u.lat',
            'u.long',

            'p.full_name as provider_name',
            'p.phone as provider_mobile'
        )
        ->where('u.role_id', 1) 
        ->orderBy('u.id', 'desc')
        ->paginate(5);

    return view('user.handymans', compact('handymans'));
}
         

public function showBookings()
{
 
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $bookings = DB::table('bookings')
    ->leftJoin('user_details as u1', 'bookings.user_id', '=', 'u1.id')           // User
    ->leftJoin('user_details as u2', 'bookings.handyman_id', '=', 'u2.id')       // Handyman
    ->leftJoin('services', 'bookings.service_id', '=', 'services.id')
    ->select(
        'bookings.*',
        'u1.full_name as user_name',
        'u2.full_name as handyman_name',
        'services.name as service_name'
    )
    ->paginate(10);

   
    return view('booking.bookings', compact('bookings'));
}
public function demo()
{
 
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $bookings = DB::table('bookings')
    ->leftJoin('user_details as u1', 'bookings.user_id', '=', 'u1.id')           // User
    ->leftJoin('user_details as u2', 'bookings.handyman_id', '=', 'u2.id')       // Handyman
    ->leftJoin('services', 'bookings.service_id', '=', 'services.id')
    ->select(
        'bookings.*',
        'u1.full_name as user_name',
        'u2.full_name as handyman_name',
        'services.name as service_name'
    )
    ->paginate(10);

   
    return view('booking.bookings', compact('bookings'));
}
public function showBookings_users(Request $request, $id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $bookings = DB::table('bookings')
        ->leftJoin('user_details as u1', 'bookings.user_id', '=', 'u1.id')     // User
        ->leftJoin('user_details as u2', 'bookings.handyman_id', '=', 'u2.id') // Handyman
        ->leftJoin('services', 'bookings.service_id', '=', 'services.id')
        ->where('bookings.user_id', $id)   // ✅ FIXED WHERE
        ->select(
            'bookings.*',
            'u1.full_name as user_name',
            'u2.full_name as handyman_name',
            'services.name as service_name'
        )
        ->orderBy('bookings.created_at', 'desc')
        ->paginate(10);

    return view('booking.bookings', compact('bookings'));
}
public function showBookings_users_provideo(Request $request, $id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $bookings = DB::table('bookings')
        ->leftJoin('user_details as u1', 'bookings.user_id', '=', 'u1.id')     // User
        ->leftJoin('user_details as u2', 'bookings.handyman_id', '=', 'u2.id') // Handyman
        ->leftJoin('services', 'bookings.service_id', '=', 'services.id')
        ->where('bookings.provideo_id', $id)  
        ->select(
            'bookings.*',
            'u1.full_name as user_name',
            'u2.full_name as handyman_name',
            'services.name as service_name'
        )
        ->orderBy('bookings.created_at', 'desc')
        ->paginate(10);

    return view('booking.bookings', compact('bookings'));
}

public function showBookings_handyman(Request $request, $id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $bookings = DB::table('bookings')
        ->leftJoin('user_details as u1', 'bookings.user_id', '=', 'u1.id')     // User
        ->leftJoin('user_details as u2', 'bookings.handyman_id', '=', 'u2.id') // Handyman
        ->leftJoin('services', 'bookings.service_id', '=', 'services.id')
        ->where('bookings.handyman_id', $id)  
        ->select(
            'bookings.*',
            'u1.full_name as user_name',
            'u2.full_name as handyman_name',
            'services.name as service_name'
        )
        ->orderBy('bookings.created_at', 'desc')
        ->paginate(10);

    return view('booking.bookings', compact('bookings'));
}
	

public function slider()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $sliders = DB::table('sliders')
                ->select('id', 'image', 'status') 
                ->paginate(4);

    return view('slider.sliders', compact('sliders'));
}

	
public function zonelist()
{
    
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    // Fetch sliders with explicit columns
    $sliders = DB::table('zone_Management')
                ->select('id', 'zone_name') // Make sure the 'status' column is selected
                ->paginate(4);

    return view('slider.zonelist', compact('sliders'));
}
	
public function createZone()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

     return view('slider.zoneadd');
}

public function storeZone(Request $request)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $request->validate([
        'zone_name' => 'required|string|max:255|unique:zone_Management,zone_name',
        'coordinates' => 'required|json',
        'status' => 'nullable|in:0,1',
    ]);

    try {
        $coordinates = json_decode($request->coordinates, true);
        
        if (!is_array($coordinates) || count($coordinates) < 3) {
            return back()->with('error', 'Zone area must have at least 3 points')->withInput();
        }

        DB::table('zone_Management')->insert([
            'zone_name' => $request->zone_name,
            'coordinates' => $request->coordinates,
            'center_lat' => $request->center_lat ?? null,
            'center_lng' => $request->center_lng ?? null,
            'area_sqkm' => $request->area_sqkm ?? null,
            'description' => $request->description ?? null,
            'status' => $request->status ?? 1,
            'created_by' => session('user_id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('zonelist')->with('success', 'Zone created successfully!');
        
    } catch (\Exception $e) {
        return back()->with('error', 'Error creating zone: ' . $e->getMessage())->withInput();
    }
}


   public function slideredit($id)
{
    
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    
    $slider = DB::table('sliders')->where('id', $id)->first();
    if (!$slider) {
        return redirect()->route('dashboard.slider')->with('error', 'Slider not found.');
    }

    return view('slider.editsliders', compact('slider'));
}





public function sliderupdate(Request $request, $id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $request->validate([
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
    ]);

    $slider = DB::table('sliders')->where('id', $id)->first();

    if (!$slider) {
        return redirect()->route('dashboard.slider')->with('error', 'Slider not found.');
    }

    $updateData = [];

    if ($request->hasFile('image')) {

        // Destination folder: /public/image/
        $destinationPath = public_path('image');

        // Unique name
        $imageName = time() . '_' . $request->file('image')->getClientOriginalName();

        // Move file
        $request->file('image')->move($destinationPath, $imageName);

        // Save in DB => only image name or full path
        $updateData['image'] = 'https://mishtiry.com/image/' . $imageName;
    }

    DB::table('sliders')->where('id', $id)->update($updateData);

    return redirect()->route('sliders')->with('success', 'Slider updated successfully.');
}


public function providerstoggleStatus(Request $request, $id)
{
    
    
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    // Fetch the provider
    $provider = DB::table('user_details')->where('id', $id)->first();

    if (!$provider) {
        return redirect()->back()->with('error', 'Provider not found.');
    }

    // Validate the status value
    $newStatus = $request->input('verification_status');

    // Ensure the status is either '2' (Active) or '4' (Blocked)
    if (!in_array($newStatus, ['2', '4'])) {
        return redirect()->back()->with('error', 'Invalid status.');
    }

    // Update the status in the database
    DB::table('user_details')->where('id', $id)->update(['verification_status' => $newStatus]);

    // Redirect back with success message
    return redirect()->back()->with('success', 'Status updated successfully!');
}
public function providerdocview(Request $request, $provider_id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $provider_docs = DB::table('provider_handy_doc')
        ->select('id', 'aadhar_front', 'aadhar_back', 'aadhar_no', 'pan_number', 'pan_cart_image', 'passpost_image', 'status', 'created_at')
        ->where('user_id', $provider_id)
        ->paginate(10);

    return view('slider.providerdoc', compact('provider_docs'));
}

public function sliderstoggleStatus($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    DB::table('sliders')->where('id', $id)->delete();

    return redirect()->back()->with('success', 'Delete successfully!');
}

public function Category_delete($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    DB::table('categories')->where('id', $id)->delete();

    return redirect()->back()->with('success', 'Delete successfully!');
}
	public function subCategory_delete($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    DB::table('subcategories')->where('id', $id)->delete();

    return redirect()->back()->with('success', 'Delete successfully!');
}
	

}
