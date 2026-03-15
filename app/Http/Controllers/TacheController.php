<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\tache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class TacheController extends Controller
{
     public function index()
    {
        $tache = DB::table('taches')
                ->join('users', 'users.id', '=', 'taches.user_id')
                ->select('taches.*', 'users.nom', 'users.prenom')
                ->get();
        return response()->json($tache);
    }
     public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'Nom' => 'required|string',
            'Description' => 'required|string',
            'DateDebut' => 'required|date',
            'DateFin' => 'required|date|after:DateDebut',
            'departement_id' => 'required|exists:departements,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 404);
        }

        $tache = new tache();
        $tache->user_id = $request->user_id;
        $tache->Nom = $request->Nom;
        $tache->Description = $request->Description;
        $tache->DateDebut = $request->DateDebut;
        $tache->DateFin = $request->DateFin;
        $tache->status = $request->status;
        $tache->departement_id = $request->departement_id;


        $tache->save();

        return response()->json(['message' => 'Tache ajouter avec succes'], 200);
    }
     public function show($id)
    {
        $tache = tache::find($id);

        if (is_null($tache)) {
            return response()->json(['error' => 'Tache Non trouvé.'], 404);
        }

        return response()->json($tache);
    }

     public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                Rule::exists('users', 'id'),
            ],
            'Nom' => 'required|string',
            'Description' => 'required|string',
            'DateDebut' => 'required|date',
            'DateFin' => 'required|date|after:DateDebut',
              'departement_id' => [
                'required',
                Rule::exists('departements', 'id'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        } $tache = tache::find($id);

        if (is_null($tache)) {
            return response()->json(['error' => 'Tache non trouvé.'], 404);
        }

        $tache->user_id = $request->user_id;
        $tache->Nom = $request->Nom;
        $tache->Description = $request->Description;
        $tache->DateDebut = $request->DateDebut;
        $tache->DateFin = $request->DateFin;
        $tache->status = $request->status;
        $tache->departement_id = $request->departement_id;


        $tache->save();
        return response()->json($tache);
    }
     public function search(Request $request)
    {
        $query = $request->input('search');

        $taches = tache::where('Nom', 'like', "%$query%")
            ->orWhere('Description', 'like', "%$query%")
            ->orWhere('DateDebut', 'like', "%$query%")
            ->orWhere('DateFin', 'like', "%$query%")
            ->get();

        return response()->json($taches);
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
 public function getTacheByUserId($user_id)
    {
        $tache = tache::where('user_id', $user_id)->get();
        if (is_null($tache)) {
            return response()->json(['error' => "Utilisateur n'est pas utulisé"], 404);
        } else{
            return response()->json(["tache"=>$tache], 200);
        }
    }

}
