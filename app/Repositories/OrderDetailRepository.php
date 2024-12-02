<?php

namespace App\Repositories;

use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderDetailRepository{
    public function saveOrUpdate(OrderDetail $orderDetail){
        $orderDetail->save();
        return $orderDetail;
    }

    public function getByid($id){
        return OrderDetail::where('id',$id)->first();
    }
    
    public function getAll(){
        return OrderDetail::all();
    }
    public function delete($id){
        OrderDetail::destroy($id);
    }


    // thong ke
    public function topProduct($num,$start_date,$end_date){
        $topSellingProducts = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereBetween('order_details.created_at', [$start_date, $end_date])  // Chọn khoảng thời gian từ start_date đến end_date
            ->groupBy('order_details.product_id', 'products.title')
            ->select(
                'products.title',
                DB::raw('SUM(order_details.quantity * order_details.price) as total_revenue')
            )
            ->orderByDesc('total_revenue')  // Sắp xếp theo doanh thu giảm dần
            ->limit($num)  // Lấy ra top N sản phẩm
            ->get();

        
        $labels = $topSellingProducts->pluck('title')->toArray();
        $values = $topSellingProducts->pluck('total_revenue')->toArray();

        $result = [
            'label' => $labels,
            'value' => $values
        ];

        return $result;
    }


    public function topUser($num,$start_date,$end_date){
        $topUsers = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('SUM(orders.amount) as total_spent'))
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->groupBy('orders.user_id', 'users.name')
            ->orderByDesc('total_spent')
            ->limit($num)
            ->get();

        // Chuẩn bị kết quả theo định dạng mong muốn
        $topUsersData = [
            'label' => $topUsers->pluck('name')->toArray(),  // Lấy tên người dùng
            'value' => $topUsers->pluck('total_spent')->toArray()  // Lấy tổng chi tiêu
        ];

        return $topUsersData;
    }

    public function revenue($start_date, $end_date)
    {
        // Chuyển đổi sang Carbon nếu không phải đối tượng Carbon
        $start_date = $start_date instanceof Carbon ? $start_date : Carbon::parse($start_date);
        $end_date = $end_date instanceof Carbon ? $end_date : Carbon::parse($end_date);

        // Tạo danh sách tất cả các ngày trong khoảng thời gian
        $all_dates = [];
        for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
            $all_dates[] = $date->format('Y-m-d');
        }

        // Truy vấn lấy dữ liệu từ cơ sở dữ liệu
        $data = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(CASE WHEN status = "delivered" THEN 1 END) as total_delivered'),
                DB::raw('COUNT(CASE WHEN status = "cancel" THEN 1 END) as total_cancel'),
                DB::raw('CAST(SUM(CASE WHEN status = "delivered" THEN amount ELSE 0 END) AS SIGNED) as total_amount_delivered')
            )
            ->whereBetween('created_at', [$start_date->format('Y-m-d'), $end_date->format('Y-m-d')])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->keyBy('date'); // Sắp xếp dữ liệu theo ngày để dễ dàng tìm kiếm

        // Khởi tạo mảng kết quả
        $label = [];
        $value1 = [];
        $value2 = [];
        $value3 = [];

        // Lặp qua danh sách ngày đầy đủ
        foreach ($all_dates as $date) {
            $label[] = $date;
            $value1[] = isset($data[$date]) ? $data[$date]->total_delivered : 0;
            $value2[] = isset($data[$date]) ? $data[$date]->total_cancel : 0;
            $value3[] = isset($data[$date]) ? $data[$date]->total_amount_delivered : 0;
        }

        // Kết quả
        return [
            'label' => $label,
            'value1' => $value1,
            'value2' => $value2,
            'value3' => $value3,
        ];
    }

        
}