<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator; 

class CategoriesController extends Controller
{ 
   public function categories(Request $request)
{
    // GET + POST दोनों से limit / offset पढ़ने के लिए
    $limit = is_numeric($request->input('limit')) ? (int)$request->input('limit') : 10;
    $offset = is_numeric($request->input('offset')) ? (int)$request->input('offset') : 0;

    $category_id = (int)$request->input('category_id', 0);
    $subcategory_id = (int)$request->input('subcategory_id', 0);

    if ($category_id === 0) {

        $categories = DB::table('categories')
            ->select('id', 'name', 'image', 'status')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // All categories => show all subcategories
        $categories = $categories->map(function ($category) {
            $subcategories = DB::table('subcategories')
                ->where('category_id', $category->id)
                ->select('id', 'name', 'category_id', 'image', 'status')
                ->get();

            $category->subcategories = $subcategories;
            return $category;
        });

    } else {

        $categories = DB::table('categories')
            ->select('id', 'name', 'image', 'status')
            ->where('id', $category_id)
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No category found.',
                'data' => []
            ], 200);
        }

        // Category found => subcategory filter apply
        $categories = $categories->map(function ($category) use ($category_id, $subcategory_id) {

            $subcategories = DB::table('subcategories')
                ->where('category_id', $category_id)
                ->when($subcategory_id != 0, function ($q) use ($subcategory_id) {
                    return $q->where('id', $subcategory_id);  // specific subcategory
                })
                ->select('id', 'name', 'category_id', 'image', 'status')
                ->get();

            $category->subcategories = $subcategories;
            return $category;
        });
    }

    return response()->json([
        'success' => true,
        'data' => $categories
    ], 200);
}


	
	public function houseman_list(Request $request)
{
    $provider_id = $request->input('provider_id');

    $houseman = DB::table('user_details')
        ->leftJoin(
            'regcommission',
            'user_details.select_commission',
            '=',
            'regcommission.id'
        )
        ->select(
            'user_details.*',
            'regcommission.name as commission',
            'regcommission.type'
        )
       
        ->where('user_details.provideo_id', $provider_id)
        ->get();

    if ($houseman->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No houseman found.'
        ], 200);
    }

    return response()->json([
        'success' => true,
        'data' => $houseman
    ], 200);
}


	
public function provider_list(Request $request)
{
    // Optional filter
    $subcategory_id = $request->input('category_id');

    // ✅ Base query
    $query = DB::table('user_details')
        ->join('services', 'user_details.id', '=', 'services.provider_id')
        ->select(
            'user_details.*',
            'services.category_id',
            'services.subcategory_id',
            'services.provider_id'
        );

    // ✅ Apply subcategory filter if provided
    if (!empty($subcategory_id)) {
        $query->where('services.subcategory_id', $subcategory_id);
    }

    // ✅ Fetch data
    $providers = $query->get();

    // ✅ Response
    if ($providers->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No providers found.',
            'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data Found',
        'data' => $providers
    ], 200);
}
	
public function houseman_online(Request $request)
{
    $provider_id = $request->input('provider_id');

    $houseman = DB::table('user_details')
        ->leftJoin(
            'regcommission',
            'user_details.select_commission',
            '=',
            'regcommission.id'
        )
        ->select(
            'user_details.*',
            'regcommission.name as commission',
            'regcommission.type'
        )
        ->where('user_details.on_off', 1)
        ->where('user_details.provideo_id', $provider_id)
        ->get();

    if ($houseman->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No houseman found.',
			'data' => []
        ], 200);
    }

    return response()->json([
        'success' => true,
        'data' => $houseman
    ], 200);
}


	
    public function subcategory ($categoryId)
    {
        $subcategory = DB::table('subcategories')->where('category_id', $categoryId)->select('id', 'name','category_id','image')->get();
       if ($subcategory->isEmpty()) {
        
        return response()->json([
            'success'=> false,
            'message' => 'No subcategory found.'],200);
        
    } else {
        
        return response()->json([
            'success'=>true,
            'data' => $subcategory],200);
    }
    }
    
    
