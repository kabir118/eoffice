<?php

namespace App\Http\Controllers;

use App\Mail\SuggestionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SuggestionController extends Controller
{
    /**
     * Handle the suggestion / issue email submission.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

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
