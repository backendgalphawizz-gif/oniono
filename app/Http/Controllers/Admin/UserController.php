<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->trim();
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->status === 'active', fn ($q) => $q->where('is_blocked', false)->where('is_suspended', false))
            ->when($request->status === 'blocked', fn ($q) => $q->where('is_blocked', true))
            ->when($request->status === 'suspended', fn ($q) => $q->where('is_suspended', true))
            ->when($request->verification === 'verified', fn ($q) => $q->where('is_verified', true))
            ->when($request->verification === 'unverified', fn ($q) => $q->where('is_verified', false))
            ->withCount('orders')
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);
        $data['is_verified'] = $request->boolean('is_verified');

        User::query()->create($data);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function show(User $user)
    {
        $user->load(['addresses']);

        $orders = $user->orders()
            ->with('restaurant')
            ->latest()
            ->limit(25)
            ->get();

        $payments = Payment::query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->with(['order' => fn ($q) => $q->select('id', 'order_number', 'user_id')])
            ->latest()
            ->limit(25)
            ->get();

        $complaints = SupportTicket::query()
            ->where('creator_type', 'user')
            ->where('creator_id', $user->id)
            ->latest()
            ->limit(25)
            ->get();

        return view('admin.users.show', compact('user', 'orders', 'payments', 'complaints'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, updating: true, user: $user);
        $data['is_verified'] = $request->boolean('is_verified');

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function block(User $user)
    {
        $user->update(['is_blocked' => true]);

        return back()->with('success', 'User blocked.');
    }

    public function unblock(User $user)
    {
        $user->update(['is_blocked' => false]);

        return back()->with('success', 'User unblocked.');
    }

    public function suspend(User $user)
    {
        $user->update(['is_suspended' => true]);

        return back()->with('success', 'User suspended.');
    }

    public function unsuspend(User $user)
    {
        $user->update(['is_suspended' => false]);

        return back()->with('success', 'User suspension lifted.');
    }

    public function verify(User $user)
    {
        $user->update([
            'is_verified' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        return back()->with('success', 'User account verified.');
    }

    public function unverify(User $user)
    {
        $user->update([
            'is_verified' => false,
            'email_verified_at' => null,
        ]);

        return back()->with('success', 'User verification removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $updating = false, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [$updating ? 'nullable' : 'required', 'string', 'min:8'],
            'is_verified' => ['nullable', 'boolean'],
        ]);
    }
}
