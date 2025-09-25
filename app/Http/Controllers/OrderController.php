<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // Заказы сотрудника
    public function index()
    {
        $orders = Auth::user()->orders()
                    ->with('items.dish')
                    ->orderBy('planned_for', 'desc')
                    ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    // Все заказы компании (для менеджера)
    public function managerIndex()
    {
        $companyId = Auth::user()->company_id;
        $orders = Order::where('company_id', $companyId)
                    ->with(['user', 'items.dish'])
                    ->orderBy('planned_for', 'desc')
                    ->paginate(15);

        return view('manager.orders.index', compact('orders'));
    }

    // Создание заказа
    public function store(Request $request)
    {
        $validated = $request->validate([
            'planned_for' => 'required|date',
            'planned_time' => 'required|date_format:H:i',
            'dish_id' => 'required|exists:dishes,id',
            'notes' => 'nullable|string',
        ]);

        $dish = Dish::findOrFail($validated['dish_id']);

        $order = Order::create([
            'planned_for' => $validated['planned_for'],
            'planned_time' => $validated['planned_time'],
            'user_id' => Auth::id(),
            'company_id' => Auth::user()->company_id,
            'notes' => $validated['notes'],
        ]);

        $order->items()->create([
            'dish_id' => $dish->id,
            'unit_price' => $dish->price,
            'quantity' => 1,
        ]);

        $order->updateTotalAmount();

        return redirect()->route('orders.index')->with('success', 'Заказ создан!');
    }

    // Обновление статуса (сотрудник)
    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => 'required|in:ordered,delivered,received,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Статус обновлен!');
    }

    // Обновление статуса (менеджер)
    public function managerUpdateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:ordered,delivered,received,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Статус заказа обновлен!');
    }

    // Массовое обновление статусов
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:ordered,delivered,received,cancelled',
        ]);

        Order::whereIn('id', $validated['order_ids'])
            ->update(['status' => $validated['status']]);

        return back()->with('success', 'Статусы обновлены!');
    }
}