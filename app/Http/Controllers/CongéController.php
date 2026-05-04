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
            'user_id' => 'required|exists:users,id',
            'type' => 'required',
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date',
            'nbrJour' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = User::find($request->user_id);

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
            $filename = time().'-'.$file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $conge->photo = 'images/'.$filename;
        }
        $conge->status = 'attente';
        $conge->status2 = 'attente';
        $conge->save();
        if ($user) {
            $user->enConge = false;
            $user->save();
        }
        $receivers = User::where('role', 'agentRh')->get();
        foreach ($receivers as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Nouvelle demande de congé de {$user->nom} {$user->prenom}",
                'type' => 'demande-conge',
            ]);
        }

        return response()->json([
            'message' => 'Demande de congé effectuée avec succès.',
        ], 201);
    }

    public function demandeCongeAgentRH(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'type' => 'required',
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date',
            'nbrJour' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        if (! $user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
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
            $filename = time().'-'.$file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $conge->photo = 'images/'.$filename;
        }

        $conge->status = null;
        $conge->status2 = 'attente';
        $conge->save();

        $user->enConge = false;
        $user->save();

        $receivers = User::where('role', 'ResponsableRH')->get();

        foreach ($receivers as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Nouvelle demande (AgentRH) de {$user->nom} {$user->prenom}",
                'type' => 'demande-conge',
            ]);
        }

        return response()->json([
            'message' => 'Demande envoyée au Responsable RH avec succès.',
        ], 201);
    }

    public function preApproveConge($id)
    {
        $conge = congé::find($id);

        if (! $conge) {
            return response()->json(['error' => 'Congé non trouvé'], 404);
        }

        $conge->status = 'accepté';
        $conge->save();

        $user = User::find($conge->user_id);
        if ($user) {
            $user->enConge = true;
            $user->save();
        }

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
        $conge->save();

        $user = User::find($conge->user_id);
        if ($user) {
            $user->enConge = false;
            $user->save();
        }

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
        $conge->save();

        $user = User::find($conge->user_id);
        if ($user) {
            $user->enConge = true;
            $user->nb_jour_conge -= $conge->nbrJour;
            $user->save();
        }

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

        // Mise à jour statut congé
        $conge->status2 = 'refusé';
        $conge->save();

        // Récupérer utilisateur
        $user = User::find($conge->user_id);
        if ($user) {
            $user->enConge = false;
            $user->save();
        }

        // Notification utilisateur
        Notification::create([
            'user_id' => $conge->user_id,
            'message' => 'Votre demande de congé a été refusée définitivement',
            'type' => 'resultat-conge',
        ]);

        // Notification aux agents RH
        $agents = User::where('role', 'agentRh')->get();
        foreach ($agents as $a) {
            Notification::create([
                'user_id' => $a->id,
                'message' => 'Un congé a été refusé définitivement',
                'type' => 'resultat-conge',
            ]);
        }

        return response()->json(['message' => 'Refus final effectué']);
    }

    // Récupérer les congés par statut pour Responsable RH
    public function getCongeAttenteResponsable()
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select(
                'congés.*',
                'users.id as user_id',
                'users.nom',
                'users.prenom',
                'users.photo',
                'users.nb_jour_conge'
            )->where(function ($query) {

                $query->where(function ($q) {
                    $q->whereIn('users.role', ['employee', 'chefProjet'])
                        ->where('congés.status', 'accepté')
                        ->where('congés.status2', 'attente');
                })

                    ->orWhere(function ($q) {
                        $q->where('users.role', 'agentRh')
                            ->where('congés.status2', 'attente');
                    });

            })
            ->orderBy('congés.id', 'desc')
            ->get();

        return response()->json($conges);
    }

    public function getCongeApproveResonsableRH()
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select(
                'congés.*',
                'users.id as user_id',
                'users.nom',
                'users.prenom',
                'users.photo',
                'users.nb_jour_conge'
            )
            ->orderBy('congés.id', 'desc')
            ->get();

        foreach ($conges as $c) {
            if ($c->status2 === 'accepté' && $c->status === 'accepté') {
                $c->resultat = 'accepté';
            } else {
                $c->resultat = null;
            }
        }

        return response()->json(
            $conges->where('resultat', 'accepté')->values()
        );
    }

    public function getCongeRefuseResponsableRH()
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select(
                'congés.*',
                'users.id as user_id',
                'users.nom',
                'users.prenom',
                'users.photo',
                'users.nb_jour_conge'
            )
            ->orderBy('congés.id', 'desc')
            ->get();
        foreach ($conges as $c) {
            if ($c->status == 'refusé' || $c->status2 == 'refusé') {
                $c->resultat = 'refusé';
            }
        }

        return response()->json(
            $conges->where('resultat', 'refusé')->values()
        );
    }

    public function getResultByUser($user_id)
    {
        $conges = congé::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($conges as $c) {
            if (Carbon::parse($c->dateFin)->isPast()) {

                $user = User::find($c->user_id);
                if ($user && $user->enConge == true) {
                    $user->enConge = false;
                    $user->save();
                }
            }
            if ($c->status === 'attente' && Carbon::parse($c->dateDebut)->isPast()) {
                $c->status = 'refusé';
            }
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
            if ($c->status2 === 'attente' && Carbon::parse($c->dateDebut)->isPast()) {
                $c->status2 = 'refusé';
            }
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

    public function getCongeAttenteAgentRH()
    {
        $conges = congé::where('congés.status', 'attente')
            ->join('users', 'users.id', '=', 'congés.user_id')
            ->whereIn('users.role', ['chefProjet', 'employee'])
            ->select(
                'congés.*',
                'users.id as user_id',
                'users.nom',
                'users.prenom',
                'users.photo',
                'users.nb_jour_conge'
            )->orderBy('congés.id', 'desc')
            ->get();

        foreach ($conges as $c) {
            if (
                $c->status === 'attente' &&
                Carbon::parse($c->dateDebut)->isPast()
            ) {
                $c->status = 'refusé';
            }
        }

        return response()->json($conges);
    }

    // public function getCongeApproveAgentRH()
    // {
    //     $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
    //         ->whereIn('users.role', ['chefProjet', 'employee'])
    //         ->where('congés.status', 'accepté')
    //         ->where('congés.status2', 'accepté')
    //         ->select(
    //             'congés.*',
    //             'users.id as user_id',
    //             'users.nom',
    //             'users.prenom',
    //             'users.photo',
    //             'users.nb_jour_conge'
    //         )->orderBy('congés.id', 'desc')
    //         ->get();

    //     return response()->json($conges);
    // }
    public function getCongeApproveAgentRH()
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->whereIn('users.role', ['chefProjet', 'employee'])
            ->select(
                'congés.*',
                'users.id as user_id',
                'users.nom',
                'users.prenom',
                'users.photo',
                'users.nb_jour_conge'
            )
            ->orderBy('congés.id', 'desc')
            ->get();

        foreach ($conges as $c) {

            if ($c->status === 'accepté' || $c->status2 === 'accepté') {
                $c->resultat = 'accepté';
            }
        }

        return response()->json(
            $conges->where('resultat', 'accepté')->values()
        );
    }

    // public function getCongeRefuseAgentRH()
    // {
    //     $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
    //         ->whereIn('users.role', ['chefProjet', 'employee'])
    //         ->where('congés.status', 'refusé')
    //         ->where('congés.status2', 'refusé')
    //         ->select(
    //             'congés.*',
    //             'users.id as user_id',
    //             'users.nom',
    //             'users.prenom',
    //             'users.photo',
    //             'users.nb_jour_conge'
    //         )->orderBy('congés.id', 'desc')
    //         ->get();

    //     return response()->json($conges);
    // }
    public function getCongeRefuseAgentRH()
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->whereIn('users.role', ['chefProjet', 'employee'])
            ->select(
                'congés.*',
                'users.id as user_id',
                'users.nom',
                'users.prenom',
                'users.photo',
                'users.nb_jour_conge'
            )
            ->orderBy('congés.id', 'desc')
            ->get();

        foreach ($conges as $c) {

            if ($c->status === 'refusé' || $c->status2 === 'refusé') {
                $c->resultat = 'refusé';

            }
        }

        return response()->json(
            $conges->where('resultat', 'refusé')->values()
        );
    }
}
