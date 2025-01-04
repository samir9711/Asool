<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\OrderResource;
use App\Models\OrderSetting;
use App\Models\Category;
use App\Models\Shop;
use Carbon\Carbon;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ShopResource;


class OrderController extends Controller
{
    //
    use GeneralTrait;
    public function createOrder(Request $request)
{
    DB::beginTransaction();

    try {
        $data = $request->validate([
            'order_items' => 'required|array|min:1',
            'order_items.*.product_id' => 'required|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'reciver_name' => 'required|string',
            'reciver_phone' => 'required|string',
            'is_premium' => 'sometimes|boolean',
        ]);

        $user = auth()->user();
        if (!$user) {
            return $this->unAuthorizeResponse();
        }

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 0,
            'date' => now(),
            'lat' => $data['lat'],
            'lon' => $data['lon'],
            'reciver_name' => $data['reciver_name'],
            'reciver_phone' => $data['reciver_phone'],
            'status' => 'pending',
            'is_premium' => $data['is_premium'] ?? false,
        ]);

        $totalPrice = 0;
        foreach ($data['order_items'] as $item) {
            $product = Product::find($item['product_id']);
            $profitPercentage = $product->profit_percentage;
            $finalPrice = $product->price + ($product->price * $profitPercentage / 100);
            $itemPrice = $finalPrice * $item['quantity'];
            $totalPrice += $itemPrice;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => round($itemPrice, 2),
            ]);
        }

        if ($order->is_premium) {
            $premiumPercentage = OrderSetting::where('key', 'premium_percentage')->value('value') ?? 0;
            $totalPrice += $totalPrice * ($premiumPercentage / 100);
        }

        $order->update(['total_price' => round($totalPrice, 2)]);

        DB::commit();

        return $this->apiResponse([
            'order_id' => $order->id,
            'total_price' => round($totalPrice, 2),
            'status' => $order->status,
        ], true, null);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error creating order:', ['error' => $e->getMessage()]);
        return $this->handleException($e);
    }
}


    public function getOrders(Request $request)
    {
        $query = Order::query()->with(['user', 'orderItems.product.category', 'orderItems.product']);


        if ($request->has('min_price')) {
            $query->where('total_price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('total_price', '<=', $request->max_price);
        }


        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }


        if ($request->has('status')) {
            $query->where('status', $request->status);
        }


        if ($request->has('reciver_name')) {
            $query->where('reciver_name', 'like', '%' . $request->reciver_name . '%');
        }




        if ($request->has('category_id')) {
            $query->whereHas('orderItems.product.category', function ($q) use ($request) {
                $q->where('id', $request->category_id);
            });
        }


        $perPage = $request->has('per_page') ? (int) $request->per_page : 5;


        $orders = $query->paginate($perPage);

        return OrderResource::collection($orders);
    }


    public function setPreparingStatus(Request $request, Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order is not in pending state.'], 400);
        }

        $order->update(['status' => 'prepering']);

        return response()->json(['message' => 'Order status updated to preparing.', 'order' => $order], 200);
    }


    public function rejectOrder(Request $request, Order $order)
    {
        $request->validate(['reject_reason' => 'required|string|max:255']);

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order is not in pending state.'], 400);
        }

        $order->update([
            'status' => 'fail',
            'reject_reason' => $request->reject_reason,
        ]);

        return response()->json(['message' => 'Order rejected successfully.', 'order' => $order], 200);
    }



    public function setDoneStatus(Request $request, Order $order)
    {
        if ($order->status !== 'prepering') {
            return response()->json(['error' => 'Order is not in prepering state.'], 400);
        }

        $order->update(['status' => 'done']);


        $user = $order->user;
        $pointsToAdd = intval($order->total_price / 100);
        $user->update([
            'points' => $user->points + $pointsToAdd,
        ]);


        return response()->json([
            'message' => 'Order status updated to done, and points added to the user.',
            'order' => $order,
            'user_points' => $pointsToAdd,

        ], 200);
    }


    public function topProducts(Request $request)
    {
        try {
            $range = $request->query('range', 'monthly'); // Default to monthly
            $dateFrom = $this->getDateRange($range);

            $products = OrderItem::whereHas('order', function ($query) use ($dateFrom) {
                $query->where('date', '>=', $dateFrom);
            })
                ->selectRaw('product_id, SUM(quantity) as total_sold')
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'product' => new ProductResource(Product::find($item->product_id)),
                        'total_sold' => $item->total_sold,
                    ];
                });

            return $this->apiResponse($products);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    // Get top 10 sold categories
    public function topCategories(Request $request)
    {
        try {
            $range = $request->query('range', 'monthly');
            $dateFrom = $this->getDateRange($range);

            $categories = OrderItem::whereHas('order', function ($query) use ($dateFrom) {
                $query->where('date', '>=', $dateFrom);
            })
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->selectRaw('products.category_id, SUM(order_items.quantity) as total_sold')
                ->groupBy('products.category_id')
                ->orderByDesc('total_sold')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'category' => new CategoryResource(Category::find($item->category_id)),
                        'total_sold' => $item->total_sold,
                    ];
                });

            return $this->apiResponse($categories);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    // Get top 10 sold shops
    public function topShops(Request $request)
    {
        try {
            $range = $request->query('range', 'monthly');
            $dateFrom = $this->getDateRange($range);

            $shops = OrderItem::whereHas('order', function ($query) use ($dateFrom) {
                $query->where('date', '>=', $dateFrom);
            })
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->join('shops', 'categories.shop_id', '=', 'shops.id')
                ->selectRaw('shops.id, shops.name, SUM(order_items.quantity) as total_sold')
                ->groupBy('shops.id', 'shops.name')
                ->orderByDesc('total_sold')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'shop' => new ShopResource(Shop::find($item->id)),
                        'total_sold' => $item->total_sold,
                    ];
                });

            return $this->apiResponse($shops);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    // Helper function to calculate date range
    private function getDateRange($range)
    {
        switch ($range) {
            case 'weekly':
                return Carbon::now()->subWeek();
            case 'yearly':
                return Carbon::now()->subYear();
            case 'monthly':
            default:
                return Carbon::now()->subMonth();
        }
    }





    private function getPeriodKey($date, $range)
    {
        $carbonDate = Carbon::parse($date);

        switch ($range) {
            case 'weekly':
                return $carbonDate->format('D');
            case 'yearly':
                return $carbonDate->format('M');
            case 'monthly':
            default:
                return $carbonDate->format('d');
        }
    }


    public function getEarnings(Request $request)
{
    try {
        $range = $request->query('range', 'monthly');
        $dateFrom = $this->getDateRange($range);

        $orders = Order::where('status', 'done')
            ->where('date', '>=', $dateFrom)
            ->with(['orderItems.product'])
            ->get();

        $earnings = [];
        foreach ($orders as $order) {

            $basePriceTotal = 0;

            foreach ($order->orderItems as $item) {
                $basePriceTotal += $item->product->price * $item->quantity;
            }


            $orderProfit = $order->total_price - $basePriceTotal;

            $periodKey = $this->getPeriodKey($order->date, $range);
            if (!isset($earnings[$periodKey])) {
                $earnings[$periodKey] = 0;
            }

            $earnings[$periodKey] += $orderProfit;
        }

        
        $formattedEarnings = [];
        foreach ($earnings as $period => $profit) {
            $formattedEarnings[] = [
                'period' => $period,
                'profit' => round($profit, 2),
            ];
        }

        return $this->apiResponse($formattedEarnings);

    } catch (\Exception $e) {
        return $this->handleException($e);
    }
}



}
