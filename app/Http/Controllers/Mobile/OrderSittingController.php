<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderSetting;
use App\Http\Traits\GeneralTrait;

class OrderSittingController extends Controller
{
    //
    use GeneralTrait;
    public function showPremiumPercentage()
    {
        $percentage = OrderSetting::where('key', 'premium_percentage')->value('value');
        return $this->apiResponse(['premium_percentage' => $percentage], true, null);
    }

    public function updatePremiumPercentage(Request $request)
    {
        $data = $request->validate([
            'premium_percentage' => 'required|numeric|min:0|max:100',
        ]);

        OrderSetting::updateOrCreate(
            ['key' => 'premium_percentage'],
            ['value' => $data['premium_percentage']]
        );

        return $this->apiResponse(['message' => 'Premium percentage updated successfully'], true, null);
    }
}
