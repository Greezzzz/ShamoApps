<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Faker\Guesser\Name;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $description = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if($id){
            $product = Product::with(['category','galleries'])->find($id);

            if($product)
            {
                return ResponseFormatter::success(
                    $product,
                    'Data product berhasil diambil'
                );
            }
            else 
            {
                return ResponseFormatter::error(
                    null,
                    'Data product tidak ada',
                    404
                );
            }
        }

        $productQuery = Product::with(['category','galleries']);

        if($name)
        {
            $productQuery->where('name' , 'like','%' . $name . "%");
        }
        if($description)
        {
            $productQuery->where('description' , 'like','%' . $description . "%");
        }
        if($tags)
        {
            $productQuery->where('tags' , 'like','%' . $tags . "%");
        }
        if($categories)
        {
            $productQuery->where('categories' ,$categories);
        }

        if($price_from)
        {
            $productQuery->where('price', '>=', $price_from);
        }
        
        if($price_to)
        {
            $productQuery->where('price', '<=', $price_to);
        }

        $products = $productQuery->paginate($limit);

        // Cek jika tidak ada produk yang ditemukan
        if ($products->isEmpty()) {
            return ResponseFormatter::error(
                null,
                'Data product tidak ditemukan',
                404
            );
        }

        return ResponseFormatter::success(
            $products,
            'Data product berhasil diambil'
        );
    }
}
