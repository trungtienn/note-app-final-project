<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PreferenceController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark'],
            'font_size' => ['required', 'in:small,base,large'],
            'default_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $user = $request->user();
        $user->preferences = $validated;
        $user->save();

        return back()->with('status', 'preferences-updated');
    }
}