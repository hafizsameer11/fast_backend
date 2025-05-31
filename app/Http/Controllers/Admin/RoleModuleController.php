<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleModuleController extends Controller
{
    public function rolesindex(){
        $roles = Role::all();
        return response()->json($roles);
    }
    public function index(){
        $roles = Role::all();
        $modules = Module::all();
        return response()->json([
            'roles' => $roles,
            'modules' => $modules
        ]);
    }
    public function createRole(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        $role = Role::create(['name' => $request->name]);
        return response()->json($role);
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $request->validate(['name' => 'required|unique:roles,name,' . $id]);
        $role->update(['name' => $request->name]);
        return response()->json($role);
    }

    public function deleteRole($id)
    {
        Role::findOrFail($id)->delete();
        return response()->json(['message' => 'Role deleted']);
    }

    // Module APIs
    public function createModule(Request $request)
    {
        $request->validate(['name' => 'required|unique:modules,name']);
        $module = Module::create(['name' => $request->name]);
        return response()->json($module);
    }

    public function updateModule(Request $request, $id)
    {
        $module = Module::findOrFail($id);
        $request->validate(['name' => 'required|unique:modules,name,' . $id]);
        $module->update(['name' => $request->name]);
        return response()->json($module);
    }

    public function deleteModule($id)
    {
        Module::findOrFail($id)->delete();
        return response()->json(['message' => 'Module deleted']);
    }

    // Assign modules to role
    public function assignModulesToRole(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);
        $validator = \Validator::make($request->all(), [
            'module_ids' => 'required|array',
            'module_ids.*' => 'exists:modules,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role->modules()->sync($request->module_ids); // overwrite existing
        return response()->json(['message' => "$role->name permissions updated"]);
    }

    // Get roles with module permissions
    public function getRolesWithModules()
    {
        $modules = Module::all();
        $roles = Role::with('modules')->get();

        $data = $roles->map(function ($role) use ($modules) {
            $moduleStates = $modules->map(function ($module) use ($role) {
                return [
                    'module_id' => $module->id,
                    'module_name' => $module->name,
                    'has_permission' => $role->modules->contains($module->id)
                ];
            });

            return [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permissions' => $moduleStates
            ];
        });

        return response()->json($data);
    }
}
