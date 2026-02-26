<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\departements;

class StatistiqueController extends Controller
{
    public function EmployeePlusAbsent()
    {
        $user = User::where('role','employee')
            ->orderByDesc('jours_absence')
            ->first();
        return response()->json($user);
    }
    public function EmployeePlusPresent(){
        $user = User::where('role','employee')
            ->orderByDesc('jours_presence')
            ->first();

        return response()->json($user);
    }
     public function CountDepartement(){
        $count = departements::count();
        return response()->json(['total_departements' => $count]);
    }
    public function CountEmployee(){
        $count = User::where('role', 'employee')->count();
        return response()->json(['total_employees' => $count]);
    }
public function EmployeesPresentToday()
{
    $today = date('Y-m-d');
    $present = User::where('role', 'employee')
        ->whereDate('derniere_presence', $today)
        ->count();
    $total = User::where('role', 'employee')->count();

    return response()->json([
        'present' => $present,
        'total' => $total
    ]);
}
public function EmployeesAbsenceToday()
{
    $today = date('Y-m-d');
    $present = User::where('role', 'employee')
        ->whereDate('derniere_presence', $today)
        ->count();
    $total = User::where('role', 'employee')->count();
    return response()->json([
        'present' => $present,
        'total' => $total
    ]);
}
    public function EmployeesPresentList(){
        $today = date('Y-m-d');
        $users = User::where('role', 'employee')->whereDate('derniere_presence', $today)->get(['id', 'nom', 'prenom', 'photo',]);

        return response()->json($users);
    }

    public function EmployeesAbsentToday()
{
    $today = date('Y-m-d');

    $absents = User::where('role', 'employee')
        ->where(function($query) use ($today) {
            $query->whereNull('derniere_presence')
                  ->orWhereDate('derniere_presence', '!=', $today);
        })
        ->get(['id', 'nom', 'prenom', 'photo']);
    $total = User::where('role', 'employee')->count();
    $countAbsent = $absents->count();

    return response()->json([
        'absent' => $countAbsent,
        'total' => $total,
        'employees' => $absents
    ]);
}

public function EmployeePlusAbsentByMonth($month, $year)
{
    // Tous les employés
    $employees = User::where('role', 'employee')->get();

    // On map chaque employé pour calculer le nombre de jours d'absence dans le mois donné
    $employeesAbsence = $employees->map(function ($user) use ($month, $year) {
        $totalDays = User::where('id', $user->id)
            ->whereYear('derniere_presence', $year)
            ->whereMonth('derniere_presence', $month)
            ->count(); // Nombre de jours présents

        // On calcule le nombre de jours d'absence pour le mois
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $absenceDays = $daysInMonth - $totalDays;

        return [
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'photo' => $user->photo,
            'jours_absence_mois' => $absenceDays,
        ];
    });

    // On récupère celui qui a le plus d'absences
    $mostAbsent = $employeesAbsence->sortByDesc('jours_absence_mois')->first();

    return response()->json($mostAbsent);
}

public function EmployeePlusPresentByMonth($month, $year)
{
    // Tous les employés
    $employees = User::where('role', 'employee')->get();

    // On map chaque employé pour calculer le nombre de jours de présence dans le mois donné
    $employeesPresence = $employees->map(function ($user) use ($month, $year) {
        $presenceDays = User::where('id', $user->id)
            ->whereYear('derniere_presence', $year)
            ->whereMonth('derniere_presence', $month)
            ->count();

        return [
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'photo' => $user->photo,
            'jours_presence_mois' => $presenceDays,
        ];
    });

    // On récupère celui qui a le plus de présences
    $mostPresent = $employeesPresence->sortByDesc('jours_presence_mois')->first();

    return response()->json($mostPresent);
}





}
