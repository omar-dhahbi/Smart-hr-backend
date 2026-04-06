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
    // Demande de congé pour un utilisateur classique
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

        // Vérifier qu'il n'y a pas de demande dans les dernières 24h
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

        // Upload photo si existe
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

        // Notifications aux agents RH
        $user = User::find($request->user_id);
        $receivers = User::where('role', 'agentRh')->get();
        foreach ($receivers as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Nouvelle demande de congé de {$user->nom} {$user->prenom}",
            ]);
        }

        return response()->json(['message' => 'Demande de congé effectuée avec succès.'], 201);
    }

    // Demande de congé pour un Agent RH (notif au ResponsableRH)
    public function demandeCongeAgentRH(Request $request)
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

        $conge->status2 = Carbon::parse($request->dateDebut)->isPast() ? 'refusé' : 'attente';
        $conge->enCongé = false;
        $conge->save();

        // Notifications aux ResponsableRH
        $receivers = User::where('role', 'ResponsableRH')->get();
        foreach ($receivers as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Nouvelle demande (AgentRH) de {$user->nom} {$user->prenom}",
            ]);
        }

        return response()->json(['message' => 'Demande envoyée au Responsable RH avec succès.'], 201);
    }

    // Pré-acceptation/refus par Agent RH
    public function preApproveConge($id)
    {
        $conge = congé::find($id);
        if (! $conge) {
            return response()->json(['error' => 'Congé non trouvé'], 404);
        }

        $conge->status = 'accepté';
        $conge->save();

        $receivers = User::whereIn('role', ['employee', 'ChefProjet', 'ResponsableRh'])->get();
        foreach ($receivers as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Congé pré-accepté par Agent RH pour {$conge->user->nom} {$conge->user->prenom}",
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
        ]);

        return response()->json(['message' => 'Refus effectué']);
    }

    // Acceptation/Refus final par Responsable RH
    public function finalApproveConge($id)
    {
        $conge = congé::find($id);
        if (! $conge || $conge->status != 'accepté') {
            return response()->json(['error' => 'Pré-acceptation requise'], 400);
        }

        $conge->status2 = 'accepté';
        $conge->enCongé = true;

        if ($conge->type == 'autre') {
            $user = User::find($conge->user_id);
            $user->nb_jour_conge -= $conge->nbrJour;
            $user->save();
        }

        $conge->save();

        $receivers = User::whereIn('role', ['employee', 'ChefProjet', 'agentRh'])->get();
        foreach ($receivers as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Votre congé de {$conge->user->nom} {$conge->user->prenom} est accepté définitivement",
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

        $receivers = User::whereIn('role', ['employee', 'ChefProjet', 'agentRh'])->get();
        foreach ($receivers as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Votre demande de congé de {$conge->user->nom} {$conge->user->prenom} a été refusée définitivement",
            ]);
        }

        return response()->json(['message' => 'Refus final effectué']);
    }

    // Récupérer les congés par statut pour Responsable RH
    public function getCongeAttenteResponsable(Request $request)
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom')
            ->where('congés.status', 'accepté')
            ->where('congés.status2', 'attente')
            ->orderBy('congés.id', 'desc')
            ->get();

        return response()->json($conges);
    }

    public function getCongeApproveResonsableRH(Request $request)
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom')
            ->where('congés.status', 'accepté')
            ->where('congés.status2', 'accepté')
            ->orderBy('congés.id', 'desc')
            ->get();

        return response()->json($conges);
    }

    public function getCongeRefuseResponsableRH(Request $request)
    {
        $conges = congé::join('users', 'users.id', '=', 'congés.user_id')
            ->select('congés.*', 'users.nom', 'users.prenom')
            ->where('congés.status', 'refusé')
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

    // Récupérer congés par statut pour Agent RH
    public function getcongéAttenteAgentRH()
    {
        $conges = congé::where('congés.status', 'attente')
            ->join('users', 'users.id', '=', 'congés.user_id')
            ->whereIn('users.role', ['chefProjet', 'employee'])
            ->select('congés.*', 'users.nom', 'users.prenom')
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
            ->select('congés.*', 'users.nom', 'users.prenom')
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
            ->select('congés.*', 'users.nom', 'users.prenom')
            ->orderBy('congés.id', 'desc')
            ->get();

        return response()->json($conges);
    }
}
