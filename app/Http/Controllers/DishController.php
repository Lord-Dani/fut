<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DishController extends Controller
{
    // Список блюд для сотрудника
    public function index()
    {
        $user = Auth::user();
        $dishes = Dish::forCompany($user->company_id)
                    ->active()
                    ->with('company')
                    ->get();

        return view('dishes.index', compact('dishes'));
    }

    // Личные блюда сотрудника
    public function myDishes()
    {
        $dishes = Auth::user()->dishes()->active()->get();
        return view('dishes.my', compact('dishes'));
    }

    // Список блюд для менеджера
    public function managerIndex()
    {
        $user = Auth::user();
        $dishes = Dish::forCompany($user->company_id)
                    ->with('user')
                    ->get();

        return view('manager.dishes.index', compact('dishes'));
    }

    // Список блюд для администратора
    public function adminIndex()
    {
        $dishes = Dish::with(['company', 'user'])->get();
        return view('admin.dishes.index', compact('dishes'));
    }

    // Создание личного блюда
    public function storePersonal(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'calories' => 'nullable|integer',
            'price' => 'required|numeric',
            'restaurant' => 'nullable|string',
        ]);

        Auth::user()->dishes()->create($validated);

        return redirect()->route('dishes.my')->with('success', 'Блюдо добавлено!');
    }

    // Создание блюда для компании (менеджер)
    public function storeCompany(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'calories' => 'nullable|integer',
            'price' => 'required|numeric',
            'allergens' => 'nullable|array',
        ]);

        Dish::create(array_merge($validated, [
            'company_id' => Auth::user()->company_id,
            'user_id' => Auth::user()->id,
        ]));

        return redirect()->route('manager.dishes.index')->with('success', 'Блюдо добавлено в меню компании!');
    }

    public function show(Dish $dish)
    {
        return view('dishes.show', compact('dish'));
    }

    public function update(Request $request, Dish $dish)
    {
        // Проверка прав доступа
        $this->authorize('update', $dish);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'is_active' => 'boolean',
        ]);

        $dish->update($validated);

        return back()->with('success', 'Блюдо обновлено!');
    }

    public function destroy(Dish $dish)
    {
        $this->authorize('delete', $dish);
        $dish->delete();

        return back()->with('success', 'Блюдо удалено!');
    }
}