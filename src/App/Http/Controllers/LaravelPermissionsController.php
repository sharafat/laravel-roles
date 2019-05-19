<?php

namespace jeremykenedy\LaravelRoles\App\Http\Controllers;

use App\Http\Controllers\Controller;
use jeremykenedy\LaravelRoles\App\Http\Requests\StorePermissionRequest;
use jeremykenedy\LaravelRoles\App\Http\Requests\UpdatePermissionRequest;
use jeremykenedy\LaravelRoles\App\Services\PermissionFormFields;
use jeremykenedy\LaravelRoles\Traits\RolesAndPermissionsHelpersTrait;
use Illuminate\Http\Request;

class LaravelPermissionsController extends Controller
{
    use RolesAndPermissionsHelpersTrait;

    private $_rolesGuiAuthEnabled;
    private $_rolesGuiMiddlewareEnabled;
    private $_rolesGuiMiddleware;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_rolesGuiAuthEnabled         = config('roles.rolesGuiAuthEnabled');
        $this->_rolesGuiMiddlewareEnabled   = config('roles.rolesGuiMiddlewareEnabled');
        $this->_rolesGuiMiddleware          = config('roles.rolesGuiMiddleware');

        if ($this->_rolesGuiAuthEnabled) {
            $this->middleware('auth');
        }

        if ($this->_rolesGuiMiddlewareEnabled) {
            $this->middleware($this->_rolesGuiMiddleware);
        }
    }

    /**
     * Show the roles and Permissions dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->getDashboardData();

        return view($data['view'], $data['data']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $service    = new PermissionFormFields();
        $data       = $service->handle();

        return view('laravelroles::laravelroles.crud.permissions.create', $data);
    }

    /**
     * Store a newly created permission in storage.
     *
     * @param \jeremykenedy\LaravelRoles\App\Http\Requests\StorePermissionRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StorePermissionRequest $request)
    {
        $permissionData = $request->permissionFillData();
        $permission = $this->storeNewPermission($permissionData);

        return redirect()->route('laravelroles::roles.index')
                            ->with('success', trans('laravelroles::laravelroles.flash-messages.permission-create', ['permission' => $permission->name]));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->getPermissionItemData($id);

        return view('laravelroles::laravelroles.crud.permissions.show', $data);
    }

    /**
     * Edit the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $service    = new PermissionFormFields($id);
        $data       = $service->handle();

        return view('laravelroles::laravelroles.crud.permissions.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \jeremykenedy\LaravelRoles\App\Http\Requests\UpdatePermissionRequest $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePermissionRequest $request, $id)
    {
        $permissionData = $request->permissionFillData($id);
        $permission     = $this->updatePermission($id, $permissionData);

        return redirect()->route('laravelroles::roles.index')
            ->with('success', trans('laravelroles::laravelroles.flash-messages.permission-updated', ['permission' => $permission->name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $permission = config('roles.models.permission')::findOrFail($id);
        $this->removeUsersAndRolesFromPermissions($permission);
        $permission->delete();

        return redirect(route('laravelroles::roles.index'))
                    ->with('success', trans('laravelroles::laravelroles.flash-messages.successDeletedItem', ['type' => 'Permission', 'item' => $permission->name]));
    }
}