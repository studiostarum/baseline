<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactFormSubmitted;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    /**
     * Show the contact page.
     */
    public function show(Request $request): Response
    {
        return Inertia::render('Contact', [
            'status' => $request->session()->get('status'),
            'error' => $request->session()->get('error'),
        ]);
    }

    /**
     * Handle the contact form submission.
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $to = Setting::get('contact_email');
        if (empty($to)) {
            return back()->with('error', __('website.contact.not_configured'));
        }

        Mail::to($to)->send(new ContactFormSubmitted($data));

        return back()->with('status', __('website.contact.success'));
    }
}
