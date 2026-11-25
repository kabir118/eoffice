<?php

namespace App\Http\Controllers;

use App\Mail\SuggestionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SuggestionController extends Controller
{
    public function show()
    {
        return view('suggestions.index');
    }

    /**
     * Handle the suggestion / issue email submission.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string'],
        ]);

        // Verify that message contains either text or embedded image(s)
        if (empty(trim(strip_tags($validated['message'], '<img>')))) {
            return redirect()->back()
                ->withErrors(['message' => 'Please provide message details or attach an image.'])
                ->withInput();
        }

        $user = Auth::user();

        $mailable = new SuggestionMail(
            senderName:        $user->name,
            senderEmail:       $user->email,
            senderDesignation: $user->designation?->title ?? '',
            senderDepartment:  $user->department?->name ?? '',
            subject:           $validated['subject'],
            messageBody:       $validated['message'],
        );

        Mail::to('kabirbangash118@ustb.edu.pk')->send($mailable);

        return redirect()->back()->with('success', 'Your suggestion / issue has been sent successfully. We will get back to you shortly.');
    }
}
