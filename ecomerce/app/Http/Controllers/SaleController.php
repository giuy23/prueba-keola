<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleDetailResource;
use App\Models\Product;
use App\Models\ProductSale;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{

    public function store(Request $request)
    {
        $products = $request['products'];
        $total = 0;

        foreach ($products as $item) {
            $product = $this->searchProduct($item);
            $total = $total + ($product->price * $item['quantity']);
        }

        DB::beginTransaction();
        try {
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'total' => floatval($total),
            ]);

            foreach ($products as $item) {
                $product = $this->searchProduct($item);
                ProductSale::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'sub_total' => floatval($item['quantity'] * $product->price)
                ]);
            }

            (new ProductController())->decreaseQuantity($products);
            DB::commit();
            return response()->json(['message' => 'Venta realizada con éxito'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al realizar la venta'], 500);
        }
    }

    private function searchProduct($item)
    {
        $product = Product::find($item['id']);

        return $product;
    }

    public function detailSale(Request $request)
    {
        $saleId = $request['saleId'];

        $detail = ProductSale::where('sale_id', $saleId)->get();

        $saleDetails = SaleDetailResource::collection($detail);

        return response()->json($saleDetails, 200);
    }

    public function updateSale(Request $request)
    {
        $sale = Sale::find($request['saleId']);
        $products = $request['products'];
        $total = 0;

        DB::beginTransaction();
        try {
            foreach ($products as $item) {
                $productInSale = ProductSale::with('product')->find($item['id']);
                $product = $productInSale->product;

                $total += $product->price * $item['quantity'];
            }

            $sale->update([
                'total' => floatval($total)
            ]);

            $productController = new ProductController();

            foreach ($products as $item) {
                $productInSale = ProductSale::with('product')->find($item['id']);
                $productSale = $productInSale;
                $product = $productInSale->product;

                if ($item['quantity'] != $productSale->quantity) {
                    $quantityDifference = abs($item['quantity'] - $productSale->quantity);

                    if ($item['quantity'] > $productSale->quantity) {
                        $productToUpdate = [
                            'id' => $product->id,
                            'quantity' => $quantityDifference
                        ];
                        $productController->decreaseQuantity([$productToUpdate]);
                    } else {
                        $productToUpdate = [
                            'id' => $product->id,
                            'quantity' => $quantityDifference
                        ];
                        $productController->increaseQuantity(new Request(['products' => [$productToUpdate]]));
                    }

                    $productSale->update([
                        'quantity' => $item['quantity'],
                        'sub_total' => floatval($item['quantity'] * $product->price)
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Venta actualizada'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Hubo un problema al actualizar la venta'], 500);
        }
    }

    public function destroy(Sale $sale)
    {
        $sale->products()->detach();
        $sale->delete();

        return response()->json(['message' => 'Venta eliminada con éxito'], 200);
    }
}
