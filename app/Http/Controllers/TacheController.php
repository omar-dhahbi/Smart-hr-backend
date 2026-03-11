<?php

namespace App\Http\Controllers;

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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 404);
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


}
