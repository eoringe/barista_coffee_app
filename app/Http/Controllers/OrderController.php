<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            Log::info('Orders@index: starting fetch of PAID orders');

            // 1) Get orders with status = PAID
            $orders = DB::table('orders')
                ->select('id')
                ->where('status', 'PAID')
                ->orderByDesc('id')
                ->limit(100)
                ->get();

            if ($orders->isEmpty()) {
                Log::info('Orders@index: no PAID orders found');
                return response()->json(['success' => true, 'data' => []]);
            }

            $orderIds = $orders->pluck('id')->all();
            Log::info('Orders@index: fetched order IDs', ['count' => count($orderIds)]);

            // 2) Fetch order items joined with menu_items for titles and prices
            $items = DB::table('order_items as oi')
                ->join('menu_items as mi', 'mi.id', '=', 'oi.menu_item_id')
                ->select(
                    'oi.order_id',
                    'oi.menu_item_id',
                    'oi.size',
                    'oi.quantity',
                    'mi.coffee_title as title',
                    'mi.single_price',
                    'mi.double_price'
                )
                ->whereIn('oi.order_id', $orderIds)
                ->get();

            Log::info('Orders@index: fetched order_items', ['count' => $items->count()]);

            // 3) Fetch receipts for these orders
            $receipts = DB::table('receipts')
                ->select('order_id', 'receipt_number')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->keyBy('order_id');

            Log::info('Orders@index: fetched receipts', ['count' => $receipts->count()]);

            // 4) Flatten into UI-friendly rows (one row per item)
            $rows = $items->map(function ($it) use ($receipts) {
                $receipt = $receipts->get($it->order_id);
                $receiptNumber = $receipt->receipt_number ?? 'N/A';
                $size = strtolower((string) $it->size) === 'double' ? 'Double' : 'Single';
                $price = $size === 'Double' ? $it->double_price : $it->single_price;
                $priceStr = is_null($price) ? '—' : (string) $price . ' KES';
                return [
                    'order_id' => $it->order_id,
                    'receipt_number' => $receiptNumber,
                    'item' => $it->title,
                    'size' => $size,
                    'qty' => (int) $it->quantity,
                    'price' => $priceStr,
                ];
            })->values();

            Log::info('Orders@index: rows prepared', ['count' => $rows->count()]);

            return response()->json([
                'success' => true,
                'data' => $rows,
            ]);
        } catch (\Throwable $e) {
            Log::error('Orders@index: error fetching orders', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
