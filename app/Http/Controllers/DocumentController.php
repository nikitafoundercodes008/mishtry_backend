<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use DB;

class DocumentController extends Controller
{

    public function getDocumentTypes()
    {
        $documentTypes = ['PAN CARD', 'DRIVING LICENSE', 'AADHAR CARD', 'PASSPORT', 'VOTING CARD'];
        return response()->json([
            'success'=>true,
            'data'=>$documentTypes
            ],200);
    }


public function addDocument(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:providers_details,id',
        'type' => 'nullable',
        'image' => 'nullable',
        'image_base64' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 200);
    }

    $userId = $request->user_id;
    $imagePath = null;
    
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images', 'public');
        $imagePath = 'https://handyman.mobileappdemo.net/storage/' . basename($imagePath); 
    }

    if ($request->input('image_base64')) {
        $imageData = base64_decode($request->input('image_base64'));
        $imageName = 'image/' . uniqid() . '.png'; 
        file_put_contents(public_path($imageName), $imageData);

        $imagePath = 'https://handyman.mobileappdemo.net/public/' . $imageName;
    }

    $document = Document::create([
        'user_id' => $userId,
        'type' => $request->type,
        'image' => $imagePath, 
        'status' => 1, 
    ]);

DB::table('providers_details')
    ->where('id', $userId)
    ->update(['verification_status' => 1]);

    return response()->json([
        'success' => true,
        'message' => 'Document uploaded successfully!',
    ], 200);
}




public function listDocuments($providerId)
{
    
    $documents = Document::where('user_id', $providerId)
    ->where('status', 2)
    ->get();
    if ($documents->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No documents found for this provider.'
        ], 200);
    } else {
        return response()->json([
            'success' => true,
            'document' => $documents
        ], 200);
    }
}


}
