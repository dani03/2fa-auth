<?php

namespace App\Http\Controllers;

use App\Notifications\SendTwoFactorCode;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Nette\Schema\ValidationException;

class TwoFactorController extends Controller
{
    public function index(): View {
        return view('auth.twoFactor');
    }

    public function store(Request $request): ValidationException|RedirectResponse {
        $request->validate([
            'two_factor_code' => ['integer', 'required'],
        ]);
        $user = auth()->user();
        if ($request->input('two_factor_code') !== $user->two_factor_code) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'two_factor_code' => __('le code est incorrect'),
            ]);
        }

        $user->resetTwoFactorCode();
        return redirect()->to(RouteServiceProvider::HOME);

    }

    public function resend(): RedirectResponse
    {
        $user = auth()->user();
        if(!$user) return redirect()->back()->with('error',__('user nexiste pas'));
        $user->generateTwoFactorCode();
        $user->notify(new SendTwoFactorCode());

        return redirect()->back()->withStatus(__('The two factor code has been sent again'));
    }
}
