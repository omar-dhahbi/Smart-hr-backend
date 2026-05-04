<?php

namespace App\Http\Controllers;

use App\Models\Projets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjetController extends Controller
{
    public function index()
    {
        $Projets = Projets::get();

        return response()->json($Projets);
    }

    public function getProjetById($id)
    {
        $Projets = Projets::find($id);
        if (is_null($Projets)) {
            return response()->json(['error' => 'Projet Not Found.'], 404);
        }

        return response()->json(Projets::find($id));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NomProjet' => 'required|unique:projets,NomProjet|min:2|string',
            'Description' => 'required|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $Projets = new Projets;

        $Projets->NomProjet = $request->NomProjet;
        $Projets->Description = $request->Description;
        $Projets->save();

        return response()->json([
            'message' => 'Projet added successfully',
            'data' => $Projets,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $Projets = Projets::find($id);
        if (is_null($Projets)) {
            return response()->json(['message' => 'Projets Not Found.'], 404);
        }
        $Projets->update($request->all());

        return response()->json($Projets);
    }

    public function destroy($id)
    {
        $Projets = Projets::find($id);
        if (is_null($Projets)) {
            return response()->json(['message' => 'Projet not found']);
        }
        $Projets->delete();

        return response()->json(['message' => 'Projets deleted']);
    }

    public function search(Request $request)
    {
        $query = $request->search;

        $projets = Projets::where('NomProjet', 'like', "%$query%")
            ->orWhere('Description', 'like', "%$query%")
            ->get();

        return response()->json($projets);
    }
}
