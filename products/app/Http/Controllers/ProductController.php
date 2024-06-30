<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Auth::user()->products;
            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching products.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
            ]);

            $product = Auth::user()->products()->create($request->all());

            return response()->json($product, 201);
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while creating the product.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Auth::user()->products()->findOrFail($id);
            return response()->json($product);
        } catch (\Exception $e) {
            Log::error('Error fetching product: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching the product.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Auth::user()->products()->findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
            ]);

            $product->update($request->all());

            return response()->json($product);
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while updating the product.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Auth::user()->products()->findOrFail($id);
            $product->delete();

            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while deleting the product.'], 500);
        }
    }
}
