<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\GeneralTrait;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    //
    use GeneralTrait;


    public function getInterestedShops()
    {
        try {
            $shops = Shop::where('is_interested', 1)->get();
            return $this->apiResponse(ShopResource::collection($shops), true, null);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function show($id)
    {
        try {
            $shop = Shop::findOrFail($id);
            return $this->apiResponse(new ShopResource($shop), true, null);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function index()
    {
        try {
            $categories = Shop::all();
            if ($categories->isEmpty()) {
                return $this->notFoundResponse('No shops found.');
            }
            return $this->apiResponse(ShopResource::collection($categories), true, null);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function store(Request $request)
    {
        try {
            \Log::info('Store Shop Request Received', ['request' => $request->all()]);


            $user = auth('work_user')->user();
            if (!$user) {
                \Log::error('Unauthorized Access Attempt');
                return $this->unAuthorizeResponse();
            }
            \Log::info('Authenticated User', ['user' => $user]);


            $data = $request->validate([
                'name' => 'required|array',
                'name.en' => 'required|string|max:255',
                'name.ar' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg',
                'is_interested' => 'required|boolean',
            ]);
            \Log::info('Validated Data', ['data' => $data]);


            $imagePath = Helper::uploadImage('shops', $request->file('image'));
            \Log::info('Image Uploaded', ['image_path' => $imagePath]);


            $shop = Shop::create([
                'name' => [
                    'en' => $data['name']['en'],
                    'ar' => $data['name']['ar'],
                ],
                'image' => $imagePath,
                'is_interested' => $data['is_interested'],
            ]);
            \Log::info('Shop Created Successfully', ['shop' => $shop]);

            return $this->apiResponse(new ShopResource($shop), true, null, 201);

        } catch (\Exception $e) {
            \Log::error('Error in Store Shop', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the shop.',
            ], 500);
        }
    }


    public function update(Request $request, Shop $shop)
{
    \Log::info('Update Shop Request Received', [
        'shop_id' => $shop->id,
        'request_data' => $request->all()
    ]);

    $user = auth('work_user')->user();
    if (!$user) {
        \Log::error('Unauthorized Access Attempt');
        return $this->unAuthorizeResponse();
    }

    $data = $request->validate([
        'name' => 'sometimes|array',
        'name.en' => 'sometimes|string|max:255',
        'name.ar' => 'sometimes|string|max:255',
        'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        'is_interested' => 'sometimes|boolean',
    ]);

    \Log::info('Validated Data', ['data' => $data]);

    if ($request->hasFile('image')) {
        Helper::deleteFile($shop->image);
        $shop->image = Helper::uploadImage('shops', $request->file('image'));
        \Log::info('Image Updated', ['image_path' => $shop->image]);
    }

    if (isset($data['name'])) {
        $shop->setTranslations('name', [
            'en' => $data['name']['en'] ?? $shop->getTranslation('name', 'en'),
            'ar' => $data['name']['ar'] ?? $shop->getTranslation('name', 'ar'),
        ]);
        \Log::info('Translations Updated', ['name' => $shop->name]);
    }

    if (isset($data['is_interested'])) {
        $shop->is_interested = $data['is_interested'];
        \Log::info('is_interested Updated', ['is_interested' => $shop->is_interested]);
    }

    $shop->save();

    \Log::info('Shop Updated Successfully', ['shop' => $shop]);

    return $this->apiResponse(new ShopResource($shop), true, null, 200);
}



public function destroy(Shop $shop)
{
    $user = auth('work_user')->user();
    if (!$user) {
        return $this->unAuthorizeResponse();
    }

    Helper::deleteFile($shop->image);
    $shop->delete();

    return $this->apiResponse(null, true, null, 200);
}




}
