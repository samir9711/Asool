<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product_offer;
use App\Http\Resources\ProductOfferResource;
use App\Http\Resources\ProductResource;
use App\Http\Traits\GeneralTrait;

class ProductOfferController extends Controller
{
    use GeneralTrait;

    //
    public function index(Request $request)
    {
        try {
            $offers = Product_offer::with('product')->get();
            if ($offers->isEmpty()) {
                return $this->notFoundResponse(__('product_offer.offer_not_found'));
            }

            return $this->apiResponse(ProductOfferResource::collection($offers), true, false);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }

    public function show($id)
    {
        try {
            $offer = Product_offer::with('product')->findOrFail($id);
            return $this->apiResponse(new ProductOfferResource($offer), true, false);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

}
