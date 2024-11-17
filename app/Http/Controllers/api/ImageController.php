<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Exception;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    //

    public function upload(Request $request){
        try {
            $request->validate([
                'images' => 'required|array',
                'images.*' => 'image|max:2048' // Mỗi file phải là ảnh, dung lượng tối đa 2MB
            ]);
        
            if ($request->hasFile('images')) {
                $files = $request->file('images');
                $images = [];
        
                foreach ($files as $file) {
                    // Tạo tên file duy nhất bằng uniqid()
                    $fileName = uniqid() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('uploads', $fileName, 'public');
        
                    if ($filePath) {
                        $images[] = [
                            "link" => asset('storage/uploads/' . $fileName) // Trả về link đầy đủ
                        ];
                    } else {
                        return response()->json([
                            'message' => 'Có lỗi xảy ra khi lưu ảnh: ' . $file->getClientOriginalName()
                        ], 500);
                    }
                }
        
                return response()->json([
                    'message' => 'Upload ảnh thành công!',
                    'images' => $images
                ], 201);
            }
        
            return response()->json([
                'message' => 'Không có ảnh nào được tải lên.'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getImage($filename)
    {
        $path = storage_path('app/public/uploads/' . $filename);

        if (!file_exists($path)) {

            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->file($path);
    }
}
