<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    // Список компаний (админ)
    public function index()
    {
        $companies = Company::withCount('users')->get();
        return view('admin.companies.index', compact('companies'));
    }

    // Форма редактирования компании (менеджер)
    public function edit()
    {
        $company = Auth::user()->company;
        return view('manager.company.edit', compact('company'));
    }

    // Обновление компании (менеджер)
    public function update(Request $request)
    {
        $company = Auth::user()->company;
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nutrition_budget' => 'required|numeric|min:0',
            'employee_limit' => 'required|numeric|min:1',
            'order_deadline' => 'required|date_format:H:i',
        ]);

        $company->update($validated);

        return back()->with('success', 'Данные компании обновлены!');
    }

    // Создание компании (админ)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies',
            'nutrition_budget' => 'required|numeric|min:0',
            'employee_limit' => 'required|numeric|min:1',
        ]);

        $company = Company::create(array_merge($validated, [
            'slug' => Str::slug($validated['name'])
        ]));

        return back()->with('success', 'Компания создана!');
    }

    // Обновление компании (админ)
    public function adminUpdate(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nutrition_budget' => 'required|numeric|min:0',
            'employee_limit' => 'required|numeric|min:1',
            'order_deadline' => 'required|date_format:H:i',
        ]);

        $company->update($validated);

        return back()->with('success', 'Компания обновлена!');
    }

    // Удаление компании (админ)
    public function destroy(Company $company)
    {
        $company->delete();
        return back()->with('success', 'Компания удалена!');
    }

    // Сотрудники компании (менеджер)
    public function employees()
    {
        $company = Auth::user()->company;
        $employees = $company->users()->employees()->get();
        
        return view('manager.employees.index', compact('employees', 'company'));
    }

    // Добавление сотрудника (менеджер)
    public function addEmployee(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->first();
        
        if ($user->company_id) {
            return back()->with('error', 'Пользователь уже состоит в другой компании!');
        }

        $user->update(['company_id' => Auth::user()->company_id]);

        return back()->with('success', 'Сотрудник добавлен в компанию!');
    }

    // Удаление сотрудника (менеджер)
    public function removeEmployee(User $user)
    {
        if ($user->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $user->update(['company_id' => null]);

        return back()->with('success', 'Сотрудник удален из компании!');
    }

    // Отчеты (менеджер)
    public function reports()
    {
        $company = Auth::user()->company;
        
        $reportData = [
            'total_employees' => $company->getActiveEmployeesCount(),
            'active_orders' => $company->orders()->where('status', '!=', 'cancelled')->count(),
            'monthly_budget' => $company->nutrition_budget,
            'used_budget' => $company->orders()
                ->whereMonth('planned_for', now()->month)
                ->sum('total_amount'),
        ];

        return view('manager.reports.index', compact('reportData', 'company'));
    }
}