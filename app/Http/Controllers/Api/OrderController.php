<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $orders = Order::all();
            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch orders.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string',
                'phone_number' => 'required|string',
                'file_url' => 'nullable|string',
                'product_type' => 'required|string',
                'quantity' => 'required|integer|min:1',
                'paper_type' => 'required|string',
                'size' => 'required|string',
                'status' => 'in:menunggu,diproses,selesai,batal',
                'deadline' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            $order = Order::create($validated);
            return response()->json($order, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $order = Order::findOrFail($id);
            return response()->json($order);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $order = Order::findOrFail($id);

            $order->update($request->only([
                'customer_name',
                'phone_number',
                'file_url',
                'product_type',
                'quantity',
                'paper_type',
                'size',
                'status',
                'deadline',
                'notes'
            ]));

            return response()->json($order);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();
            return response()->json(['message' => 'Deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateOrderProgress(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:menunggu,diproses,selesai',
                'process_note' => 'nullable|string',
            ]);

            $order = Order::findOrFail($id);

            $order->status = $validated['status'];
            $order->process_note = $validated['process_note'] ?? $order->process_note;
            $order->save();

            return response()->json([
                'message' => 'Order progress updated successfully.',
                'order' => $order
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update order progress.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function history(Request $request)
    {
        try {
            $query = Order::query();

            // Filter berdasarkan status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan rentang tanggal (created_at)
            if ($request->has(['start_date', 'end_date'])) {
                $query->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data pesanan yang ditemukan.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'message' => 'Riwayat pesanan berhasil diambil.',
                'data' => $orders
            ], 200);

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil riwayat pesanan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
