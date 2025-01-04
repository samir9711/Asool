<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\GeneralTrait;
use App\Models\Category;
use App\Models\Shop;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Helpers\Helper;
class CategoryController extends Controller
{
    //
    use GeneralTrait;

    public function getInterestedCategories()
    {
        try {
            $categories = Category::where('is_interested', true)->get();

            if ($categories->isEmpty()) {
                return $this->notFoundResponse(__('category.category_not_found'));
            }

            return $this->apiResponse(CategoryResource::collection($categories));
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function show($id)
{
    try {
        $category = Category::with('shop')->findOrFail($id);

        return $this->apiResponse(new CategoryResource($category), true, false);
    } catch (\Exception $e) {
        return $this->handleException($e);
    }
}

public function index()
    {
        try {
            $categories = Category::all();
            if ($categories->isEmpty()) {
                return $this->notFoundResponse(__('category.category_not_found'));
            }
            return $this->apiResponse(CategoryResource::collection($categories), true, null);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    /*public function search(Request $request)
    {
        try {
            $query = $request->get('query');
            if (!$query) {
                return $this->requiredField('Search query is required.');
            }

            $categories = Category::where('name->' . app()->getLocale(), 'LIKE', "%{$query}%")->get();

            if ($categories->isEmpty()) {
                return $this->notFoundResponse('No categories match your search.');
            }
            return $this->apiResponse(CategoryResource::collection($categories), true, null);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
*/

public function getCategoriesByShop($shopId)
{
    try {

        $shop = Shop::with('categories')->findOrFail($shopId);


        if ($shop->categories->isEmpty()) {
            return $this->notFoundResponse(__('translate.no_categories_found_for_this_shop'));
        }


        return $this->apiResponse(CategoryResource::collection($shop->categories), true, null);
    } catch (\Exception $e) {
        return $this->handleException($e);
    }
}



public function getProductsByCategory($categoryId)
{
    try {

        $category = Category::with('products')->findOrFail($categoryId);


        if ($category->products->isEmpty()) {
            return $this->notFoundResponse(__('translate.no_products_found_for_this_category'));
        }


        return $this->apiResponse(ProductResource::collection($category->products), true, null);
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
            'is_interested' => 'required|boolean',
            'shop_id' => 'required|exists:shops,id',
        ]);


        $imagePath = Helper::uploadImage('categories', $request->file('image'));


        $category = Category::create([
            'name' => [
                'en' => $data['name']['en'],
                'ar' => $data['name']['ar'],
            ],
            'image' => $imagePath,
            'is_interested' => $data['is_interested'],
            'shop_id' => $data['shop_id'],
        ]);

        return $this->apiResponse(new CategoryResource($category), true, null, 201);
    }


    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'sometimes|array',
            'name.en' => 'sometimes|string|max:255',
            'name.ar' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'is_interested' => 'sometimes|boolean',
            'shop_id' => 'sometimes|exists:shops,id',
        ]);


        if ($request->hasFile('image')) {
            Helper::deleteFile($category->image);
            $category->image = Helper::uploadImage('categories', $request->file('image'));
        }


        if (isset($data['name'])) {
            $category->setTranslations('name', [
                'en' => $data['name']['en'] ?? $category->getTranslation('name', 'en'),
                'ar' => $data['name']['ar'] ?? $category->getTranslation('name', 'ar'),
            ]);
        }


        if (isset($data['is_interested'])) {
            $category->is_interested = $data['is_interested'];
        }
        if (isset($data['shop_id'])) {
            $category->shop_id = $data['shop_id'];
        }

        $category->save();

        return $this->apiResponse(new CategoryResource($category), true, null, 200);
    }


    public function destroy(Category $category)
    {
        Helper::deleteFile($category->image);
        $category->delete();

        return $this->apiResponse(null, true, null, 200);
    }

}
