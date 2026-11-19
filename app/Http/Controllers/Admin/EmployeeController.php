<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Admin;
use App\Services\AdminGeoScopeSyncService;
use App\Services\AdminPermissionSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(
        protected AdminPermissionSyncService $permissionSync,
        protected AdminGeoScopeSyncService $geoScopeSync
    ) {}

    public function index(): View
    {
        $employees = Admin::query()
            ->where('role', 'employee')
            ->with('geoScopes')
            ->withCount('permissions')
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        return view('admin.settings.employees.index', compact('employees'));
    }

    public function create(): View
    {
        $permissionMatrix = $this->emptyMatrix();

        return view('admin.settings.employees.create', compact('permissionMatrix'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $admin = Admin::query()->create([
            'name' => $data['name'],
            'login_id' => $data['login_id'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'password_plain' => $data['password'],
            'role' => 'employee',
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->permissionSync->sync($admin, $request->input('permissions', []));
        $this->syncGeoScope($admin, $request);

        return redirect()
            ->route('admin.settings.employees.index')
            ->with('success', 'Staff member created.');
    }

    public function edit(Admin $admin): View
    {
        $this->ensureEmployee($admin);

        $permissionMatrix = $this->permissionSync->matrixForAdmin($admin);

        return view('admin.settings.employees.edit', compact('admin', 'permissionMatrix'));
    }

    public function update(UpdateEmployeeRequest $request, Admin $admin): RedirectResponse
    {
        $this->ensureEmployee($admin);

        $data = $request->validated();

        $admin->fill([
            'name' => $data['name'],
            'login_id' => $data['login_id'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($data['password'])) {
            $admin->password = $data['password'];
            $admin->password_plain = $data['password'];
        }

        $admin->save();

        $this->permissionSync->sync($admin, $request->input('permissions', []));
        $this->syncGeoScope($admin, $request);

        return redirect()
            ->route('admin.settings.employees.index')
            ->with('success', 'Staff member updated.');
    }

    public function toggleActive(Admin $admin): RedirectResponse
    {
        $this->ensureEmployee($admin);

        if (auth('admin')->id() === $admin->id) {
            return back()->with('error', 'You cannot change your own active status here.');
        }

        $admin->update(['is_active' => ! $admin->is_active]);

        return back()->with('success', $admin->is_active ? 'Staff member activated.' : 'Staff member deactivated.');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        $this->ensureEmployee($admin);

        if (auth('admin')->id() === $admin->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()
            ->route('admin.settings.employees.index')
            ->with('success', 'Staff member removed.');
    }

    protected function ensureEmployee(Admin $admin): void
    {
        if (! $admin->isEmployee()) {
            abort(404);
        }
    }

    protected function syncGeoScope(Admin $admin, StoreEmployeeRequest|UpdateEmployeeRequest $request): void
    {
        $allRegions = $request->input('region_access', 'all') !== 'limited';
        $rows = $request->input('geo_scopes', []);
        if (! is_array($rows)) {
            $rows = [];
        }

        $this->geoScopeSync->sync($admin, $allRegions, $rows);
    }

    /**
     * @return array<string, array{view: bool, edit: bool, delete: bool}>
     */
    protected function emptyMatrix(): array
    {
        $matrix = [];
        foreach ($this->permissionSync->allModuleKeys() as $module) {
            $matrix[$module] = ['view' => false, 'edit' => false, 'delete' => false];
        }

        return $matrix;
    }
}
