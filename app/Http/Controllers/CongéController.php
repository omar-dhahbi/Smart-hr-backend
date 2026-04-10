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
            'dateFin' => 'required|date',
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

        $conge = new congé;
        $conge->user_id = $request->user_id;
        $conge->type = $request->type;
        $conge->dateDebut = $request->dateDebut;
        $conge->dateFin = $request->dateFin;
        $conge->nbrJour = $request->nbrJour;
        $conge->cause = $request->cause;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = date('His').'-'.$file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $conge->photo = '/images/'.$filename;
        }

        // Définir le statut en fonction de la date
        if (Carbon::parse($request->dateDebut)->isPast()) {
            $conge->status = 'refusé';
            $conge->status2 = 'refusé';
        } else {
            $conge->status = 'attente';
            $conge->status2 = 'attente';
        }
        $conge->enCongé = false;
        $conge->save();
        $user = User::find($request->user_id);
        $receivers = User::where('role', 'agentRh')->get();
        foreach ($receivers as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Nouvelle demande de congé de {$user->nom} {$user->prenom}",
                'type' => 'demande-conge',
            ]);
        }

        return response()->json(['message' => 'Demande de congé effectuée avec succès.'], 201);
    }

    public function demandeCongeAgentRH(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'type' => 'required',
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date',
            'nbrJour' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = User::find($request->user_id);
        if (! $user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $lastRequest = congé::where('user_id', $request->user_id)
            ->where('created_at', '>=', now()->subDay())
            ->first();
        if (! empty($lastRequest)) {
            return response()->json(['error' => 'Vous avez déjà soumis une demande dans 24h.'], 409);
        }

        $conge = new congé;
        $conge->user_id = $request->user_id;
        $conge->type = $request->type;
        $conge->dateDebut = $request->dateDebut;
        $conge->dateFin = $request->dateFin;
        $conge->nbrJour = $request->nbrJour;
        $conge->cause = $request->cause;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time().'-'.$file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $conge->photo = 'images/'.$filename;
        }
        if (Carbon::parse($request->dateDebut)->isPast()) {
            $conge->status = null;
            $conge->status2 = 'refusé';
        } else {
            $conge->status = null;
            $conge->status2 = 'attente';
        }        $conge->enCongé = false;
        $conge->save();
        $receivers = User::where('role', 'ResponsableRH')->get();
        foreach ($receivers as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Nouvelle demande (AgentRH) de {$user->nom} {$user->prenom}",
                'type' => 'demande-conge',

            ]);
        }

        return response()->json(['message' => 'Demande envoyée au Responsable RH avec succès.'], 201);
    }

    // Pré-acceptation/refus par Agent RH
    // Pré-acceptation
    public function preApproveConge($id)
    {
        $conge = congé::find($id);

        if (! $conge) {
            return response()->json(['error' => 'Congé non trouvé'], 404);
        }

        $conge->status = 'accepté';
        $conge->save();

        // Notification user
        Notification::create([
            'user_id' => $conge->user_id,
            'message' => 'Votre demande de congé est pré-acceptée par Agent RH',
            'type' => 'resultat-conge',
        ]);
        $responsables = User::where('role', 'ResponsableRh')->get();
        foreach ($responsables as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => 'Demande de congé pré-acceptée en attente de validation finale',
                'type' => 'resultat-conge',
            ]);
        }

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
        Notification::create([
            'user_id' => $conge->user_id,
            'message' => 'Votre demande de congé a été refusée par Agent RH',
            'type' => 'resultat-conge',
        ]);

        return response()->json(['message' => 'Refus effectué']);
    }

    public function finalApproveConge($id)
    {
        $conge = congé::find($id);

        if (! $conge) {
            return response()->json(['error' => 'Congé non trouvé'], 404);
        }

        if ($conge->status === 'refusé') {
            return response()->json(['error' => 'Demande refusée par Agent RH'], 400);
        }
        if ($conge->status === 'attente') {
            return response()->json(['error' => 'Pré-acceptation requise'], 400);
        }

        $conge->status2 = 'accepté';
        $conge->enCongé = true;

        $user = User::find($conge->user_id);
        if ($user) {
            $user->nb_jour_conge -= $conge->nbrJour;
            $user->save();
        }
        $conge->save();

        Notification::create([
            'user_id' => $conge->user_id,
            'message' => 'Votre congé est accepté définitivement',
            'type' => 'resultat-conge',
        ]);

        $agents = User::where('role', 'agentRh')->get();
        foreach ($agents as $a) {
            Notification::create([
                'user_id' => $a->id,
                'message' => 'Un congé a été validé définitivement',
                'type' => 'resultat-conge',

            ]);
        }

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

        Notification::create([
            'user_id' => $conge->user_id,
            'message' => 'Votre demande de congé a été refusée définitivement',
            'type' => 'resultat-conge',

        ]);

        return response()->json(['message' => 'Refus final effectué']);
    }

    // Récupérer les congés par statut pour Responsable RH
    public function getCongeAttenteResponsable()
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom', 'users.role')
            ->where(function ($query) {

                // employee / chefProjet → après validation agentRH
                $query->where(function ($q) {
                    $q->whereIn('users.role', ['employee', 'chefProjet'])
                        ->where('congés.status', 'accepté')
                        ->where('congés.status2', 'attente');
                })

                // agentRH → direct
                    ->orWhere(function ($q) {
                        $q->where('users.role', 'agentRh')
                            ->where('congés.status2', 'attente');
                    });

            })
            ->orderBy('congés.id', 'desc')
            ->get();

        return response()->json($conges);
    }

    public function getCongeApproveResonsableRH(Request $request)
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom')
            // ->where('congés.status', 'accepté')
            ->where('congés.status2', 'accepté')
            ->orderBy('congés.id', 'desc')
            ->get();

        return response()->json($conges);
    }

    public function getCongeRefuseResponsableRH(Request $request)
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom')
            // ->where('congés.status', 'refusé')
            ->where('congés.status2', 'refusé')
            ->orderBy('congés.id', 'desc')
            ->get();

        return response()->json($conges);
    }

    // Récupérer les congés par utilisateur
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

    public function getResultByUserAgentRh($user_id)
    {
        $conges = congé::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($conges as $c) {
            if ($c->status2 == 'refusé') {
                $c->resultat = 'refusé';
            } elseif ($c->status2 == 'accepté') {
                $c->resultat = 'accepté';
            } else {
                $c->resultat = 'attente';
            }
        }

        return response()->json($conges);
    }

    // Récupérer congés par statut pour Agent RH
    public function getCongeAttenteAgentRH()
    {
        $conges = congé::where('congés.status', 'attente')
            ->join('users', 'users.id', '=', 'congés.user_id')
            ->whereIn('users.role', ['chefProjet', 'employee'])
            ->select('congés.*', 'users.*')
            ->orderBy('congés.id', 'desc')
            ->get();

        return response()->json($conges);
    }

    public function getCongeApproveAgentRH()
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->whereIn('users.role', ['chefProjet', 'employee'])
            ->where('congés.status', 'accepté')
            ->where('congés.status2', 'accepté')
            ->select('congés.*', 'users.*')
            ->orderBy('congés.id', 'desc')
            ->get();

        return response()->json($conges);
    }

    public function getCongeRefuseAgentRH()
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->whereIn('users.role', ['chefProjet', 'employee'])
            ->where('congés.status', 'refusé')
            ->where('congés.status2', 'refusé')
            ->select('congés.*', 'users.*')
            ->orderBy('congés.id', 'desc')
            ->get();

        return response()->json($conges);
    }
}
