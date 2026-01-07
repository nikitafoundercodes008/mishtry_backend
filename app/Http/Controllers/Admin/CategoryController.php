<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;



class CategoryController extends Controller
{
   public function index()
    {
        if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
        
        $categories = DB::table('categories')->paginate(5); 
        
        return view('service.categories', compact('categories'));
    }
    

    
     public function subcategorys()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $subcategories = DB::table('subcategories')
        ->join('categories', 'subcategories.category_id', '=', 'categories.id')
        ->select('subcategories.*', 'categories.name as category_name')
        ->paginate(5);

    return view('service.subcategories', compact('subcategories'));
}

    
 public function storeservices(Request $request)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors(['login' => 'You must be logged in.']);
    }

    $request->validate([
        'subcategory_id' => 'nullable',
        'category_id'    => 'nullable',
        'name'           => 'nullable',
        'description'    => 'nullable',
        'price'          => 'nullable',
        'discount'       => 'nullable',
        'duration'       => 'nullable',
        'tax'            => 'nullable',
        'image'          => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    $baseUrl = 'https://admin.mishtiry.com';
    $image = $request->file('image');
    $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
    $image->move(public_path('image'), $fileName);
    $filePath = $baseUrl . '/public/image/' . $fileName;

    DB::table('services')->insert([
        'subcategory_id' => 75,
        'category_id'    => $request->category_id,
        'name'           => $request->name,
        'description'    => $request->description,
        'price'          => $request->price,
        'discount'       => $request->discount ?? 0,
        'duration'       => $request->duration,
        'tax'            => $request->tax,
        'image'          => $filePath,
        'status'         => 1,
        'provider_id'    => session('user_id'),
        'created_at'     => now(),
    ]);

    return redirect()->route('services')->with('success', 'Service added successfully.');
}

   
public function services()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $services = DB::table('services')
        ->leftJoin('user_details as handyman', 'services.handyman_id', '=', 'handyman.id')
        ->leftJoin('user_details as provider', 'services.provider_id', '=', 'provider.id')
        ->select(
            'services.id',
            'services.category_id',
            'services.subcategory_id',
            'services.subcategory_name',
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
            'services.city as ciry ',
            'services.type',
            'services.handyman_id',
            'services.address',
            'services.user_id',
            'services.tax',
            'handyman.full_name as handyman_name',
            'provider.full_name as provider_name'
        )
        ->orderBy('services.id', 'desc')
        ->paginate(10);

    return view('service.services', compact('services'));
}

public function servicescategory()
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $services = DB::table('subcategories')->get(); // Fetch all

    return view('service.services', compact('services'));
}




    
    
    
    public function creates() {
        if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
        return view('service.create');
    }
    
    
public function store(Request $request)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $request->validate([
        'name' => 'required|string',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'status' => 'required',
    ]);

        $baseUrl = 'https://admin.mishtiry.com';
$image = $request->file('image');

// Handle image upload
$fileName = uniqid() . '.' . $image->getClientOriginalExtension();
$image->move(public_path('image'), $fileName); // Store in public/image/ directly

// Generate the file path for public URL
$filePath = $baseUrl . '/public/image/' . $fileName;


    DB::table('categories')->insert([
        'name' => $request->input('name'),
        'image' => $filePath,
        'status' => $request->input('status'),
    ]);

    return redirect()->route('item')->with('success', 'Category added successfully.');
}


public function toggleStatus($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $category = DB::table('categories')->find($id);

    if (!$category) {
        return redirect()->back()->with('error', 'Category not found.');
    }

    
    $newStatus = ($category->status === '1') ? '0' : '1';

    DB::table('categories')->where('id', $id)->update(['status' => $newStatus]);

    return redirect()->back()->with('success', 'Status updated successfully!');
}



public function subcategorietoggleStatus($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $subcategory = DB::table('subcategories')->find($id);

    if (!$subcategory) {
        return redirect()->back()->with('error', 'subCategory not found.');
    }

    
    $newStatus = ($subcategory->status === '1') ? '0' : '1';

    DB::table('subcategories')->where('id', $id)->update(['status' => $newStatus]);

    return redirect()->back()->with('success', 'Status updated successfully!');
}


public function servicestoggleStatus($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $services = DB::table('services')->find($id);

    if (!$services) {
        return redirect()->back()->with('error', 'services not found.');
    }

    
    $newStatus = ($services->status === '1') ? '0' : '1';

    DB::table('services')->where('id', $id)->update(['status' => $newStatus]);

    return redirect()->back()->with('success', 'Status updated successfully!');
}