public function services($categoryId, $subcategoryId, $userId)
{

    $query = DB::table('services')
        ->join('providers_details', 'services.provider_id', '=', 'providers_details.id')
        ->leftJoin('favorites', function ($join) use ($userId) {
            $join->on('services.id', '=', 'favorites.service_id')
                 ->where('favorites.user_id', '=', $userId);
        })
        ->select(
            'services.id',
            'services.category_id',
            'services.subcategory_name',
            'services.subcategory_id',
            'services.name',
            'services.description',
            'services.image',
            'services.price',
            'services.discount',
            'services.duration',
            'providers_details.id as provider_id',
            'providers_details.full_name as provider_name',
            'providers_details.image as provider_image',
            'providers_details.rating',
            'providers_details.address as provider_address',
            DB::raw("IF(favorites.user_id IS NOT NULL, 1, 0) as favorites_status")
        );

    // Adding category and subcategory filters
    if ($subcategoryId == 0) {
        $query->where('services.category_id', $categoryId);
    } else {
        $query->where('services.category_id', $categoryId)
              ->where('services.subcategory_id', $subcategoryId);
    }
    
       $query->where('services.status', '1');

    // Executing the query
    $services = $query->get();

    // Returning the response
    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No services found.',
            'data' => []
        ], 200);
    } else {
        return response()->json([
            'success' => true,
            'message' => 'Services found.',
            'data' => $services
        ], 200);
    }
}



public function search(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'query' => 'required',

    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['success' => false, 'message' => $validator->errors()->first()],200);
    }
    $query = $request->has('query') ? $request->input('query') : null;

    

    $categories = DB::table('categories')
        ->where('name', 'like', "%$query%")
        ->select('id', 'name', 'image')
        ->get();

    $subcategories = DB::table('subcategories')
        ->where('name', 'like', "%$query%")
        ->select('id', 'name', 'category_id', 'image')
        ->get();

    $services = DB::table('services')
        ->where('name', 'like', "%$query%")
        ->select('id', 'name', 'subcategory_id', 'image')
        ->get();

    foreach ($categories as $category) {
        $category->subcategories = $subcategories->where('category_id', $category->id);
        foreach ($category->subcategories as $subcategory) {
            $subcategory->services = $services->where('subcategory_id', $subcategory->id);
        }
    }

    $results = [
        'categories' => $categories,
        'subcategories' => $subcategories,
        'services' => $services
    ];

    return response()->json([
        'success' => true,
        'data' => $results,
    ], 200);
}


public function getServices(Request $request)
{
    $search = $request->input('search');
    
    // Correct the select statement
    $query = DB::table('services')
        ->join('providers_details', 'services.provider_id', '=', 'providers_details.id') // Join with the providers table
        ->select(
            'services.id', 
            'services.name', 
            'services.image', 
            'services.discount', 
            'services.duration', 
            'services.price', 
            'services.description', 
            'services.provider_id',
            'providers_details.rating',
            'providers_details.full_name as provider_name',  // Select provider's name
            'providers_details.image as provider_image' // Select provider's image
        );

    // Add the search condition if the 'search' parameter is provided
    if (!empty($search)) {
        $query->where('services.name', 'LIKE', "%{$search}%");
    }

    // Execute the query
    $services = $query->get();

    // Return appropriate response based on whether services are found
    if ($services->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No services found.'
        ], 200);
    } else {
        return response()->json([
            'success' => true,
            'data' => $services
        ], 200);
    }
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
        
        $category = DB::table('categories')->where('id', $id)->first();
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 200);
        }

        DB::table('categories')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ], 200);
    }	
public function subcategories_delete(Request $request)
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
        
        $category = DB::table('subcategories')->where('id', $id)->first();
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'SubCategories	 not found'
            ], 200);
        }

        DB::table('subcategories')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcategories deleted successfully'
        ], 200);
    }	

public function destroy_services($id)
{
    
    $destroy = DB::table('services')->where('id', $id)->first();

    if (!$destroy) {
        return response()->json([
            'status' => false,
            'message' => 'service not found.',
        ], 200);
    }

    
    DB::table('services')->where('id', $id)->delete();

    return response()->json([
        'status' => true,
        'message' => 'service deleted successfully.',
    ], 200);
}

public function searchData(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'query' => 'required',

    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['success' => false, 'message' => $validator->errors()->first()],200);
    }
    $query = $request->has('query') ? $request->input('query') : null;

    

    $categories = DB::table('categories')
        ->where('name', 'like', "%$query%")
        ->select('id', 'name', 'image')
        ->get();

    $subcategories = DB::table('subcategories')
        ->where('name', 'like', "%$query%")
        ->select('id', 'name', 'category_id', 'image')
        ->get();

    $services = DB::table('services')
        ->where('name', 'like', "%$query%")
        ->select('id', 'name', 'subcategory_id', 'image')
        ->get();

    foreach ($categories as $category) {
        $category->subcategories = $subcategories->where('category_id', $category->id);
        foreach ($category->subcategories as $subcategory) {
            $subcategory->services = $services->where('subcategory_id', $subcategory->id);
        }
    }

    $results = [
        'categories' => $categories,
        'subcategories' => $subcategories,
        'services' => $services
    ];

    return response()->json([
        'success' => true,
        'data' => $results,
    ], 200);
}

}













