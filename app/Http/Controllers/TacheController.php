<?php

namespace App\Http\Controllers;

use App\Models\Projet_tache_users;
use App\Models\taches;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TacheController extends Controller
{
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'Nom' => 'required',
            'Description' => 'required|min:10',
            'DateDebut' => 'required|date',
            'DateFin' => 'required|date',
            'projet_id' => 'required|array',
            'user_id' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }

        $tache = new taches;

        $tache->Nom = $request->Nom;
        $tache->Description = $request->Description;
        $tache->DateDebut = $request->DateDebut;
        $tache->DateFin = $request->DateFin;
        $tache->status = $request->status ?? 'incomplet';

        $tache->save();

        foreach ($request->projet_id as $dep) {

            foreach ($request->user_id as $user) {

                $pivot = new Projet_tache_users;

                $pivot->projet_id = $dep;
                $pivot->tache_id = $tache->id;
                $pivot->user_id = $user;

                $pivot->save();
            }
        }

        return response()->json([
            'message' => 'Tache created successfully',
        ]);
    }

    public function update(Request $request, $id)
    {

        $tache = taches::find($id);

        if (! $tache) {
            return response()->json(['message' => 'Tache not found'], 404);
        }

        $tache->Nom = $request->Nom;
        $tache->Description = $request->Description;
        $tache->DateDebut = $request->DateDebut;
        $tache->DateFin = $request->DateFin;
        $tache->status = $request->status;

        $tache->save();

        DB::table('projet_tache_users')
            ->where('tache_id', $id)
            ->delete();

        foreach ($request->projet_id as $dep) {

            foreach ($request->user_id as $user) {

                Projet_tache_users::create([
                    'projet_id' => $dep,
                    'tache_id' => $id,
                    'user_id' => $user,
                ]);
            }
        }

        return response()->json([
            'message' => 'Tache updated successfully',
        ]);
    }

    public function getDataById($id)
    {

        $data = DB::table('taches')

            ->join('projet_tache_users', 'taches.id', '=', 'projet_tache_users.tache_id')

            ->join('projets', 'projets.id', '=', 'projet_tache_users.projet_id')

            ->join('users', 'users.id', '=', 'projet_tache_users.user_id')

            ->select(
                'taches.id',
                'taches.Nom',
                'taches.Description',
                'taches.DateDebut',
                'taches.DateFin',
                'taches.status',
                'projets.Nom',
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

        if (! $tache) {
            return response()->json(['message' => 'Tache not found'], 404);
        }

        DB::table('projet_tache_users')
            ->where('tache_id', $id)
            ->delete();

        $tache->delete();

        return response()->json([
            'message' => 'Tache deleted successfully',
        ]);
    }

    public function index()
    {

        $taches = DB::table('taches')
            ->join('projet_tache_users', 'taches.id', '=', 'projet_tache_users.tache_id')
            ->join('projets', 'projets.id', '=', 'projet_tache_users.projet_id')
            ->join('users', 'users.id', '=', 'projet_tache_users.user_id')

            ->select(
                'taches.id',
                'taches.Nom',
                'taches.Description',
                'taches.DateDebut',
                'taches.DateFin',
                'taches.status',
                'projets.Nom',
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
                    'id' => $tache->id,
                    'Nom' => $tache->Nom,
                    'Description' => $tache->Description,
                    'DateDebut' => $tache->DateDebut,
                    'DateFin' => $tache->DateFin,
                    'status' => $tache->status,
                    'projets' => [$tache->Nom],
                    'users' => [$tache->nom.' '.$tache->prenom],
                ];
            } else {

                $currentTache['projets'][] = $tache->Nom;
                $currentTache['users'][] = $tache->nom.' '.$tache->prenom;
            }
        }

        if ($currentTache !== null) {
            $result[] = $currentTache;
        }

        return $result;
    }

    public function getEmployeeByProjet($projet_id)
    {

        $employees = User::where('projet_id', $projet_id)->where('role', 'employee')->get();
        if ($employees->isEmpty()) {
            return response()->json([
                'message' => 'Aucun employé trouvé dans ce département',
            ], 404);
        }

        return response()->json([
            'projet_id' => $projet_id,
            'employees' => $employees,
        ], 200);
    }

    public function getTacheByUserId($user_id)
    {

        $tache = tache::join('projet_tache_users', 'taches.id', '=', 'projet_tache_users.tache_id')
            ->join('projets', 'projet_tache_users.projet_id', '=', 'projets.id')
            ->join('users', 'projets.id', '=', 'users.projet_id')
            ->select('taches.*', 'projets.Nom')
            ->where('users.id', '=', $user_id)
            ->get();

        return $tache;
    }
}
