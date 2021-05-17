<?php

namespace App\Http\Controllers;

use App\User;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class UserController extends Controller
{public function index()
    {
    $users = DB::table('users')->get();
     return response()->json($users);
    }

    public function show($id)
    {
        $user = User::with('roles')->find($id);
        $roles = Role::all();

        if (!empty($user)) {
            $user_roles = [];
            $user_roles = collect($user_roles);

            foreach ($roles as $role) {
                if (DB::table('role_user')
                    ->where('role_id', $role->id)
                    ->where('user_id', $id)
                    ->exists()
                ) {
                    $user_roles->put($role->name, true);
                } else {
                    $user_roles->put($role->name, false);
                }
            }

            $user_roles = $user_roles->all();

            $user = collect($user);
            $user->put('roles', $user_roles);
            $user = $user->all();

            return response()->json($user);
        }

        return response()->json(['error' => 'Resource not found!'], 404);
    }

    public function store(Request $request)
    {
        $user = User::where('email', '=', $request->email)->first();

        if (empty($user)) {
            DB::table('users')->insert([
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'remember_token' => str_random(10),
                    'created_at' =>  \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now()
                ]
            ]);

            return response()->json($this->index());
        } else {
            return response()->json(['error' => 'Email already exists!'], 500);
        }
    }

    public function update(Request $request, $id)
    {

        if (empty($request->password)) {
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'updated_at' => \Carbon\Carbon::now()
                ]);
        } else {
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'updated_at' => \Carbon\Carbon::now()
                ]);
        }

        if (!empty($request->roles)) {
            DB::table('role_user')->where('user_id', $id)->delete();
            $request->roles = (object)$request->roles;
            $this->setRoles($request->roles, $id);
        }

        return response()->json($this->index());
    }

    public function showSearch(Request $request)
    {
        $users = User::where('name', 'like', '%' . $request->user . '%')->get();
        return response()->json($users);
    }

    public function setRoles($roles, $id)
    {
        if ($roles->create_account) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_account')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->update_account) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'update_account')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->create_client) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_client')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->update_client) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'update_client')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->create_warehouse) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_warehouse')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->update_warehouse) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'update_warehouse')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->create_item) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_item')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->update_item) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'update_item')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->delete_item) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'delete_item')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->create_category) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_category')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->update_category) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'update_category')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->delete_category) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'delete_category')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->create_sales_order) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_sales_order')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->update_sales_order) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'update_sales_order')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->create_delivery_receipt) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_delivery_receipt')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->create_purchase_order) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_purchase_order')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->update_purchase_order) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'update_purchase_order')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->approved_purchase_order) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'approved_purchase_order')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->approved_sales_order) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'approved_sales_order')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->create_sales_return) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_sales_return')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->create_company_assets) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_company_assets')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->update_company_assets) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'update_company_assets')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->delete_company_assets) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'delete_company_assets')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->create_supplier) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'create_supplier')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }

        if ($roles->update_supplier) {
            $role = DB::table('roles')
                ->select('id')
                ->where('name', 'update_supplier')
                ->first();

            if (DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('user_id', $id)
                ->doesntExist()
            ) {
                DB::table('role_user')->insert(
                    ['user_id' => $id, 'role_id' => $role->id]
                );
            }
        }
    }
}
