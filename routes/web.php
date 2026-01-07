<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{DashboardController,AuthController,CategoryController,VerificationController,ChatController}; 


Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');            
Route::get('/item', [CategoryController::class, 'index'])->name('item');
Route::get('/subitem', [CategoryController::class, 'subcategorys'])->name('subitem');
Route::get('/services', [CategoryController::class, 'services'])->name('services');
Route::get('/users', [DashboardController::class, 'users'])->name('users');
Route::get('/users_one/{id}', [DashboardController::class, 'users_one'])->name('users_one');
Route::get('/reviews', [DashboardController::class, 'reviews'])->name('reviews');
Route::get('/transaction_details', [DashboardController::class, 'transaction_details'])->name('transaction_details');
Route::get('/transaction_details_users/{id}', [DashboardController::class, 'transaction_details_users'])->name('transaction_details_users');
Route::get('/couponlist', [DashboardController::class, 'couponlist'])->name('couponlist');
Route::any('/deleteCoupon', [DashboardController::class, 'deleteCoupon'])->name('deleteCoupon');
Route::any('/updateCoupon', [DashboardController::class, 'updateCoupon'])->name('coupon.update');
Route::any('/storeCoupon', [DashboardController::class, 'storeCoupon'])->name('coupon.store');
Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
Route::any('/updateSetting/{id}', [DashboardController::class, 'updateSetting'])->name('updateSetting');
Route::get('/commission', [DashboardController::class, 'commission'])->name('commission');
Route::post('/commission/store', [DashboardController::class, 'storeCommission'])->name('commission.store');
Route::put('/commission/update/{id}', [DashboardController::class, 'updateCommission'])->name('commission.update');
Route::delete('/commission/delete/{id}', [DashboardController::class, 'deleteCommission'])->name('commission.delete');
Route::get('/reviews_delete/{id}', [DashboardController::class, 'reviews_delete'])->name('reviews_delete');
Route::get('/providers', [DashboardController::class, 'providers'])->name('providers');
Route::get('/handymans', [DashboardController::class, 'handymans'])->name('handymans');
Route::get('/handymanslist/{id}', [DashboardController::class, 'handymanslist'])->name('handymanslist');
Route::get('bookings',[DashboardController::class, 'showBookings'])->name('showBookings');
Route::get('showBookings_users_provideo/{id}',[DashboardController::class, 'showBookings_users_provideo'])->name('showBookings_users_provideo');

Route::get('/showBookings_users/{id}', [DashboardController::class, 'showBookings_users'])
    ->name('showBookings_users');
Route::get('booking_count/{providerId}',[DashboardController::class, 'booking_count'])->name('booking_count');
Route::get('/edit', [DashboardController::class, 'editadmin'])->name('admin.updateprofile');
Route::post('/update_profile', [DashboardController::class, 'updateprofile'])->name('admin.editprofile');
Route::any('sliders',[DashboardController::class, 'slider'])->name('sliders');
Route::get('settings',[DashboardController::class, 'settings'])->name('settings');
Route::get('zonelist',[DashboardController::class, 'zonelist'])->name('zonelist');
Route::get('/zones/create', [DashboardController::class, 'createZone'])->name('zones.create');
Route::post('/zones/store', [DashboardController::class, 'storeZone'])->name('zones.store');

Route::get('/slider/edit/{id}', [DashboardController::class, 'slideredit'])->name('slider.edit');
Route::post('/slider/update/{id}', [DashboardController::class, 'sliderupdate'])->name('slider.update');
Route::get('/creates', [CategoryController::class, 'creates'])->name('creates');
Route::post('/store', [CategoryController::class, 'store'])->name('store');
Route::any('/storeservices', [CategoryController::class, 'storeservices'])->name('storeservices');
Route::post('/category/toggle-status/{id}', [CategoryController::class, 'toggleStatus'])->name('category.toggleStatus');
Route::post('/subcategory/toggle-status/{id}', [CategoryController::class, 'subcategorietoggleStatus'])->name('subcategory.toggleStatus');
Route::post('/services/toggle-status/{id}', [CategoryController::class, 'servicestoggleStatus'])->name('services.toggleStatus');
Route::any('/providers_approve/{id}', [CategoryController::class, 'providers_approve'])->name('providers_approve');
Route::any('/providers/toggle-status/{id}', [DashboardController::class, 'providerstoggleStatus'])->name('providers.toggleStatus');
Route::any('/providerdocview/{id}', [DashboardController::class, 'providerdocview'])->name('providerdocview');
Route::post('/userstoggleStatus/{id}', [CategoryController::class, 'userstoggleStatus'])->name('userstoggleStatus');
Route::get('/categories/edit/{id}', [CategoryController::class, 'edit'])->name('category.edit');
Route::post('/categories/update/{id}', [CategoryController::class, 'update'])->name('category.update');
Route::get('/subcategory/edit/{id}', [CategoryController::class, 'subedit'])->name('subcategorie.edit');   
Route::post('/subcategory/update/{id}', [CategoryController::class, 'subupdate'])->name('subcategorie.update');
Route::get('/sliders/toggle-status/{id}', [DashboardController::class, 'sliderstoggleStatus'])->name('sliders.toggleStatus');
Route::get('/Category_delete/{id}', [DashboardController::class, 'Category_delete'])->name('Category_delete');
Route::get('/subCategory_delete/{id}', [DashboardController::class, 'subCategory_delete'])->name('subCategory_delete');

Route::delete('/users/{id}', [DashboardController::class, 'destroy'])->name('users.destroy');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/login', [AuthController::class, 'AuthLogin'])->name('Auth.login');
Route::get('/', [AuthController::class, 'AuthIndex'])->name('Auth.index');
Route::post('form', [AuthController::class, 'form'])->name('form');
Route::get('/papers', [VerificationController::class, 'paper'])->name('papers.index');
Route::post('/documents/approve/{id}', [VerificationController::class, 'approve'])->name('documents.approve');
Route::post('/documents/reject/{id}', [VerificationController::class, 'reject'])->name('documents.reject');
Route::get('/subcreates', [CategoryController::class, 'subcreates'])->name('subcreates');
Route::post('/subcategory_store', [CategoryController::class, 'storeSubcategory'])->name('storeSubcategory');
Route::get('chats_message', [ChatController::class, 'index'])->name('index.chats');

