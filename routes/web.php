<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/run-storage-link', function () {
    try {
        // Run storage:link
        Artisan::call('storage:link');
        $output = Artisan::output();

        // Copy files from public/seed-images to storage/app/public
        $seedCategoryDir = public_path('seed-images/category');
        $seedProductDir = public_path('seed-images/product');

        $targetCategoryDir = storage_path('app/public/category');
        $targetProductDir = storage_path('app/public/product');

        $copiedCount = 0;

        // Copy categories
        if (is_dir($seedCategoryDir)) {
            $di = new RecursiveDirectoryIterator($seedCategoryDir, RecursiveDirectoryIterator::SKIP_DOTS);
            $it = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::SELF_FIRST);
            foreach ($it as $file) {
                $targetPath = str_replace($seedCategoryDir, $targetCategoryDir, $file->getPathname());
                if ($file->isDir()) {
                    if (!is_dir($targetPath)) {
                        mkdir($targetPath, 0755, true);
                    }
                } else {
                    $dir = dirname($targetPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    copy($file->getPathname(), $targetPath);
                    $copiedCount++;
                }
            }
        }

        // Copy products
        if (is_dir($seedProductDir)) {
            $di = new RecursiveDirectoryIterator($seedProductDir, RecursiveDirectoryIterator::SKIP_DOTS);
            $it = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::SELF_FIRST);
            foreach ($it as $file) {
                $targetPath = str_replace($seedProductDir, $targetProductDir, $file->getPathname());
                if ($file->isDir()) {
                    if (!is_dir($targetPath)) {
                        mkdir($targetPath, 0755, true);
                    }
                } else {
                    $dir = dirname($targetPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    copy($file->getPathname(), $targetPath);
                    $copiedCount++;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'storage:link executed and seed images copied',
            'output' => $output,
            'copied_count' => $copiedCount,
            'public_exists' => file_exists(public_path('storage')) ? 'yes' : 'no',
            'target_exists' => file_exists(storage_path('app/public')) ? 'yes' : 'no',
            'product_1_exists' => file_exists(storage_path('app/public/product/1')) ? 'yes' : 'no',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});
