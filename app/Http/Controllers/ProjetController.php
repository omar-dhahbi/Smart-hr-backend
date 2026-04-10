<?php

namespace App\Http\Controllers;

use App\Models\Projets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjetController extends Controller
{
    public function index()
    {
        return response()->json(Projets::all());
    }

    public function getProjetById($id)
    {
        $projet = Projets::find($id);

        if (! $projet) {
            return response()->json(['error' => 'Projet Not Found'], 404);
        }

        return response()->json($projet);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NomProjet' => 'required|unique:projets,NomProjet|min:2|string',
            'Description' => 'required|min:5|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $projet = Projets::create([
            'NomProjet' => $request->NomProjet,
            'Description' => $request->Description,
        ]);

        return response()->json([
            'message' => 'Projet added successfully',
            'data' => $projet,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $projet = Projets::find($id);

        if (! $projet) {
            return response()->json(['message' => 'Projet Not Found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'NomProjet' => "required|min:3|unique:projets,NomProjet,$id",
            'Description' => 'required|min:5|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $projet->update([
            'NomProjet' => $request->NomProjet,
            'Description' => $request->Description,
        ]);

        return response()->json([
            'message' => 'Projet updated successfully',
            'data' => $projet,
        ]);
    }

    public function destroy($id)
    {
        $projet = Projets::find($id);

        if (! $projet) {
            return response()->json(['message' => 'Projet not found'], 404);
        }

        $projet->delete();

        return response()->json(['message' => 'Projet deleted successfully']);
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
