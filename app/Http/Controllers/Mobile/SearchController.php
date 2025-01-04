<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Category;
use App\Http\Resources\ShopResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\GeneralTrait;

class SearchController extends Controller
{
    //
    use GeneralTrait;

    public function search(Request $request)
    {
        try {

            $keyword = $request->get('keyword');
            if (!$keyword) {
                return $this->requiredField('Search keyword is required.');
            }


            $shops = Shop::where('name', 'like', '%' . $keyword . '%')->get();
            $products = Product::where('name', 'like', '%' . $keyword . '%')->get();
            $categories = Category::where('name', 'like', '%' . $keyword . '%')->get();


            if ($shops->isEmpty() && $products->isEmpty() && $categories->isEmpty()) {
                return $this->notFoundResponse('No results found for the given keyword.');
            }

            
            $response = [
                'shops' => ShopResource::collection($shops),
                'products' => ProductResource::collection($products),
                'categories' => CategoryResource::collection($categories),
            ];

            return $this->apiResponse($response, true, null);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
