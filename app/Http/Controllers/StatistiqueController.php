<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\departements;

class StatistiqueController extends Controller
{
    // public function EmployeePlusAbsent()
    // {
    //     $user = User::where('role','employee')
    //         ->orderByDesc('jours_absence')
    //         ->first();
    //     return response()->json($user);
    // }
    public function EmployeePlusPresentRH(){
        $user =  User::whereIn('role', ['employee', 'ChefProjet'])
            ->orderByDesc('jours_presence')
            ->first();

        return response()->json($user);
    }
     public function EmployeePlusPresentAdmin(){
        $user = User::whereIn('role', ['employee', 'RH', 'ChefProjet'])
            ->orderByDesc('jours_presence')
            ->first();

        return response()->json($user);
    }
     public function CountDepartement(){
        $count = departements::count();
        return response()->json(['total_departements' => $count]);
    }
    public function CountEmployeeRH(){
        $count = User::whereIn('role', ['employee', 'ChefProjet'])->count();
        return response()->json(['total_employees' => $count]);
    }
     public function CountEmployeeAdmin(){
        $count = User::whereIn('role', ['employee', 'RH', 'ChefProjet'])->count();
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
        'total' => $total
    ]);
}
public function EmployeesPresentTodayAdmin()
{
    $today = date('Y-m-d');
    $present = User::whereIn('role', ['employee', 'RH', 'ChefProjet'])
        ->whereDate('derniere_presence', $today)
        ->count();
    $total = User::whereIn('role', ['employee', 'RH', 'ChefProjet'])->count();

    return response()->json([
        'present' => $present,
        'total' => $total
    ]);
}
public function EmployeesAbsenceTodayRH()
{
    $today = date('Y-m-d');
    $present = User::whereIn('role', ['employee','ChefProjet'])
        ->whereDate('derniere_presence', $today)
        ->count();
    $total = User::whereIn('role', ['employee','ChefProjet'])->count();
    return response()->json([
        'present' => $present,
        'total' => $total
    ]);
}
public function EmployeesAbsenceTodayAdmin()
{
    $today = date('Y-m-d');
    $present =User::whereIn('role', ['employee', 'RH', 'ChefProjet'])
        ->whereDate('derniere_presence', $today)
        ->count();
    $total =User::whereIn('role', ['employee', 'RH', 'ChefProjet'])->count();
    return response()->json([
        'present' => $present,
        'total' => $total
    ]);
}
    public function EmployeesPresentListRH(){
        $today = date('Y-m-d');
            $users = User::whereIn('role', ['employee', 'ChefProjet'])->whereDate('derniere_presence', $today)->get(['id', 'nom', 'prenom', 'photo',]);

        return response()->json($users);
    }

    public function EmployeesPresentListAdmin(){
        $today = date('Y-m-d');
        $users =  $users = User::whereIn('role', ['employee', 'RH', 'ChefProjet'])->whereDate('derniere_presence', $today)->get(['id', 'nom', 'prenom', 'photo',]);

        return response()->json($users);
    }
    public function EmployeesAbsentTodayListAdmin() {
    $today = date('Y-m-d');

    $absents = User::whereIn('role', ['employee', 'ChefProjet', 'RH'])
        ->where(function($query) use ($today) {
            $query->whereNull('derniere_presence')
                  ->orWhereDate('derniere_presence', '!=', $today);
        })
        ->get(['id', 'nom', 'prenom', 'photo']);

    return response()->json($absents);
}
 public function EmployeesAbsentTodayListRH() {
    $today = date('Y-m-d');

    $absents = User::whereIn('role', ['employee', 'ChefProjet'])
        ->where(function($query) use ($today) {
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
        ->where(function($query) use ($today) {
            $query->whereNull('derniere_presence')
                  ->orWhereDate('derniere_presence', '!=', $today);})
        ->get(['id', 'nom', 'prenom', 'photo']);
    $total = User::whereIn('role', ['employee', 'ChefProjet'])->count();
    $countAbsent = $absents->count();
    return response()->json([
        'absent' => $countAbsent,
        'total' => $total,
        'employees' => $absents
    ]);
}
 public function EmployeesAbsentTodayAdmin()
{
    $today = date('Y-m-d');

    $absents =User::whereIn('role', ['employee', 'ChefProjet', 'RH'])
        ->where(function($query) use ($today) {
            $query->whereNull('derniere_presence')
                  ->orWhereDate('derniere_presence', '!=', $today);})
        ->get(['id', 'nom', 'prenom', 'photo']);
    $total =User::whereIn('role', ['employee', 'ChefProjet', 'RH'])->count();
    $countAbsent = $absents->count();
    return response()->json([
        'absent' => $countAbsent,
        'total' => $total,
        'employees' => $absents
    ]);
}
public function EmployeLePlusPresentMoisDernierAdmin()
{
    // Obtenir le premier et dernier jour du mois dernier
    $debutMoisDernier = now()->subMonth()->startOfMonth()->toDateString();
    $finMoisDernier = now()->subMonth()->endOfMonth()->toDateString();

    // Récupérer tous les employés
    $employes = User::whereIn('role', ['employee', 'ChefProjet', 'RH'])->get();

    $employeLePlusPresent = null;
    $maxPresences = -1;

    foreach ($employes as $employe) {
        // Compter les présences pendant le mois dernier
        $presences = User::where('id', $employe->id)
            ->whereDate('derniere_presence', '>=', $debutMoisDernier)
            ->whereDate('derniere_presence', '<=', $finMoisDernier)
            ->count();

        if ($presences > $maxPresences) {
            $maxPresences = $presences;
            $employeLePlusPresent = $employe;
        }
    }
    if ($employeLePlusPresent) {
            return response()->json([
                'nom' => $employeLePlusPresent->nom,
                'prenom' => $employeLePlusPresent->prenom,
                'photo' => $employeLePlusPresent->photo,
                'presences' => $maxPresences
            ]);
        }

        return response()->json(['message' => 'Aucun employé trouvé']);
    }
    public function EmployeLePlusPresentMoisDernierRH()
{
    // Obtenir le premier et dernier jour du mois dernier
    $debutMoisDernier = now()->subMonth()->startOfMonth()->toDateString();
    $finMoisDernier = now()->subMonth()->endOfMonth()->toDateString();

    // Récupérer tous les employés
    $employes =User:: whereIn('role', ['employee', 'ChefProjet'])->get();

    $employeLePlusPresent = null;
    $maxPresences = -1;

    foreach ($employes as $employe) {
        // Compter les présences pendant le mois dernier
        $presences = User::where('id', $employe->id)
            ->whereDate('derniere_presence', '>=', $debutMoisDernier)
            ->whereDate('derniere_presence', '<=', $finMoisDernier)
            ->count();

        if ($presences > $maxPresences) {
            $maxPresences = $presences;
            $employeLePlusPresent = $employe;
        }
    }
    if ($employeLePlusPresent) {
            return response()->json([
                'nom' => $employeLePlusPresent->nom,
                'prenom' => $employeLePlusPresent->prenom,
                'photo' => $employeLePlusPresent->photo,
                'presences' => $maxPresences
            ]);
        }

        return response()->json(['message' => 'Aucun employé trouvé']);
    }





}
