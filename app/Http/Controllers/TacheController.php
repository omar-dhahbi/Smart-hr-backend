<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\tache;
use App\Models\User;
use App\Models\departementTacheUser;

class TacheController extends Controller
{

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'Nom' => 'required',
            'Description' => 'required|min:10',
            'DateDebut' => 'required|date',
            'DateFin' => 'required|date',
            'departement_id' => 'required|array',
            'user_id' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 401);
        }

        $tache = new tache();

        $tache->Nom = $request->Nom;
        $tache->Description = $request->Description;
        $tache->DateDebut = $request->DateDebut;
        $tache->DateFin = $request->DateFin;
        $tache->status = $request->status ?? 'incomplet';

        $tache->save();


        foreach ($request->departement_id as $dep) {

            foreach ($request->user_id as $user) {

                $pivot = new departementTacheUser();

                $pivot->departement_id = $dep;
                $pivot->tache_id = $tache->id;
                $pivot->user_id = $user;

                $pivot->save();
            }
        }

        return response()->json([
            'message' => 'Tache created successfully'
        ]);
    }


    public function update(Request $request, $id)
    {

        $tache = tache::find($id);

        if (!$tache) {
            return response()->json(['message' => 'Tache not found'], 404);
        }

        $tache->Nom = $request->Nom;
        $tache->Description = $request->Description;
        $tache->DateDebut = $request->DateDebut;
        $tache->DateFin = $request->DateFin;
        $tache->status = $request->status;

        $tache->save();


        DB::table('departement_tache_users')
            ->where('tache_id', $id)
            ->delete();


        foreach ($request->departement_id as $dep) {

            foreach ($request->user_id as $user) {

                departementTacheUser::create([
                    'departement_id' => $dep,
                    'tache_id' => $id,
                    'user_id' => $user
                ]);
            }
        }

        return response()->json([
            'message' => 'Tache updated successfully'
        ]);
    }


    public function getDataById($id)
    {

        $data = DB::table('taches')

            ->join('departement_tache_users', 'taches.id', '=', 'departement_tache_users.tache_id')

            ->join('departements', 'departements.id', '=', 'departement_tache_users.departement_id')

            ->join('users', 'users.id', '=', 'departement_tache_users.user_id')

            ->select(
                'taches.id',
                'taches.Nom',
                'taches.Description',
                'taches.DateDebut',
                'taches.DateFin',
                'taches.status',
                'departements.NomDepartement',
                'users.nom',
                'users.prenom'
            )

            ->where('taches.id', $id)

            ->get();

        return response()->json($data);
    }


    public function destroy($id)
    {

        $tache = tache::find($id);

        if (!$tache) {
            return response()->json(['message' => 'Tache not found'], 404);
        }

        DB::table('departement_tache_users')
            ->where('tache_id', $id)
            ->delete();

        $tache->delete();

        return response()->json([
            'message' => 'Tache deleted successfully'
        ]);
    }

    public function index()
    {

        $taches = DB::table('taches')
            ->join('departement_tache_users', 'taches.id', '=', 'departement_tache_users.tache_id')
            ->join('departements', 'departements.id', '=', 'departement_tache_users.departement_id')
            ->join('users', 'users.id', '=', 'departement_tache_users.user_id')

            ->select(
                'taches.id',
                'taches.Nom',
                'taches.Description',
                'taches.DateDebut',
                'taches.DateFin',
                'taches.status',
                'departements.NomDepartement',
                'users.nom',
                'users.prenom'
            )
            ->orderBy('taches.id')

            ->get();


        $result = [];
        $currentTache = null;

        foreach ($taches as $tache) {

            if ($currentTache === null || $currentTache['id'] !== $tache->id) {

                if ($currentTache !== null) {
                    $result[] = $currentTache;
                }

                $currentTache = [
                    "id" => $tache->id,
                    "Nom" => $tache->Nom,
                    "Description" => $tache->Description,
                    "DateDebut" => $tache->DateDebut,
                    "DateFin" => $tache->DateFin,
                    "status" => $tache->status,
                    "departements" => [$tache->NomDepartement],
                    "users" => [$tache->nom . ' ' . $tache->prenom],
                ];
            } else {

                $currentTache["departements"][] = $tache->NomDepartement;
                $currentTache["users"][] = $tache->nom . ' ' . $tache->prenom;
            }
        }

        if ($currentTache !== null) {
            $result[] = $currentTache;
        }

        return $result;
    }

   public function getEmployeeBydepartement($departement_id){

        $employees = User::where('departement_id', $departement_id)->where('role', 'employee')->get();
        if ($employees->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun employé trouvé dans ce département'
                ], 404);
            }
            return response()->json([
                'departement_id' => $departement_id,
                'employees' => $employees
            ], 200);
}

}
