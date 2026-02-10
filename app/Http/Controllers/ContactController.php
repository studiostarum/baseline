<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    /**
     * Show the contact page.
     */
    public function show(): Response
    {
        return Inertia::render('Contact');
    }

    /**
     * Handle the contact form submission.
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        $request->validated();

        return back()->with('status', __('website.contact.success'));
    }
}
