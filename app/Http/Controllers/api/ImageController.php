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
        try{
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
            ]);

            
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads', $fileName, 'public');

            
                return response()->json([
                    'message' => 'Upload ảnh thành công!',
                    'file_path' => $filePath
                ], 201);
            }

            return response()->json([
                'message' => 'Không có ảnh nào được tải lên.'
            ], 400);
        }
        catch(Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ] );
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
