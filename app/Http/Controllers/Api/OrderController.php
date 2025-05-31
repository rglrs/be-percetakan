<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $orders = Order::orderBy('created_at', 'desc')->get();
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
                'customer_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,ai,psd,cdr,eps|max:10240', // Max 10MB
                'product_type' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
                'paper_type' => 'required|string|max:255',
                'size' => 'required|string|max:100',
                'status' => 'sometimes|in:menunggu,diproses,selesai,batal',
                'deadline' => 'required|date_format:Y-m-d',
                'notes' => 'nullable|string',
            ]);

            $fileUrl = null;
            $fileName = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName(); // Dapatkan nama asli
                $filePath = $file->storeAs('order_files', $fileName); // Simpan dengan nama asli
                $fileUrl = Storage::url($filePath); // URL publik
            }

            $dataToCreate = $validated;

            if ($fileUrl) {
                $dataToCreate['file_url'] = $fileUrl;
                $dataToCreate['file_name'] = $fileName; // Tambahkan nama file asli
            }

            unset($dataToCreate['file']);

            if (!isset($dataToCreate['status'])) {
                $dataToCreate['status'] = 'menunggu';
            }

            $order = Order::create($dataToCreate);

            return response()->json($order, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
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

            // dd($request->all()); 

            $validated = $request->validate([
                'customer_name' => 'sometimes|required|string|max:255',
                'phone_number' => 'sometimes|required|string|max:20',
                'product_type' => 'sometimes|required|string|max:255',
                'quantity' => 'sometimes|required|integer|min:1',
                'paper_type' => 'sometimes|required|string|max:255',
                'size' => 'sometimes|required|string|max:100',
                'status' => 'sometimes|required|in:menunggu,diproses,selesai,batal',
                'deadline' => 'sometimes|required|date_format:Y-m-d',
                'notes' => 'nullable|string',
                'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,zip,rar|max:10240', // New file input, expecting 'file'
                'remove_existing_file' => 'sometimes|in:true,false,0,1', // Flag to remove existing file
            ]);

            $dataToUpdate = $validated;

            // Convert remove_existing_file string ('true'/'false') to boolean
            $removeExistingFile = filter_var($request->input('remove_existing_file', 'false'), FILTER_VALIDATE_BOOLEAN);

            $oldFileStoragePath = null;
            if ($order->file_url) {
                // Derive storage path from URL. Assumes URL is generated by Storage::disk('public')->url()
                $storageUrlPrefix = rtrim(Storage::disk('public')->url(''), '/'); // e.g., http://localhost/storage
                // Check if file_url starts with the storage prefix
                if (strpos($order->file_url, $storageUrlPrefix . '/') === 0) {
                    $oldFileStoragePath = substr($order->file_url, strlen($storageUrlPrefix . '/')); // e.g., order_files/unique_name.pdf
                }
            }

            if ($request->hasFile('file')) { // A new file is uploaded, check for 'file'
                // 1. Delete the old file if it exists
                if ($oldFileStoragePath && Storage::disk('public')->exists($oldFileStoragePath)) {
                    Storage::disk('public')->delete($oldFileStoragePath);
                }

                // 2. Store the new file
                $newUploadedFile = $request->file('file'); // Get file by name 'file'
                $newOriginalFileName = $newUploadedFile->getClientOriginalName();
                $sanitizedNewOriginalName = preg_replace('/[^A-Za-z0-9\._-]/', '', $newOriginalFileName);
                $newUniqueFileName = time() . '_' . $sanitizedNewOriginalName;

                $newStoredPath = $newUploadedFile->storeAs('order_files', $newUniqueFileName, 'public');

                $dataToUpdate['file_url'] = Storage::disk('public')->url($newStoredPath);
                $dataToUpdate['file_name'] = $newOriginalFileName; // Store original name

            } elseif ($removeExistingFile) { // No new file, but remove_existing_file is true
                if ($oldFileStoragePath && Storage::disk('public')->exists($oldFileStoragePath)) {
                    Storage::disk('public')->delete($oldFileStoragePath);
                }
                $dataToUpdate['file_url'] = null;
                $dataToUpdate['file_name'] = null;
            }
            // If no new file is uploaded and removeExistingFile is false,
            // file_url and file_name from the existing $order record remain untouched
            // unless they are part of $validated (which they are not, as they are derived).

            // Remove temporary/helper fields from data before updating the model
            unset($dataToUpdate['file']); // Unset 'file'
            unset($dataToUpdate['remove_existing_file']);

            $order->update($dataToUpdate);

            return response()->json($order->fresh()); // Return the updated model with fresh data

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Order update failed for ID ' . $id . ': ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
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
