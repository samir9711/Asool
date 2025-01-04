<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkUser;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\GeneralTrait;

class WorkUserController extends Controller
{
    //
    use GeneralTrait;
    public function createWorkUser(Request $request)
{
    try {

        $currentUser = Auth::user();


        if (!in_array($currentUser->type, ['super', 'admin'])) {
            return $this->forbiddenResponse();
        }


        $data = $request->validate([
            'username' => 'required|string|unique:work_users,username',
            'password' => 'required|string|min:6',
            'is_admin' => 'sometimes|boolean',
        ]);


        $data['password'] = bcrypt($data['password']);


        $data['is_admin'] = $data['is_admin'] ?? false;


        if ($currentUser->type === 'admin' && $data['is_admin'] === true) {
            return $this->forbiddenResponse();
        }


        $workUser = WorkUser::create([
            'username' => $data['username'],
            'password' => $data['password'],
            'type' => $data['is_admin'] ? 'admin' : 'employee',
        ]);


        return $this->apiResponse([
            'user_id' => $workUser->id,
            'username' => $workUser->username,
            'type' => $workUser->type,
        ], true, null, 201);

    } catch (\Exception $e) {

        \Log::error('Error creating work user:', ['message' => $e->getMessage()]);
        return $this->handleException($e);
    }
}


}
