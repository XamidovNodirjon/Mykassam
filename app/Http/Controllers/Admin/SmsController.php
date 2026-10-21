<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SmsMessageResource;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SmsController extends Controller
{
    public function index(Request $request, SmsService $sms): Response
    {
        $search = $request->string('search')->replaceMatches('/\D+/', '')->toString() ?: null;

        return Inertia::render('Admin/Sms/Index', [
            'messages' => SmsMessageResource::collection($sms->paginate($search)),
            'summary' => $sms->summary(),
            // Calls the provider API, so it loads after the page renders.
            'balance' => Inertia::defer(fn (): array => $sms->balance()),
            'isLive' => config('sms.driver') === 'usms',
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Check with the provider which of the recent messages actually reached the phone.
     */
    public function sync(SmsService $sms): RedirectResponse
    {
        $changed = $sms->syncDeliveryReports();

        $this->toast($changed > 0 ? "{$changed} ta SMS holati yangilandi." : "Yangi holatlar yo'q.");

        return back();
    }
}
