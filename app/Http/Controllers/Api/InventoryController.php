<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $inventories = Inventory::all();
            return response()->json($inventories);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch inventory data.',
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
                'item_name' => 'required|string',
                'unit' => 'required|string',
                'quantity' => 'required|integer|min:0',
                'threshold' => 'required|integer|min:0',
            ]);

            $inventory = Inventory::create($validated);
            return response()->json($inventory, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create inventory.',
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
            $inventory = Inventory::findOrFail($id);
            return response()->json($inventory);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Inventory not found'], 404);
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
            $inventory = Inventory::findOrFail($id);
            $inventory->update($request->only(['item_name', 'unit', 'quantity', 'threshold']));
            return response()->json($inventory);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Inventory not found'], 404);
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
            $inventory = Inventory::findOrFail($id);
            $inventory->delete();
            return response()->json(['message' => 'Deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Inventory not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function adjustQuantity(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|integer',
            ]);

            $inventory = Inventory::findOrFail($id);

            $newQuantity = $inventory->quantity + $validated['amount'];
            if ($newQuantity < 0) {
                return response()->json([
                    'message' => 'Stok tidak boleh kurang dari 0.'
                ], 422);
            }

            $inventory->quantity = $newQuantity;
            $inventory->save();

            return response()->json([
                'message' => 'Stok berhasil diperbarui.',
                'inventory' => $inventory
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Data inventaris tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui stok.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
