<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        switch ($user->role) {
            case 'admin':
                return $this->adminDashboard();
            case 'manager':
                return $this->managerDashboard();
            default:
                return $this->employeeDashboard();
        }
    }

    private function employeeDashboard()
    {
        $user = Auth::user();
        return view('dashboard.employee', compact('user'));
    }

    private function managerDashboard()
    {
        $user = Auth::user();
        $company = $user->company;
        return view('dashboard.manager', compact('user', 'company'));
    }

    private function adminDashboard()
    {
        return view('dashboard.admin');
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function updateSettings(Request $request)
    {
        return back()->with('success', 'Настройки обновлены!');
    }
}