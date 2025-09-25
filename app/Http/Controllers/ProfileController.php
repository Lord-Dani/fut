<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Редактирование профиля
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    // Обновление профиля
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'dietary_preferences' => 'nullable|array',
            'telegram_chat_id' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return back()->with('success', 'Профиль обновлен!');
    }

    // Настройки интеграций
    public function integrations()
    {
        $user = Auth::user();
        return view('profile.integrations', compact('user'));
    }

    // Подключение Telegram
    public function connectTelegram(Request $request)
    {
        $validated = $request->validate([
            'telegram_chat_id' => 'required|string|max:255',
        ]);

        Auth::user()->update(['telegram_chat_id' => $validated['telegram_chat_id']]);

        return back()->with('success', 'Telegram подключен!');
    }

    // Настройки уведомлений
    public function notifications()
    {
        return view('profile.notifications');
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'telegram_notifications' => 'boolean',
            'order_reminders' => 'boolean',
        ]);

        // Здесь можно сохранить настройки уведомлений
        // Например, в таблице user_settings

        return back()->with('success', 'Настройки уведомлений обновлены!');
    }
}