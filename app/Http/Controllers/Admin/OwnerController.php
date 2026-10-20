<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OwnerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class OwnerController extends Controller
{
    public function __construct(private OwnerService $owners) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString() ?: null;

        return Inertia::render('Admin/Owners/Index', [
            'owners' => UserResource::collection($this->owners->paginate($search)),
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Block or unblock an owner.
     */
    public function toggleStatus(User $owner): RedirectResponse
    {
        abort_unless($owner->isOwner(), HttpResponse::HTTP_NOT_FOUND);

        $owner = $this->owners->toggleStatus($owner);

        $this->toast($owner->is_active ? 'Egasi faollashtirildi.' : 'Egasi bloklandi.');

        return back();
    }
}
