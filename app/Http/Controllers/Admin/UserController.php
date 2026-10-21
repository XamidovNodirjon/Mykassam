<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\AdminUserData;
use App\Enums\UserRole;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private UserService $users) {}

    /**
     * Every account (administrators, owners and sellers) with its shop, activity and status.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString() ?: null;
        $role = $request->enum('role', UserRole::class);

        return Inertia::render('Admin/Users/Index', [
            'users' => UserResource::collection($this->users->paginate($search, $role)),
            'summary' => $this->users->summary(),
            'filters' => ['search' => $search, 'role' => $role?->value],
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->users->update($user, AdminUserData::fromRequest($request));

        $this->toast("Foydalanuvchi ma'lumotlari saqlandi.");

        return back();
    }

    /**
     * @throws BusinessException
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->users->delete($user);

        $this->toast("Foydalanuvchi o'chirildi.");

        return back();
    }
}
