<?php

namespace App\Repositories;

use App\Models\OrderDetail;
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

    public function revenue($status,$start_date,$end_date){
        $orders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id') // Kết nối với bảng users để lấy thông tin người dùng
            ->leftJoin('order_details', 'orders.id', '=', 'order_details.order_id') // Kết nối với bảng order_details để tính tổng số sản phẩm
            ->whereBetween('orders.created_at', [$start_date, $end_date]) // Lọc theo thời gian
            ->when($status !== "", function($query) use ($status) {
                return $query->where('orders.status', $status); // Lọc theo trạng thái nếu status không phải chuỗi rỗng
            })
            ->groupBy('orders.id', 'users.name') // Nhóm theo đơn hàng và tên người dùng
            ->select(
                'orders.id',
                'orders.amount',
                'orders.status',
                'orders.created_at',
                'users.name as user_name',
                'users.phone as user_phone',
                DB::raw('SUM(order_details.quantity) as total_products') // Tính tổng số sản phẩm trong mỗi đơn hàng
            )
            ->orderByDesc('orders.created_at') // Sắp xếp từ mới nhất
            ->get();
        
        // Tính tổng tiền của tất cả các đơn hàng
        $totalAmount = DB::table('orders')
            ->whereBetween('orders.created_at', [$start_date, $end_date]) // Lọc theo thời gian
            ->when($status !== "", function($query) use ($status) {
                return $query->where('orders.status', $status); // Lọc theo trạng thái nếu status không phải chuỗi rỗng
            })
            ->sum(DB::raw('amount + discount')); // Tính tổng tiền (bao gồm discount)

            return [
                'orders' => $orders,
                'total_amount' => $totalAmount
            ];
    }

        
}