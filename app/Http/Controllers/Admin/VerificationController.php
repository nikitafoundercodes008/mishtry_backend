<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


use Illuminate\Http\Request;

class VerificationController extends Controller
{
    
    
    
public function paper()
{
    
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }

    $papers = DB::table('documents')
        ->join('providers_details', 'documents.user_id', '=', 'providers_details.id') 
        ->orderBy('documents.id', 'desc')  
        ->select('documents.*', 'providers_details.full_name')  
        ->paginate(10); 

    return view('docverification.doc', compact('papers'));
}

    
    
public function approve($id)
{
    
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    $doc = DB::table('documents')->find($id);
    if (!$doc) {
        return redirect()->back()->with('error', 'Document not found.');
    }
    $newStatus = 2;
    DB::table('documents')->where('id', $id)->update(['status' => $newStatus]);

    
    DB::table('providers_details')->where('id', $doc->user_id)
        ->update(['verification_status' => $newStatus]);

    return redirect()->back()->with('success', 'Document approved successfully!');
}

public function reject($id)
{
    if (!session()->has('user_id')) {
        return redirect('/')->withErrors([
            'login' => 'You must be logged in to access the dashboard.',
        ]);
    }
    
    $doc = DB::table('documents')->find($id);
    if (!$doc) {
        return redirect()->back()->with('error', 'Document not found.');
    }
    $newStatus = 3;
    DB::table('documents')->where('id', $id)->update(['status' => $newStatus]);

    DB::table('providers_details')->where('id', $doc->user_id)
        ->update(['verification_status' => $newStatus]);

    return redirect()->back()->with('success', 'Document rejected successfully!');
}

}