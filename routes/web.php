<?php

use App\Http\Controllers\{
    DashboardController,
    DishController,
    OrderController,
    CompanyController,
    ProfileController,
    DishSuggestionController,
    MealPlanController
};
use Illuminate\Support\Facades\Route;

// ==================== PUBLIC ROUTES ====================
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ==================== BREEZE AUTH ROUTES ====================
Route::get('/dashboard', function () {
    return redirect()->route('dashboard'); // Перенаправляем на наш кастомный дашборд
})->middleware(['auth', 'verified']);

// ==================== PROTECTED ROUTES ====================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // ==================== MAIN DASHBOARD ====================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ==================== PROFILE ROUTES ====================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/integrations', [ProfileController::class, 'integrations'])->name('profile.integrations');
    Route::post('/profile/telegram', [ProfileController::class, 'connectTelegram'])->name('profile.connect-telegram');
    
    // ==================== COMMON ROUTES (для всех авторизованных) ====================
    Route::get('/dishes', [DishController::class, 'index'])->name('dishes.index');
    Route::get('/dishes/{dish}', [DishController::class, 'show'])->name('dishes.show');
    Route::get('/order-history', [OrderController::class, 'history'])->name('orders.history');
    
    // ==================== EMPLOYEE ROUTES ====================
    Route::middleware(['role:employee'])->group(function () {
        // Meal Planning
        Route::get('/planning', [MealPlanController::class, 'index'])->name('planning.index');
        Route::post('/planning', [MealPlanController::class, 'store'])->name('planning.store');
        Route::delete('/planning/{mealPlan}', [MealPlanController::class, 'destroy'])->name('planning.destroy');
        
        // Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        
        // Personal Dishes
        Route::get('/my-dishes', [DishController::class, 'myDishes'])->name('dishes.my');
        Route::post('/my-dishes', [DishController::class, 'storePersonal'])->name('dishes.store-personal');
        
        // Dish Suggestions
        Route::get('/suggestions/create', [DishSuggestionController::class, 'create'])->name('suggestions.create');
        Route::post('/suggestions', [DishSuggestionController::class, 'store'])->name('suggestions.store');
        Route::get('/my-suggestions', [DishSuggestionController::class, 'mySuggestions'])->name('suggestions.my');
    });
    
    // ==================== MANAGER ROUTES ====================
    Route::middleware(['role:manager'])->prefix('manager')->name('manager.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'manager'])->name('dashboard');
        
        // Company Management
        Route::get('/company', [CompanyController::class, 'edit'])->name('company.edit');
        Route::put('/company', [CompanyController::class, 'update'])->name('company.update');
        
        // Employees Management
        Route::get('/employees', [CompanyController::class, 'employees'])->name('employees.index');
        Route::post('/employees', [CompanyController::class, 'addEmployee'])->name('employees.store');
        Route::delete('/employees/{user}', [CompanyController::class, 'removeEmployee'])->name('employees.destroy');
        
        // Orders Management
        Route::get('/orders', [OrderController::class, 'managerIndex'])->name('orders.index');
        Route::patch('/orders/{order}/status', [OrderController::class, 'managerUpdateStatus'])->name('orders.update-status');
        Route::patch('/orders/bulk-status', [OrderController::class, 'bulkUpdateStatus'])->name('orders.bulk-update');
        
        // Dishes Management
        Route::get('/dishes', [DishController::class, 'managerIndex'])->name('dishes.index');
        Route::post('/dishes', [DishController::class, 'storeCompany'])->name('dishes.store');
        Route::put('/dishes/{dish}', [DishController::class, 'update'])->name('dishes.update');
        Route::delete('/dishes/{dish}', [DishController::class, 'destroy'])->name('dishes.destroy');
        
        // Reports
        Route::get('/reports', [CompanyController::class, 'reports'])->name('reports');
    });
    
    // ==================== ADMIN ROUTES ====================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        
        // Companies Management
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::put('/companies/{company}', [CompanyController::class, 'adminUpdate'])->name('companies.update');
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
        
        // Global Dishes Management
        Route::get('/dishes', [DishController::class, 'adminIndex'])->name('dishes.index');
        Route::post('/dishes/global', [DishController::class, 'storeGlobal'])->name('dishes.store-global');
        Route::patch('/dishes/{dish}/toggle', [DishController::class, 'toggleActive'])->name('dishes.toggle');
        
        // Dish Suggestions Moderation
        Route::get('/suggestions', [DishSuggestionController::class, 'adminIndex'])->name('suggestions.index');
        Route::patch('/suggestions/{suggestion}/approve', [DishSuggestionController::class, 'approve'])->name('suggestions.approve');
        Route::patch('/suggestions/{suggestion}/reject', [DishSuggestionController::class, 'reject'])->name('suggestions.reject');
        
        // System Settings
        Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
        Route::put('/settings', [DashboardController::class, 'updateSettings'])->name('settings.update');
    });
});

// ==================== API ROUTES ====================
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/dishes/search', [DishController::class, 'search'])->name('api.dishes.search');
    Route::get('/calendar/export', [MealPlanController::class, 'exportCalendar'])->name('api.calendar.export');
});

// ==================== BREEZE AUTH FILE ====================
require __DIR__.'/auth.php';