<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrainingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $url = "https://foodstoresbibe-production.up.railway.app/train";
        $response=Http::get($url);

         // Kiểm tra xem yêu cầu có thành công không
        if ($response->successful()) {
            // Xử lý dữ liệu trả về
            $data = $response->json(); // Lấy dữ liệu JSON từ phản hồi
            // Bạn có thể làm gì đó với $data ở đây, ví dụ lưu vào cơ sở dữ liệu
            Log::info('Training data received:', $data);
        } else {
            // Xử lý nếu có lỗi trong yêu cầu
            Log::error('Failed to fetch data from API. Status: ' . $response->status());
        }
    }
}
