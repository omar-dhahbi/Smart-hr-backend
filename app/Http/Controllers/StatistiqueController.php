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
    public function EmployeesAbsentTodayList() {
    $today = date('Y-m-d');

    $absents = User::where('role', 'employee')
        ->where(function($query) use ($today) {
            $query->whereNull('derniere_presence')
                  ->orWhereDate('derniere_presence', '!=', $today);
        })
        ->get(['id', 'nom', 'prenom', 'photo']);

    return response()->json($absents);
}
    public function EmployeesAbsentToday()
{
    $today = date('Y-m-d');

    $absents = User::where('role', 'employee')
        ->where(function($query) use ($today) {
            $query->whereNull('derniere_presence')
                  ->orWhereDate('derniere_presence', '!=', $today);})
        ->get(['id', 'nom', 'prenom', 'photo']);
    $total = User::where('role', 'employee')->count();
    $countAbsent = $absents->count();
    return response()->json([
        'absent' => $countAbsent,
        'total' => $total,
        'employees' => $absents
    ]);
}
    // Employé le plus absent du mois dernier

 // Employé le plus absent du mois dernier
// public function EmployeLePlusAbsentMoisDernier()
// {
//     // Obtenir le premier et dernier jour du mois dernier
//     $debutMoisDernier = now()->subMonth()->startOfMonth()->toDateString();
//     $finMoisDernier = now()->subMonth()->endOfMonth()->toDateString();

//     // Récupérer tous les employés
//     $employes = User::where('role', 'employee')->get();

//     $employeLePlusAbsent = null;
//     $maxAbsences = -1;

//     foreach ($employes as $employe) {
//         // Compter le nombre de jours du mois dernier où il n'a pas pointé
//         $joursAbsence = 0;

//         // Si derniere_presence est NULL ou hors du mois dernier, considérer absence
//         $presence = $employe->derniere_presence;

//         // On parcourt chaque jour du mois dernier
//         $periode = \Carbon\CarbonPeriod::create($debutMoisDernier, $finMoisDernier);

//         foreach ($periode as $jour) {
//             $jourStr = $jour->toDateString();
//             if ($presence !== $jourStr) {
//                 $joursAbsence++;
//             }
//         }

//         // Mettre à jour le champ jours_absence dans la base
//         $employe->jours_absence = $joursAbsence;
//         $employe->save();

//         // Vérifier si c'est l'employé le plus absent
//         if ($joursAbsence > $maxAbsences) {
//             $maxAbsences = $joursAbsence;
//             $employeLePlusAbsent = $employe;
//         }
//     }

//     if ($employeLePlusAbsent) {
//         return response()->json([
//             'nom' => $employeLePlusAbsent->nom,
//             'prenom' => $employeLePlusAbsent->prenom,
//             'photo' => $employeLePlusAbsent->photo,
//             'absences' => $maxAbsences
//         ]);
//     }

//     return response()->json(['message' => 'Aucun employé trouvé']);
// }


// Employé le plus présent du mois dernier
public function EmployeLePlusPresentMoisDernier()
{
    // Obtenir le premier et dernier jour du mois dernier
    $debutMoisDernier = now()->subMonth()->startOfMonth()->toDateString();
    $finMoisDernier = now()->subMonth()->endOfMonth()->toDateString();

    // Récupérer tous les employés
    $employes = User::where('role', 'employee')->get();

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
