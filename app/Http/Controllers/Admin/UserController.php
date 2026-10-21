<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\UserService;
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
}
