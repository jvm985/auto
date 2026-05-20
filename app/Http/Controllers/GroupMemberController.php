<?php

namespace App\Http\Controllers;

use App\Models\CarSharingGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GroupMemberController extends Controller
{
    public function index(Request $request, CarSharingGroup $group): View
    {
        $this->ensureMember($request, $group);

        $members = $group->users()
            ->orderByDesc('car_sharing_group_user.is_admin')
            ->orderBy('name')
            ->get();

        return view('groups.members', [
            'group' => $group,
            'members' => $members,
            'isAdmin' => $request->user()->isAdminOf($group),
        ]);
    }

    public function store(Request $request, CarSharingGroup $group): RedirectResponse
    {
        $this->ensureAdmin($request, $group);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $email = strtolower(trim($data['email']));

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => ($data['name'] ?? null) ?: explode('@', $email)[0]],
        );

        if ($group->users()->whereKey($user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Dit emailadres zit al in de groep.',
            ]);
        }

        $group->users()->attach($user->id, [
            'is_admin' => (bool) ($data['is_admin'] ?? false),
        ]);

        return redirect()
            ->route('groups.members.index', $group)
            ->with('status', "{$user->email} is toegevoegd aan de groep.");
    }

    public function update(Request $request, CarSharingGroup $group, User $user): RedirectResponse
    {
        $this->ensureAdmin($request, $group);

        $data = $request->validate([
            'is_admin' => ['required', 'boolean'],
        ]);

        abort_unless($group->users()->whereKey($user->id)->exists(), 404);

        // Prevent removing the last admin.
        if (! $data['is_admin']
            && $user->isAdminOf($group)
            && $group->admins()->count() <= 1) {
            throw ValidationException::withMessages([
                'is_admin' => 'Een groep moet minstens één beheerder hebben.',
            ]);
        }

        $group->users()->updateExistingPivot($user->id, ['is_admin' => (bool) $data['is_admin']]);

        return redirect()
            ->route('groups.members.index', $group)
            ->with('status', $data['is_admin']
                ? "{$user->email} is nu beheerder."
                : "{$user->email} is niet langer beheerder.");
    }

    public function destroy(Request $request, CarSharingGroup $group, User $user): RedirectResponse
    {
        $this->ensureAdmin($request, $group);

        abort_unless($group->users()->whereKey($user->id)->exists(), 404);

        if ($user->isAdminOf($group) && $group->admins()->count() <= 1) {
            throw ValidationException::withMessages([
                'user' => 'Je kan de laatste beheerder niet verwijderen.',
            ]);
        }

        $group->users()->detach($user->id);

        return redirect()
            ->route('groups.members.index', $group)
            ->with('status', "{$user->email} is uit de groep verwijderd.");
    }

    private function ensureMember(Request $request, CarSharingGroup $group): void
    {
        abort_unless($group->users()->whereKey($request->user()->id)->exists(), 403);
    }

    private function ensureAdmin(Request $request, CarSharingGroup $group): void
    {
        abort_unless($request->user()->isAdminOf($group), 403);
    }
}
