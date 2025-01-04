<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\GeneralTrait;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Shop;
use App\Helpers\Helper;

class ProductController extends Controller
{
    //
    use GeneralTrait;


    public function getHotProducts()
    {
        try {
            $products = Product::where('is_hot', 1)->with('category')->get();
            if ($products->isEmpty()) {
                return $this->notFoundResponse('No hot products found.');
            }
            return $this->apiResponse(ProductResource::collection($products), true, null);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function show($id)
    {
        try {
            $product = Product::with('category')->findOrFail($id);
            return $this->apiResponse(new ProductResource($product), true, null);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function getProductsByCategoryInShop($shopId, $categoryId)
    {
        try {

            $shop = Shop::with(['categories.products'])->findOrFail($shopId);


            $category = $shop->categories->where('id', $categoryId)->first();
            if (!$category) {
                return $this->notFoundResponse(__('translate.category_not_found_in_this_shop'));
            }


            $products = $category->products;


            if ($products->isEmpty()) {
                return $this->notFoundResponse(__('translate.product_category_shop') );
            }


            return $this->apiResponse(ProductResource::collection($products), true, null);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function showProductWithSimilar($productId)
    {
        try {

            $product = Product::with('category')->findOrFail($productId);


            $similarProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->get();


            $similarProductsResource = $similarProducts->isNotEmpty()
                ? ProductResource::collection($similarProducts)
                : [];


            $response = [
                'product' => new ProductResource($product),
                'similar_products' => $similarProductsResource
            ];

            return $this->apiResponse($response, true, null);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.ar' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'is_hot' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'profit_percentage' => 'required|numeric|min:0|max:100',
        ]);


        $imagePath = Helper::uploadImage('products', $request->file('image'));


        $product = Product::create([
            'name' => [
                'en' => $data['name']['en'],
                'ar' => $data['name']['ar'],
            ],
            'image' => $imagePath,
            'is_hot' => $data['is_hot'],
            'category_id' => $data['category_id'],
            'price' => $data['price'],
            'profit_percentage' => $data['profit_percentage'],
        ]);

        return $this->apiResponse(new ProductResource($product), true, null, 201);
    }


    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'sometimes|array',
            'name.en' => 'sometimes|string|max:255',
            'name.ar' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'is_hot' => 'sometimes|boolean',
            'category_id' => 'sometimes|exists:categories,id',
            'price' => 'sometimes|numeric|min:0',
            'profit_percentage' => 'sometimes|numeric|min:0|max:100',
        ]);

        // تحديث الصورة إذا وُجدت
        if ($request->hasFile('image')) {
            Helper::deleteFile($product->image);
            $product->image = Helper::uploadImage('products', $request->file('image'));
        }

        // تحديث الحقول القابلة للترجمة
        if (isset($data['name'])) {
            $product->setTranslations('name', [
                'en' => $data['name']['en'] ?? $product->getTranslation('name', 'en'),
                'ar' => $data['name']['ar'] ?? $product->getTranslation('name', 'ar'),
            ]);
        }


        $product->fill($data);
        $product->save();

        return $this->apiResponse(new ProductResource($product), true, null, 200);
    }


    public function destroy(Product $product)
    {
        Helper::deleteFile($product->image);
        $product->delete();

        return $this->apiResponse(null, true, null, 200);
    }
}
