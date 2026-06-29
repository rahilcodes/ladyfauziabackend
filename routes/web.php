<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/run-storage-link', function () {
    try {
        Artisan::call('storage:link');
        $output = Artisan::output();
        return response()->json([
            'status' => 'success',
            'message' => 'storage:link executed',
            'output' => $output,
            'public_exists' => file_exists(public_path('storage')) ? 'yes' : 'no',
            'target_exists' => file_exists(storage_path('app/public')) ? 'yes' : 'no',
            'product_1_exists' => file_exists(storage_path('app/public/product/1')) ? 'yes' : 'no',
            'product_files' => is_dir(storage_path('app/public/product')) ? scandir(storage_path('app/public/product')) : 'not_a_dir',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});
