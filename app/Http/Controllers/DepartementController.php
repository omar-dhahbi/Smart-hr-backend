<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\departements;
use Illuminate\Support\Facades\Validator;

class DepartementController extends Controller
{
    public function index()
    {
        return response()->json(departements::all());
    }

    public function getdepartementsById($id)
    {
        $departement = departements::find($id);

        if (!$departement) {
            return response()->json(['error' => 'Departement Not Found'], 404);
        }

        return response()->json($departement);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NomDepartement' => 'required|unique:departements|min:3|string',
            // 'Description' => 'required|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $departement = departements::create([
            'NomDepartement' => $request->NomDepartement,
            // 'Description' => $request->Description,
        ]);

        return response()->json([
            'message' => 'Departement added successfully',
            'data' => $departement
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $departement = departements::find($id);

        if (!$departement) {
            return response()->json(['message' => 'Departement Not Found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'NomDepartement' => "required|min:3|unique:departements,NomDepartement,$id",
            // 'Description' => 'required|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $departement->update([
            'NomDepartement' => $request->NomDepartement,
            // 'Description' => $request->Description,
        ]);

        return response()->json([
            'message' => 'Departement updated',
            'data' => $departement
        ]);
    }
    public function destroy($id)
    {
        $departement = departements::find($id);

        if (!$departement) {
            return response()->json(['message' => 'Departement not found'], 404);
        }

        $departement->delete();

        return response()->json(['message' => 'Departement deleted']);
    }
    public function search(Request $request)
    {
        $query = $request->search;

        $departements = departements::where('NomDepartement', 'like', "%$query%")
            ->get();

        return response()->json($departements);
    }
}
