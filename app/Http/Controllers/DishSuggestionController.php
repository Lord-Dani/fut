<?php

namespace App\Http\Controllers;

use App\Models\DishSuggestion;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DishSuggestionController extends Controller
{
    // Форма предложения блюда (сотрудник)
    public function create()
    {
        return view('suggestions.create');
    }

    // Сохранение предложения (сотрудник)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'calories' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'restaurant' => 'nullable|string|max:255',
            'justification' => 'required|string|min:10',
        ]);

        DishSuggestion::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'company_id' => Auth::user()->company_id,
        ]));

        return redirect()->route('suggestions.my')
            ->with('success', 'Предложение отправлено на модерацию!');
    }

    // Мои предложения (сотрудник)
    public function mySuggestions()
    {
        $suggestions = Auth::user()->dishSuggestions()
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('suggestions.my', compact('suggestions'));
    }

    // Модерация предложений (админ)
    public function adminIndex()
    {
        $suggestions = DishSuggestion::with(['user', 'company'])
                        ->pending()
                        ->orderBy('created_at', 'asc')
                        ->get();

        return view('admin.suggestions.index', compact('suggestions'));
    }

    // Одобрение предложения (админ)
    public function approve(Request $request, DishSuggestion $suggestion)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        // Создаем блюдо на основе предложения
        Dish::create([
            'name' => $suggestion->name,
            'description' => $suggestion->description,
            'category' => $suggestion->category,
            'calories' => $suggestion->calories,
            'price' => $suggestion->price ?? 0,
            'restaurant' => $suggestion->restaurant,
            'company_id' => $suggestion->company_id,
            'user_id' => $suggestion->user_id,
            'is_global' => false,
        ]);

        $suggestion->approve(Auth::id(), $validated['admin_notes']);

        return back()->with('success', 'Предложение одобрено и добавлено в меню!');
    }

    // Отклонение предложения (админ)
    public function reject(Request $request, DishSuggestion $suggestion)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|min:5',
        ]);

        $suggestion->reject(Auth::id(), $validated['admin_notes']);

        return back()->with('success', 'Предложение отклонено!');
    }
}