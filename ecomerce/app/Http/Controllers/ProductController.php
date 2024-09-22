<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    public function list()
    {
        $products = Product::with('image')->where('quantity', '>', 0)->get();
        $products = ProductResource::collection($products);

        return response()->json($products, 200);

    }

    public function decreaseQuantity($products)
    {
        DB::beginTransaction();
        try {
            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['id']);
                $newQuantity = $product->quantity - $productData['quantity'];
                if ($newQuantity < 0) {
                    throw new \Exception("Cantidad insuficiente para el producto: {$product->name}");
                }
                $product->update(['quantity' => $newQuantity]);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }


    public function increaseQuantity(Request $request)
    {
        $products = $request['products'];

        DB::beginTransaction();
        try {
            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['id']);
                $newQuantity = $product->quantity + $productData['quantity'];
                $product->update(['quantity' => $newQuantity]);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function listPurchases()
    {
        $userId = Auth::id();

        $sales = Sale::where('user_id', $userId)->with('products')->get();

        return response()->json($sales, 200);
    }
}
