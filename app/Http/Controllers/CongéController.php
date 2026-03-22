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
        if ($dateDebut->isPast()) {
            $conge->status = 'refusé';
            $conge->enCongé = false;
        }
        else {
            $conge->status = 'attente';
            $conge->enCongé = false;
        }
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

     public function approveConge($id)
{
    $conge = congé::find($id);

    if (!$conge) {
        return response()->json(['error' => 'Congé non trouvé'], 404);
    }

    $conge->status = 'accepté';

    if ($conge->type == "simple") {
        $user = User::find($conge->user_id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $nbj = $user->nb_jour_conge - $conge->nbrJour;
        $user->nb_jour_conge = $nbj;
        $user->save();
    }


    /*Notifications::create([
        'user_id' => $conge->user_id,
        'message' => 'Votre demande de congé a été acceptée.',
    ]);*/
    $conge->enCongé = true;
    $conge->save();

    return response()->json($conge);
}
public function refuseConge($id)
{
  $conge = congé::find($id);

    if (!$conge) {
        return response()->json(['error' => 'Congé non trouvé'], 404);
    }

    $conge->status = 'refusé';
    $conge->enCongé = false;
    $conge->save();

    return response()->json([
        'message' => 'Conge refused successfully'
    ], 200);
}


public function getCongeAttente()
    {
        $congéAttente = congé::where('congés.status', '=', 'attente')
            ->join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom')
            ->get();
        return response()->json($congéAttente);
    }

public function getCongeApprove()
    {
        $congéAttente = congé::where('congés.status', '=', 'accepté')
            ->join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom')
            ->get();
        return response()->json($congéAttente);
    }

public function getCongeRefuse()
    {
        $congéAttente = congé::where('congés.status', '=', 'refusé')
            ->join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom')
            ->get();
        return response()->json($congéAttente);
    }

public function getResultByUser($user_id)
{

    $conges = congé::where('user_id', $user_id)->get();

        if (is_null($conges)) {
            return response()->json(['error' => "Utilisateur n'est pas utulisé"], 404);
        } else {
    return response()->json(["conges"=>$conges], 200);
        }
}}
