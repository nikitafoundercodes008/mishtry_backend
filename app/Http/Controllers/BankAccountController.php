<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
{
   public function store(Request $request)
{
    // Validate the incoming request
    $validator = Validator::make($request->all(), [
        'userid' => 'required|string|max:11',
        'bankname' => 'required|string|max:25',
        'customername' => 'required|string|max:25',
        'ifsc' => 'required|string',
        'accountnumber' => 'required|string|unique:bank_accounts,accountnumber',
        'status' => 'required|in:1,2',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 200);
    }

    
    $userBankAccountsCount = BankAccount::where('userid', $request->userid)->count();

    if ($userBankAccountsCount >= 3) {
        return response()->json([
            'status' => false,
            'message' => 'You have reached the limit of 3 bank accounts.',
        ], 200);
    }

    // Create the bank account
    $bankAccount = BankAccount::create([
        'userid' => $request->userid,
        'bankname' => $request->bankname,
        'customername' => $request->customername,
        'ifsc' => $request->ifsc,
        'accountnumber' => $request->accountnumber,
        'status' => $request->status,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Bank account details registered successfully.',
        'last_insert_id' => $bankAccount->id,
    ], 200);
}
    
    
    
public function show($userId)
{
    $bankAccount = BankAccount::where('userid', $userId)->get();

    if ($bankAccount->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No bank accounts found for this user.',
        ], 200); 
    } else {
        return response()->json([
            'success' => true,
            'data' => $bankAccount,
        ], 200); 
    }
}

    
    
    
 public function update(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer|exists:bank_accounts,id', 
        'userid' => 'required', 
        'bankname' => 'nullable|string|max:255',
        'customername' => 'nullable|string|max:255',
        'ifsc' => 'nullable|string|max:11',
        'accountnumber' => 'nullable|string|unique:bank_accounts,accountnumber,' . $request->id, 
        'status' => 'nullable|in:1,2', 
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 200);
    }
    $bankAccount = BankAccount::where('id', $request->id)
        ->where('userid', $request->userid)
        ->first();

    if (!$bankAccount) {
        return response()->json([
            'status' => false,
            'message' => 'Bank account not found or does not belong to the user.',
        ], 200); 
    }
    $bankAccount->update($request->only([
        'bankname',
        'customername',
        'ifsc',
        'accountnumber',
        'status',
    ]));

    return response()->json([
        'status' => true,
        'message' => 'Bank account details updated successfully.',
        'last_update_id' => $bankAccount->id,
    ], 200);
}


public function destroy_details($id)
{

    $bankAccount = BankAccount::find($id);

    if (!$bankAccount) {
        return response()->json([
            'status' => false,
            'message' => 'Bank account not found.',
        ], 200);
    }
    $bankAccount->delete();

    return response()->json([
        'status' => true,
        'message' => 'Bank account deleted successfully.',
    ], 200);
}

public function update($userId)
{
    $bankAccount = BankAccount::where('userid', $userId)->get();

    if ($bankAccount->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No bank accounts found for this user.',
        ], 200); 
    } else {
        return response()->json([
            'success' => true,
            'data' => $bankAccount,
        ], 200); 
    }
}

    
    
    
 public function action(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer|exists:bank_accounts,id', 
        'userid' => 'required', 
        'bankname' => 'nullable|string|max:255',
        'customername' => 'nullable|string|max:255',
        'ifsc' => 'nullable|string|max:11',
        'accountnumber' => 'nullable|string|unique:bank_accounts,accountnumber,' . $request->id, 
        'status' => 'nullable|in:1,2', 
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 200);
    }
    $bankAccount = BankAccount::where('id', $request->id)
        ->where('userid', $request->userid)
        ->first();

    if (!$bankAccount) {
        return response()->json([
            'status' => false,
            'message' => 'Bank account not found or does not belong to the user.',
        ], 200); 
    }
    $bankAccount->update($request->only([
        'bankname',
        'customername',
        'ifsc',
        'accountnumber',
        'status',
    ]));

    return response()->json([
        'status' => true,
        'message' => 'Bank account details updated successfully.',
        'last_update_id' => $bankAccount->id,
    ], 200);
}


public function delete($id)
{

    $bankAccount = BankAccount::find($id);

    if (!$bankAccount) {
        return response()->json([
            'status' => false,
            'message' => 'Bank account not found.',
        ], 200);
    }
    $bankAccount->delete();

    return response()->json([
        'status' => true,
        'message' => 'Bank account deleted successfully.',
    ], 200);
}
}