public function userstoggleStatus($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $services = DB::table('user_details')->find($id);

    if (!$services) {
        return redirect()->back()->with('error', 'services not found.');
    }

    
    $newStatus = ($services->status === '1') ? '0' : '1';

    DB::table('user_details')->where('id', $id)->update(['status' => $newStatus]);

    return redirect()->back()->with('success', 'Status updated successfully!');
}
	
	
public function providers_approve($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors(['login' => 'You must be logged in to access the dashboard.']);
    }

    $service = DB::table('provider_handy_doc')
        ->where('user_id', $id)
        ->first();

    if (!$service) {
        return redirect()->back()->with('error', 'Documents not found.');
    }

 
    if ($service->status == 1) {
        $newStatus = 2;  // Pending → Approved
    } elseif ($service->status == 2) {
        $newStatus = 3;  // Approved → Rejected
    } elseif ($service->status == 3) {
        $newStatus = 2;  // Rejected → Approved
    } else {
        $newStatus = 1;  // Default first time
    }

    DB::table('provider_handy_doc')
        ->where('user_id', $id)
        ->update(['status' => $newStatus]);

    return redirect()->back()->with('success', 'Status Updated Successfully!');
}


    public function edit($id)
    {
        
        if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
        $category = DB::table('categories')->where('id', $id)->first();
        if (!$category) {
            return redirect()->route('category.index')->with('error', 'Category not found.');
        }

        return view('service.categoriesedit', compact('category'));
    }

    public function update(Request $request, $id)
{
    // Check login session
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    // Validation
    $request->validate([
        'name' => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
    ]);

    $category = DB::table('categories')->where('id', $id)->first();

    if (!$category) {
        return redirect()->route('category.index')->with('error', 'Category not found.');
    }

    $updateData = [
        'name' => $request->input('name'),
    ];

    // Upload image to public/image folder
    if ($request->hasFile('image')) {

        // Generate unique filename
        $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();

        // Move image to public/image/
        $request->file('image')->move(public_path('image'), $imageName);

        // Full URL
        $fullUrl = url('public/image/' . $imageName);

        // Save URL in DB
        $updateData['image'] = $fullUrl;
    }

    // Update data
    DB::table('categories')->where('id', $id)->update($updateData);

    return redirect()->route('item')->with('success', 'Category updated successfully.');
}
    
    
        public function subedit($id)
    {
        if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
        
        
        $subcategory = DB::table('subcategories')->where('id', $id)->first();
        if (!$subcategory) {
            return redirect()->route('category.subcategorys')->with('error', 'Category not found.');
        }

        return view('service.subcategoriesedit', compact('subcategory'));
    }

    public function subupdate(Request $request, $id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    // Validation
    $request->validate([
        'name'  => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
    ]);

    // Fetch subcategory
    $subcategory = DB::table('subcategories')->where('id', $id)->first();

    if (!$subcategory) {
        return redirect()->route('category.subcategorys')->with('error', 'Subcategory not found.');
    }

    // Prepare data to update
    $updateData = [
        'name' => $request->input('name'),
    ];

    // Image upload logic
    if ($request->hasFile('image')) {

        // Delete old image
        if ($subcategory->image) {
            $oldPath = str_replace(url('/') . '/', '', $subcategory->image);

            if (file_exists(public_path($oldPath))) {
                unlink(public_path($oldPath));
            }
        }

        // Upload new image inside public/image/categories
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();

        // Create folder if not exists
        if (!file_exists(public_path('image'))) {
            mkdir(public_path('image'), 0777, true);
        }

        // Move file
        $image->move(public_path('image'), $imageName);

        // Save full URL in database
        $updateData['image'] = url('public/image/' . $imageName);
    }

    // Update database
    DB::table('subcategories')->where('id', $id)->update($updateData);

    return redirect()->route('subitem')->with('success', 'Subcategory updated successfully.');
}

    
    
    public function subcreates() {
        
        if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    // Fetch all categories
    $categories = DB::table('categories')->get(); 
    
    // Pass categories to the view
    return view('service.add_subcategories', compact('categories'));
}

    
    
    
    
 public function storeSubcategory(Request $request)
{
    
    
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|string|max:255',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        'status' => 'required|boolean',
    ]);

    $baseUrl = 'https://admin.mishtiry.com';
$image = $request->file('image');

// Handle image upload
$fileName = uniqid() . '.' . $image->getClientOriginalExtension();
$image->move(public_path('image'), $fileName); // Store in public/image/ directly

// Generate the file path for public URL
$filePath = $baseUrl . '/public/image/' . $fileName;


    DB::table('subcategories')->insert([
        'category_id' => $request->category_id,
        'name' => $request->name,
        'image' => $filePath,
        'status' => $request->status,
    
    ]);

    // Redirect with success message
    return redirect()->route('subitem')->with('success', 'Category added successfully.');
}

    

}
