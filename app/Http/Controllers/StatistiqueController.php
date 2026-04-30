<?php

namespace App\Http\Controllers;

use App\Models\User;

class StatistiqueController extends Controller
{
    public function EmployeePlusPresentRH()
    {
        $user = User::whereIn('role', ['employee', 'ChefProjet'])
            ->orderByDesc('jours_presence')
            ->first();

        return response()->json($user);
    }

    public function EmployeePlusPresentAdmin()
    {
        $user = User::whereIn('role', ['employee', 'RH', 'ChefProjet'])
            ->orderByDesc('jours_presence')
            ->first();

        return response()->json($user);
    }

    public function CountEmployeeRH()
    {
        $count = User::whereIn('role', ['employee', 'ChefProjet'])->count();

        return response()->json(['total_employees' => $count]);
    }

    public function CountEmployeeAdmin()
    {
        $count = User::whereIn('role', ['employee', 'agentRh', 'ResponsableRh', 'chefProjet'])->count();

        return response()->json(['total_employees' => $count]);
    }

    public function CountEmployeeResponsableRH()
    {
        $count = User::whereIn('role', ['employee', 'agentRh', 'chefProjet'])->count();

        return response()->json(['total_employees' => $count]);
    }

    public function EmployeesPresentTodayRH()
    {
        $today = date('Y-m-d');
        $present = User::whereIn('role', ['employee', 'ChefProjet'])
            ->whereDate('derniere_presence', $today)
            ->count();
        $total = User::whereIn('role', ['employee', 'ChefProjet'])->count();

        return response()->json([
            'present' => $present,
            'total' => $total,
        ]);
    }

    public function EmployeesPresentToday()
    {
        $today = date('Y-m-d');
        $present = User::whereIn('role', ['employee', 'agentRh', 'chefProjet'])
            ->whereDate('derniere_presence', $today)
            ->count();
        $total = User::whereIn('role', ['employee', 'agentRh', 'chefProjet'])->count();

        return response()->json([
            'present' => $present,
            'total' => $total,
        ]);
    }

    public function EmployeesAbsenceTodayRH()
    {
        $today = date('Y-m-d');
        $present = User::whereIn('role', ['employee', 'chefProjet'])
            ->whereDate('derniere_presence', $today)
            ->count();
        $total = User::whereIn('role', ['employee', 'chefProjet'])->count();

        return response()->json([
            'present' => $present,
            'total' => $total,
        ]);
    }

    public function EmployeesAbsenceToday()
    {
        $today = date('Y-m-d');
        $present = User::whereIn('role', ['employee', 'agentRh', 'chefProjet'])
            ->whereDate('derniere_presence', $today)
            ->count();
        $total = User::whereIn('role', ['employee', 'agentRh', 'chefProjet'])->count();

        return response()->json([
            'present' => $present,
            'total' => $total,
        ]);
    }

    public function EmployeesPresentListRH()
    {
        $today = date('Y-m-d');
        $users = User::whereIn('role', ['employee', 'ChefProjet'])->whereDate('derniere_presence', $today)->get(['id', 'nom', 'prenom', 'photo', 'session_ouverte', 'session_fermee']);

        return response()->json($users);
    }

    public function EmployeesPresentList()
    {
        $today = date('Y-m-d');
        $users = $users = User::whereIn('role', ['employee', 'agentRh', 'chefProjet'])->whereDate('derniere_presence', $today)->get(['id', 'nom', 'prenom', 'photo', 'session_ouverte', 'session_fermee']);

        return response()->json($users);
    }

    public function EmployeesAbsentTodayList()
    {
        $today = date('Y-m-d');

        $absents = User::whereIn('role', ['employee', 'agentRh', 'chefProjet'])
            ->where(function ($query) use ($today) {
                $query->whereNull('derniere_presence')
                    ->orWhereDate('derniere_presence', '!=', $today);
            })
            ->get(['id', 'nom', 'prenom', 'photo']);

        return response()->json($absents);
    }

    public function EmployeesAbsentTodayListRH()
    {
        $today = date('Y-m-d');

        $absents = User::whereIn('role', ['employee', 'ChefProjet'])
            ->where(function ($query) use ($today) {
                $query->whereNull('derniere_presence')
                    ->orWhereDate('derniere_presence', '!=', $today);
            })
            ->get(['id', 'nom', 'prenom', 'photo']);

        return response()->json($absents);
    }

    public function EmployeesAbsentTodayRH()
    {
        $today = date('Y-m-d');

        $absents = User::whereIn('role', ['employee', 'ChefProjet'])
            ->where(function ($query) use ($today) {
                $query->whereNull('derniere_presence')
                    ->orWhereDate('derniere_presence', '!=', $today);
            })
            ->get(['id', 'nom', 'prenom', 'photo']);
        $total = User::whereIn('role', ['employee', 'ChefProjet'])->count();
        $countAbsent = $absents->count();

        return response()->json([
            'absent' => $countAbsent,
            'total' => $total,
            'employees' => $absents,
        ]);
    }

    public function EmployeesAbsentToday()
    {
        $today = date('Y-m-d');

        $absents = User::whereIn('role', ['employee', 'agentRh', 'chefProjet'])
            ->where(function ($query) use ($today) {
                $query->whereNull('derniere_presence')
                    ->orWhereDate('derniere_presence', '!=', $today);
            })
            ->get(['id', 'nom', 'prenom', 'photo']);
        $total = User::whereIn('role', ['employee', 'agentRh', 'chefProjet'])->count();
        $countAbsent = $absents->count();

        return response()->json([
            'absent' => $countAbsent,
            'total' => $total,
            'employees' => $absents,
        ]);
    }
}
