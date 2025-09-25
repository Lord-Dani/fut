<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MealPlanController extends Controller
{
    // Планирование питания (сотрудник)
    public function index()
    {
        $user = Auth::user();
        
        // План на текущую неделю
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        
        $mealPlans = $user->mealPlans()
                    ->with('dish')
                    ->whereBetween('date', [$startOfWeek, $endOfWeek])
                    ->get()
                    ->groupBy('date');

        $dishes = Dish::forCompany($user->company_id)
                    ->active()
                    ->get();

        return view('meal-plan.index', compact('mealPlans', 'dishes'));
    }

    // Сохранение плана питания
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'dish_id' => 'required|exists:dishes,id',
        ]);

        // Удаляем существующий план на это время
        MealPlan::where('user_id', Auth::id())
                ->where('date', $validated['date'])
                ->where('meal_type', $validated['meal_type'])
                ->delete();

        // Создаем новый план
        MealPlan::create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));

        return back()->with('success', 'План питания обновлен!');
    }

    // Удаление из плана
    public function destroy(MealPlan $mealPlan)
    {
        $this->authorize('delete', $mealPlan);
        
        $mealPlan->delete();

        return back()->with('success', 'План питания удален!');
    }

    // Экспорт в календарь
    public function exportCalendar()
    {
        $user = Auth::user();
        $mealPlans = $user->mealPlans()
                    ->with('dish')
                    ->where('date', '>=', now())
                    ->get();

        // Генерируем .ics файл для календаря
        $vCalendar = new \Eluceo\iCal\Domain\Entity\Calendar();
        
        foreach ($mealPlans as $plan) {
            $vEvent = new \Eluceo\iCal\Domain\Entity\Event();
            $vEvent->setSummary('Обед: ' . $plan->dish->name);
            $vEvent->setDescription($plan->dish->description);
            
            $occurrence = new \Eluceo\iCal\Domain\ValueObject\DateTime(
                \Eluceo\iCal\Domain\ValueObject\DateTime::createFromDateTime(
                    $plan->date->setTime(13, 0) // Обед в 13:00
                )
            );
            $vEvent->setOccurrence($occurrence);
            
            $vCalendar->addComponent($vEvent);
        }

        // Возвращаем файл для скачивания
        $calendarComponent = new \Eluceo\iCal\Presentation\Factory\CalendarFactory();
        $calendarString = $calendarComponent->createCalendar($vCalendar);

        return response($calendarString)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="meal-plan.ics"');
    }
}