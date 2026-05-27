<?php

namespace App\Http\Controllers;

use App\Models\Projet_tache_users;
use App\Models\taches;
use Carbon\Carbon;
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
            'projet_id' => 'required|exists:projets,id',
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

        foreach ($request->user_id as $user) {

            $pivot = new Projet_tache_users;
            $pivot->projet_id = $request->projet_id;
            $pivot->tache_id = $tache->id;
            $pivot->user_id = $user;
            $pivot->save();
        }

        return response()->json([
            'message' => 'Tache created successfully',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $tache = taches::find($id);

        if (! $tache) {
            return response()->json([
                'message' => 'Tache not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:incomplet,EnCours,complet',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 422);
        }

        $tache->status = $request->status;
        $tache->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => $tache,
        ], 200);
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
                'projets.NomProjet',
                'users.nom',
                'users.prenom'
            )

            ->where('taches.id', $id)

            ->get();

        return response()->json($data);
    }

    // public function index()
    // {

    //     $taches = DB::table('taches')
    //         ->join('projet_tache_users', 'taches.id', '=', 'projet_tache_users.tache_id')
    //         ->join('projets', 'projets.id', '=', 'projet_tache_users.projet_id')
    //         ->join('users', 'users.id', '=', 'projet_tache_users.user_id')

    //         ->select(
    //             'taches.id',
    //             'taches.Nom',
    //             'taches.Description',
    //             'taches.DateDebut',
    //             'taches.DateFin',
    //             'taches.status',
    //             'projets.NomProjet',
    //             'users.nom',
    //             'users.prenom'
    //         )
    //         ->orderBy('taches.id')

    //         ->get();

    //     // $result = [];
    //     // $currentTache = null;
    //     $today = Carbon::today();

    //     foreach ($taches as $tache) {
    //         if (
    //             $tache->status === 'EnCours' &&
    //             (
    //                 Carbon::parse($tache->DateFin)->isPast()
    //             )
    //         ) {
    //             DB::table('taches')
    //                 ->where('id', $tache->id)
    //                 ->update(['status' => 'incomplet']);

    //             $tache->status = 'incomplet';
    //         }
    //         //     if ($currentTache === null || $currentTache['id'] !== $tache->id) {

    //         //         if ($currentTache !== null) {
    //         //             $result[] = $currentTache;
    //         //         }

    //         //         $currentTache = [
    //         //             'id' => $tache->id,
    //         //             'Nom' => $tache->Nom,
    //         //             'Description' => $tache->Description,
    //         //             'DateDebut' => $tache->DateDebut,
    //         //             'DateFin' => $tache->DateFin,
    //         //             'status' => $tache->status,
    //         //             'projet_id' => $tache->NomProjet,
    //         //             'users' => [$tache->nom.' '.$tache->prenom],
    //         //         ];
    //         //     }
    //         //     // $currentTache['users'][] = $tache->nom.' '.$tache->prenom;

    //         // }

    //         // if ($currentTache !== null) {
    //         //     $result[] = $currentTache;
    //         // }

    //         // return $result;
    //     }
    // }
    public function index()
    {
        $taches = DB::table('taches')
            ->join('projet_tache_users', 'taches.id', '=', 'projet_tache_users.tache_id')
            ->join('projets', 'projets.id', '=', 'projet_tache_users.projet_id')
            ->select(
                'taches.id',
                'taches.Nom',
                'taches.Description',
                'taches.DateDebut',
                'taches.DateFin',
                'taches.status',
                'projets.NomProjet as projet_id'
            )
            ->distinct()
            ->get();
        foreach ($taches as $tache) {
            if ($tache->status === 'EnCours' && Carbon::parse($tache->DateFin)->isPast()) {
                // DB::table('taches')
                //     ->where('id', $tache->id)
                //     ->update(['status' => 'incomplet']);

                $tache->status = 'incomplet';
                $tache->save();
            }
        }
        return $taches;
    }
    public function getTacheByUserId($user_id)
    {
        $tache = taches::join('projet_tache_users', 'taches.id', '=', 'projet_tache_users.tache_id')
            ->join('projets', 'projet_tache_users.projet_id', '=', 'projets.id')
            ->join('users', 'projet_tache_users.user_id', '=', 'users.id')->select('taches.*', 'projets.NomProjet as projet_id')
            ->where('users.id', '=', $user_id)
            ->get();
        foreach ($tache as $item) {

            if (
                $item->status === 'EnCours' &&
                Carbon::parse($item->DateFin)->isPast()
            ) {

                $item->status = 'incomplet';
                $item->save();
            }
        }

        return $tache;
    }
    public function getTacheByProjetId($projet_id)
    {
        $rows = DB::table('taches')
            ->join('projet_tache_users', 'taches.id', '=', 'projet_tache_users.tache_id')
            ->join('projets', 'projet_tache_users.projet_id', '=', 'projets.id')
            ->join('users', 'projet_tache_users.user_id', '=', 'users.id')
            ->select(
                'taches.id',
                'taches.Nom',
                'taches.Description',
                'taches.DateDebut',
                'taches.DateFin',
                'taches.status',
                'projets.NomProjet',
                'users.nom',
                'users.prenom',
                'users.photo',
            )
            ->where('projets.id', $projet_id)
            ->get();

        // $result = [];

        // foreach ($rows as $row) {

        //     if (! isset($result[$row->id])) {
        //         $result[$row->id] = [
        //             'id' => $row->id,
        //             'Nom' => $row->Nom,
        //             'Description' => $row->Description,
        //             'DateDebut' => $row->DateDebut,
        //             'DateFin' => $row->DateFin,
        //             'status' => $row->status,
        //             'projet' => $row->NomProjet,
        //             'users' => [],
        //         ];
        //     }
        //     $result[$row->id]['users'][] = [
        //         'nom' => $row->nom,
        //         'prenom' => $row->prenom,
        //         'photo' => $row->photo,
        //     ];
        // }

        // return array_values($result);
        return response()->json($rows);

    }

    public function getEmployeeNonCongé()
    {
        $users = DB::table('users')
            ->where('role', 'employee')
            ->where('enConge', false)
            ->where('verif_email', true)
            ->where('status', true)
            ->select('*')
            ->get();

        return response()->json($users);
    }

    public function getEmployeByTache($tache_id)
    {
        $employees = DB::table('projet_tache_users')
            ->join('users', 'projet_tache_users.user_id', '=', 'users.id')
            ->join('taches', 'projet_tache_users.tache_id', '=', 'taches.id')
            ->select(
                'users.id',
                'users.nom',
                'users.prenom',
                'users.photo',
                'taches.Nom as nom_tache'
            )
            ->where('projet_tache_users.tache_id', $tache_id)

            ->get();

        return response()->json($employees);
    }
}
