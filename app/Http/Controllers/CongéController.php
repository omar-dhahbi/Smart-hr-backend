<?php

namespace App\Http\Controllers;

use App\Models\congé;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            'nbrJour' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $lastRequest = congé::where('user_id', $request->user_id)
            ->where('created_at', '>=', now()->subDay())
            ->first();
        if (! empty($lastRequest)) {
            return response()->json(['error' => 'Vous avez déjà soumis une demande de congé au cours des dernières 24 heures.'], 404);
        }
        // if ($request->type === 'simple') {
        //     $user = User::find($request->user_id);
        //     $nbJourConge = $user->nb_jour_conge;
        //     $nbrjour = $nbJourConge - $request->nbrJour;

        //     if ($nbrjour < 0 || $nbrjour > 21) {
        //         return response()->json(['error' => 'Demande non effectuée, nombre de jours demandés > 21'], 404);
        //     }
        //     // $user->save();
        // }
        $conge = new congé;
        $conge->user_id = $request->user_id;
        $conge->type = $request->type;
        $conge->dateDebut = $request->dateDebut;
        $conge->dateFin = $request->dateFin;
        $conge->nbrJour = $request->nbrJour;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $picture = date('His').'-'.$filename;
            $file->move(public_path('/public/images'), $picture);
            $conge->photo = '/images/'.$picture;
        }
        $conge->cause = $request->cause;
        if (Carbon::parse($request->dateDebut)->isPast()) {
            $conge->status = 'refusé';
            $conge->status2 = 'refusé';
            $conge->enCongé = false;
        } else {
            $conge->status = 'attente';
            $conge->status2 = 'attente';
            $conge->enCongé = false;
        }
        $conge->save();
        $user = User::find($request->user_id);

        if (! $user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        // if ($user->role === 'RH') {
        //     $receivers = User::where('role', 'admin')->get();
        // } else {
        //     $receivers = User::where('role', 'RH')->get();
        // }
        // foreach ($receivers as $receiver) {
        //     Notification::create([
        //         'user_id' => $receiver->id,
        //         'message' => "Nouvelle demande de congé de {$user->nom} {$user->prenom}",
        //     ]);
        // }

        return response()->json(['message' => 'Demande de congé effectuée avec succès.'], 201);
    }

    public function preApproveConge($id)
    {
        $conge = congé::find($id);
        if (! $conge) {
            return response()->json(['error' => 'Congé non trouvé'], 404);
        }

        $conge->status = 'accepté';
        $conge->save();

        // // notification au Responsable RH
        // $responsables = User::where('role', 'ResponsableRh')->get();

        // foreach ($responsables as $resp) {
        //     Notification::create([
        //         'user_id' => $resp->id,
        //         'message' => 'Nouvelle demande à valider (pré-acceptée)',
        //     ]);
        // }

        return response()->json(['message' => 'Pré-acceptation effectuée']);
    }

    public function preRefuseConge($id)
    {
        $conge = congé::find($id);

        if (! $conge) {
            return response()->json(['error' => 'Congé non trouvé'], 404);
        }

        $conge->status = 'refusé';
        $conge->status2 = 'refusé';
        $conge->enCongé = false;
        $conge->save();

        // Notification::create([
        //     'user_id' => $conge->user_id,
        //     'message' => 'Votre demande de congé a été refusée (Agent RH)',
        // ]);

        return response()->json(['message' => 'Refus effectué']);
    }

    public function finalApproveConge($id)
    {
        $conge = congé::find($id);

        if (! $conge || $conge->status != 'accepté') {
            return response()->json(['error' => 'Pré-acceptation requise'], 400);
        }
        $conge->status2 = 'accepté';
        $conge->enCongé = true;

        // Déduction jours
        if ($conge->type == 'autre') {
            $user = User::find($conge->user_id);
            $user->nb_jour_conge -= $conge->nbrJour;
            $user->save();
        }

        $conge->save();

        // Notification::create([
        //     'user_id' => $conge->user_id,
        //     'message' => 'Votre congé est accepté définitivement',
        // ]);

        return response()->json(['message' => 'Acceptation finale effectuée']);
    }

    public function finalRefuseConge($id)
    {
        $conge = congé::find($id);

        if (! $conge) {
            return response()->json(['error' => 'Congé non trouvé'], 404);
        }

        $conge->status2 = 'refusé';
        $conge->enCongé = false;
        $conge->save();

        // Notification::create([
        //     'user_id' => $conge->user_id,
        //     'message' => 'Votre demande a été refusée par Responsable RH',
        // ]);

        return response()->json(['message' => 'Refus final effectué']);
    }

    public function getCongeAttente(Request $request)
    {

        $query = congé::join('users', 'users.id', '=', 'congés.user_id') // jointure avec users
            ->select('congés.*', 'users.nom', 'users.prenom') // sélectionner données congé + nom/prénom
            ->where(function ($q) {
                $q->where('congés.status', 'attente') // cas 1 : agent RH n'a pas encore traité
                    ->orWhere(function ($q2) {
                        $q2->where('congés.status', 'accepté') // cas 2 : agent a accepté
                            ->where('congés.status2', 'attente'); // mais responsable pas encore
                    });
            })
            ->orderBy('congés.id', 'desc'); // tri du plus récent

        return response()->json($query->get());
    }

    public function getCongeApprove(Request $request)
    {

        $query = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom')
            ->where('congés.status', 'accepté')
            ->where('congés.status2', 'accepté')
            ->orderBy('congés.id', 'desc');

        return response()->json($query->get());
    }

    public function getCongeRefuse(Request $request)
    {

        $query = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom')
            ->where(function ($q) {
                $q->where('congés.status', 'refusé')
                    ->orWhere('congés.status2', 'refusé');
            })
            ->orderBy('congés.id', 'desc');

        return response()->json($query->get());
    }

    public function getResultByUser($user_id)
    {
        $conges = congé::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->get();
        foreach ($conges as $c) {
            if ($c->status == 'refusé' || $c->status2 == 'refusé') {
                $c->resultat = 'refusé';
            } elseif ($c->status == 'accepté' && $c->status2 == 'accepté') {
                $c->resultat = 'accepté';
            } else {
                $c->resultat = 'attente';
            }
        }

        return response()->json($conges);
    }
}
