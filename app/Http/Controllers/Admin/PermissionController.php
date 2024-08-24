<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BasController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class PermissionController extends BasController
{
    public function addRouteAsPermission(Request $request): JsonResponse
    {
        $routeCollection = Route::getRoutes();
        $inputArray = [];
        $added_items = Permission::pluck('name')->toArray(); //remove them from route

        foreach ($routeCollection as $value) {
            if (isset($value->action['as']) && str_starts_with($value->action['prefix'], 'api')) {
                array_push($inputArray, $value->action['as']);
            }
        }

        $need_to_add = array_diff($inputArray, $added_items);

        foreach ($need_to_add as $value) {
            Permission::create(['name' => $value]);
        }

        return $this->sendResponse($inputArray, 'Permission sync SuccessFully.');
    }


    public function getAllPermission(Request $request): JsonResponse
    {
        $limit = $request->get('limit') ?  $request->get('limit') : 10;

        $success = Permission::with('roles')->paginate($limit);

        return $this->sendResponse($success, 'Permission read successfully.');
    }

    public function searchPermission(Request $request): JsonResponse
    {
        $term = $request->route('term');
        $limit = $request->get('limit') ?  $request->get('limit') : 10;

        $searchResults = Permission::where('name', 'LIKE', '%' . $term . '%')
            ->paginate($limit);
        return $this->sendResponse($searchResults, 'Permission search read successfully.');
    }



}
