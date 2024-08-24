<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BasController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends BasController
{
    public function create(Request $request): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required|unique:roles,name',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $role = Role::create(['name' => $input['name']]);


        $success = $role->toArray();
        return $this->sendResponse($success, 'Role created successfully.');
    }

    public function userRoles(Request $request): JsonResponse
    {
        $limit = $request->get('limit') ?  $request->get('limit') : 10;
        $users = User::with("roles")->whereHas("roles");

        $success = $users->paginate($limit);
        return $this->sendResponse($success, 'User with role successfully.');
    }

    public function userRoleSearch(Request $request): JsonResponse
    {
        $term = $request->route('term');
        $limit = $request->get('limit') ?  $request->get('limit') : 10;
        $searchResults = User::where('name', 'LIKE', '%' . $term . '%')
            ->where('email', 'LIKE', '%' . $term . '%');

        $success = $searchResults->with("roles")->paginate($limit);
        return $this->sendResponse($success, 'User with role successfully.');
    }

    public function userSearch(Request $request): JsonResponse
    {
        $term = $request->route('term');
        $limit = $request->get('limit') ?  $request->get('limit') : 10;
        $searchResults = User::whereHas("roles")->where('name', 'LIKE', '%' . $term . '%');

        $success = $searchResults->with("roles")->paginate($limit);
        return $this->sendResponse($success, 'User with role successfully.');
    }

    public function userDetails(Request $request): JsonResponse
    {
        $id = $request->route('id');
        $user = User::where('id', $id)->with("roles")->firstOrFail();
        return $this->sendResponse($user, 'User data with role read.');
    }

    public function assignRoleToUser(Request $request): JsonResponse
    {
        $id =  $request->get('role_id');

        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        $validator = Validator::make($input, [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->sendError('Validation Error.', $errors);
        }

        $user = User::find($input['user_id']);

        $role = Role::findById($id);
//        return $this->sendResponse($role, 'assign Roles successfully.');

        $user->assignRole($role->name);

        $success = Role::with('permissions')->get();
        return $this->sendResponse($success, 'assign Roles successfully.');
    }

    public function removeRoleFromUser(Request $request): JsonResponse
    {
        $id =  $request->get('user_id');

        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        $validator = Validator::make($input, [
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->sendError('Validation Error.', $errors);
        }

        $user = User::find($id);

        $role = Role::findById($input['role_id']);
        $user->removeRole($role->name);

        $success = Role::with('permissions')->get();
        return $this->sendResponse($success, 'remove Roles successfully.');
    }

    public function getAll(Request $request): JsonResponse
    {
        $limit = $request->get('limit') ?  $request->get('limit') : 10;

        $success = Role::with('permissions')->paginate($limit);
        return $this->sendResponse($success, 'Role read successfully.');
    }

    public function details(Request $request): JsonResponse
    {
        $id =  $request->route('id');
        $role = Role::with('permissions')->find($id);
        return $this->sendResponse($role, 'Permission revoked to role successfully.');
    }

    public function search(Request $request): JsonResponse
    {
        $term = $request->route('term');
        $limit = $request->get('limit') ?  $request->get('limit') : 10;

        $searchResults = Role::with('permissions')->where('name', 'LIKE', '%' . $term . '%')
            ->paginate($limit);
        return $this->sendResponse($searchResults, 'Permission search read successfully.');
    }

    public function assignSuperAdminToUser(Request $request) : JsonResponse
    {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        $validator = Validator::make($input, [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->sendError('Validation Error.', $errors);
        }

        $user = User::find($input['user_id']);
        $user->assignRole("SuperAdmin");

        $success = User::with('roles')->where("id",$input['user_id'])->first();
        return $this->sendResponse($success, 'assign Roles successfully.');
    }

    public function givePermissionToRole(Request $request): JsonResponse
    {
        $id =  $request->get('role_id');
        $role = Role::findById($id);

        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        $validator = Validator::make($input, [
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->sendError('Validation Error.', $errors);
        }

        $role->givePermissionTo($input['permissions']);
        $success = Role::with('permissions')->find($id);

        return $this->sendResponse($success, 'Permission given to role successfully.');
    }

    public function revokePermissionFromRole(Request $request): JsonResponse
    {
        $id =  $request->get('role_id');
        $role = Role::findById($id);

        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        $validator = Validator::make($input, [
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->sendError('Validation Error.', $errors);
        }

        foreach ($input['permissions'] as $item) { //if name provied no loop needed
            $role->revokePermissionTo($item);
        }

        $success = Role::with('permissions')->find($id);
        return $this->sendResponse($success, 'Permission revoked to role successfully.');
    }

    public function deleteRole($roleId): JsonResponse
    {
        $role = Role::findOrFail($roleId);
        $role->delete();

        $success = $role->toArray();
        return $this->sendResponse($success, 'Role deleted successfully.');
    }
}
