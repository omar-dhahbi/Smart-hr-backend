<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\congé;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class CongéController extends Controller
{
    public function demandeConge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'type' => 'required',
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after:dateDebut',
            'nbrJour' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $lastRequest = congé::where('user_id', $request->user_id)
            ->where('created_at', '>=', now()->subDay())
            ->first();
        if (!empty($lastRequest)) {
            return response()->json(['error' => 'Vous avez déjà soumis une demande de congé au cours des dernières 24 heures.'], 404);
        }
        if ($request->type === "simple") {
            $user = User::find($request->user_id);
            $nbJourConge = $user->nb_jour_conge;
            $nbrjour = $nbJourConge - $request->nbrJour;

            if ($nbrjour < 0 || $nbrjour > 21) {
                return response()->json(['error' => 'Demande non effectuée, nombre de jours demandés > 21'], 404);
            }
            // $user->save();
        }
        $conge = new congé();
        $conge->user_id = $request->user_id;
        $conge->type = $request->type;
        $conge->dateDebut = $request->dateDebut;
        $conge->dateFin = $request->dateFin;
        $conge->nbrJour = $request->nbrJour;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $picture = date('His') . '-' . $filename;
            $file->move(public_path('/public/images'), $picture);
            $conge->photo = "/images/" . $picture;
        }
        $conge->cause = $request->cause;
        $conge->status = 'attente';
        $conge->enCongé = false;
        $conge->save();
        /*$rhUsers = users::where('role', 'RH')->get();
        foreach ($rhUsers as $rh) {
            Notifications::create([
                'user_id' => $rh->id,
                'message' => "Nouvelle demande de congé de {$user->nom} {$user->prenom}",
            ]);
        }*/
        return response()->json(['message' => 'Demande de congé effectuée avec succès.'], 201);
    }
}
