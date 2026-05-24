<?php

namespace App\Http\Controllers;

use App\Models\AvanceSalire;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AvanceSalireController extends Controller
{
    public function demandeAvance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'SalaireAvance' => 'required|numeric',
            'Description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);
        // if ($request->SalaireAvance >= $user->salaire) {
        //     return response()->json([
        //         'error' => 'Veuillez demander un montant inférieur à votre salaire.',
        //     ], 422);
        // }
        $maxAvance = $user->salaire * 0.3;
        if ($request->SalaireAvance > $maxAvance) {
            return response()->json([
                'error' => "L'avance de salaire ne doit pas dépasser 30% du salaire ({$maxAvance}).",
            ], 422);
        }

        $exists = AvanceSalire::where('user_id', $request->user_id)
            ->where('created_at', '>=', now()->subDay())
            ->exists();
        if ($exists) {
            return response()->json([
                'error' => 'Vous avez déjà demandé une avance dans les dernières 24h.',
            ], 409);
        }
        $avance = new AvanceSalire;
        $avance->user_id = $request->user_id;
        $avance->SalaireAvance = $request->SalaireAvance;
        $avance->Description = $request->Description;
        $avance->status = 'attente';
        $avance->status2 = 'attente';

        $avance->save();
        $rh = User::where('role', 'agentRh')->get();
        foreach ($rh as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Nouvelle demande d'avance de salaire de {$user->nom} {$user->prenom}",
                'type' => 'demande-avance-salaire',
            ]);
        }

        return response()->json([
            'message' => 'Demande avance envoyée avec succès.',
        ], 201);
    }

    public function preApprove($id)
    {
        $avance = AvanceSalire::find($id);
        if (! $avance) {
            return response()->json(['error' => 'Demande non trouvée'], 404);
        }
        $avance->status = 'accepté';
        $avance->save();
        $responsables = User::where('role', 'ResponsableRH')->get();
        foreach ($responsables as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => 'Demande avance salaire pré-acceptée',
                'type' => 'avance-salaire',
            ]);
        }
    }

    public function preRefuse($id)
    {
        $avance = AvanceSalire::find($id);

        if (! $avance) {
            return response()->json(['error' => 'Demande non trouvée'], 404);
        }

        $avance->status = 'refusé';
        $avance->status2 = 'refusé';
        $avance->save();
        Notification::create([
            'user_id' => $avance->user_id,
            'message' => 'Votre demande avance de salaire est refusée',
            'type' => 'avance-salaire',
        ]);

        return response()->json(['message' => 'Refus effectué']);
    }

    public function finalApprove($id)
    {
        $avance = AvanceSalire::find($id);

        if (! $avance) {
            return response()->json(['error' => 'Demande non trouvée'], 404);
        }
        if ($avance->status !== 'accepté') {
            return response()->json(['error' => 'Pré-approbation requise'], 400);
        }
        $user = User::find($avance->user_id);

        if (! $user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }
        $user->salaire = $user->salaire - $avance->SalaireAvance;
        $user->save();
        $avance->status2 = 'accepté';
        $avance->save();
        Notification::create([
            'user_id' => $user->id,
            'message' => 'Votre avance de salaire a été acceptée et appliquée',
            'type' => 'avance-salaire',
        ]);
        $rhUsers = User::where('role', 'agentRh')->get();

        foreach ($rhUsers as $rh) {
            Notification::create([
                'user_id' => $rh->id,
                'message' => "Avance de salaire validée pour {$user->nom} {$user->prenom}",
                'type' => 'avance-salaire',
            ]);
        }

        return response()->json([
            'message' => 'Avance validée et salaire mis à jour',
        ]);
    }

    public function finalRefuse($id)
    {
        $avance = AvanceSalire::find($id);

        if (! $avance) {
            return response()->json(['error' => 'Demande non trouvée'], 404);
        }

        $avance->status2 = 'refusé';
        $avance->save();
        Notification::create([
            'user_id' => $avance->user_id,
            'message' => 'Votre avance de salaire a été refusée définitivement',
            'type' => 'avance-salaire',
        ]);
        $rhUsers = User::where('role', 'agentRh')->get();

        foreach ($rhUsers as $rh) {
            Notification::create([
                'user_id' => $rh->id,
                'message' => 'Avance de salaire refusée définitivement',
                'type' => 'avance-salaire',
            ]);
        }

        return response()->json(['message' => 'Refus final effectué']);
    }

    public function AvanceSalaireAgentRH(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'SalaireAvance' => 'required|numeric|min:1',
            'Description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        // if ($request->SalaireAvance >= $user->salaire) {
        //     return response()->json([
        //         'error' => 'Veuillez demander un montant inférieur à votre salaire.',
        //     ], 422);
        // }
        $maxAvance = $user->salaire * 0.3;
        if ($request->SalaireAvance > $maxAvance) {
            return response()->json([
                'error' => "L'avance de salaire ne doit pas dépasser 30% du salaire ({$maxAvance}).",
            ], 422);
        }
        $exists = AvanceSalire::where('user_id', $request->user_id)
            ->where('created_at', '>=', now()->subDay())
            ->exists();
        if ($exists) {
            return response()->json([
                'error' => 'Vous avez déjà demandé une avance dans les dernières 24h.',
            ], 409);
        }

        $avance = new AvanceSalire;
        $avance->user_id = $request->user_id;
        $avance->SalaireAvance = $request->SalaireAvance;
        $avance->Description = $request->Description;
        $avance->status = null;
        $avance->status2 = 'attente';
        $avance->save();
        $receivers = User::where('role', 'ResponsableRH')->get();
        foreach ($receivers as $r) {
            Notification::create([
                'user_id' => $r->id,
                'message' => "Nouvelle demande d'avance de salaire de {$user->nom} {$user->prenom}",
                'type' => 'demande-avance-salaire',
            ]);
        }

        return response()->json([
            'message' => 'Demande avance envoyée avec succès.',
        ], 201);

    }

    public function getAvanceAttenteAgentRH()
    {
        return AvanceSalire::join('users', 'users.id', '=', 'avance_salires.user_id')
            ->where('avance_salires.status', 'attente')
            ->whereIn('users.role', ['employee', 'chefProjet'])
            ->select('avance_salires.*', 'users.nom', 'users.prenom', 'users.photo')
            ->orderBy('avance_salires.id', 'desc')
            ->get();
    }

    public function getAvanceApproveAgentRH()
    {
        $avances = AvanceSalire::join('users', 'users.id', '=', 'avance_salires.user_id')
            ->select('avance_salires.*', 'users.nom', 'users.prenom', 'users.photo')
            ->orderBy('avance_salires.id', 'desc')
            ->get();

        foreach ($avances as $a) {
            if ($a->status == 'accepté' || $a->status2 == 'accepté') {
                $a->resultat = 'accepté';
            } elseif ($a->status == 'refusé' || $a->status2 == 'refusé') {
                $a->resultat = 'refusé';
            } else {
                $a->resultat = 'attente';
            }
        }

        return response()->json(
            $avances->where('resultat', 'accepté')->values()
        );
    }

    public function getAvanceRefuseAgentRH()
    {
        $avances = AvanceSalire::join('users', 'users.id', '=', 'avance_salires.user_id')
            ->select('avance_salires.*', 'users.nom', 'users.prenom', 'users.photo')
            ->orderBy('avance_salires.id', 'desc')
            ->get();

        foreach ($avances as $a) {
            if ($a->status == 'refusé' || $a->status2 == 'refusé') {
                $a->resultat = 'refusé';
            } elseif ($a->status == 'accepté' && $a->status2 == 'accepté') {
                $a->resultat = 'accepté';
            } else {
                $a->resultat = 'attente';
            }
        }

        return response()->json(
            $avances->where('resultat', 'refusé')->values()
        );
    }

    public function getAvanceApproveResponsable()
    {
        $data = AvanceSalire::join('users', 'users.id', '=', 'avance_salires.user_id')
            ->select('avance_salires.*', 'users.nom', 'users.prenom', 'users.photo')
            ->orderBy('avance_salires.id', 'desc')
            ->get();

        foreach ($data as $a) {
            if ($a->status2 == 'accepté' && $a->status == 'accepté') {
                $a->resultat = 'accepté';
            }
        }

        return response()->json(
            $data->where('resultat', 'accepté')->values()
        );
    }

    public function getAvanceAttenteResponsable()
    {
        $data = AvanceSalire::join('users', 'users.id', '=', 'avance_salires.user_id')
            ->select(
                'avance_salires.*',
                'users.id as user_id',
                'users.nom',
                'users.prenom',
                'users.photo'
            )
            ->where(function ($query) {

                $query->where(function ($q) {
                    $q->whereIn('users.role', ['employee', 'chefProjet'])
                        ->where('avance_salires.status', 'accepté')
                        ->where('avance_salires.status2', 'attente');
                })
                    ->orWhere(function ($q) {
                        $q->where('users.role', 'agentRh')
                            ->where('avance_salires.status2', 'attente');
                    });

            })
            ->orderBy('avance_salires.id', 'desc')->get();

        return response()->json($data);
    }

    public function getAvanceRefuseResponsable()
    {
        $data = AvanceSalire::join('users', 'users.id', '=', 'avance_salires.user_id')
            ->select('avance_salires.*', 'users.nom', 'users.prenom', 'users.photo')
            ->orderBy('avance_salires.id', 'desc')
            ->get();

        foreach ($data as $a) {
            if ($a->status == 'refusé' || $a->status2 == 'refusé') {
                $a->resultat = 'refusé';
            }
        }

        return response()->json(
            $data->where('resultat', 'refusé')->values()
        );
    }

    public function getResultByUser($user_id)
    {
        $avances = AvanceSalire::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($avances as $a) {

            if ($a->status == 'refusé' || $a->status2 == 'refusé') {
                $a->resultat = 'refusé';
            } elseif ($a->status == 'accepté' && $a->status2 == 'accepté') {
                $a->resultat = 'accepté';
            } else {
                $a->resultat = 'attente';
            }
        }

        return response()->json($avances);
    }

    public function getResultByUserAgentRh($user_id)
    {
        $avances = AvanceSalire::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($avances as $a) {

            if ($a->status2 == 'refusé') {
                $a->resultat = 'refusé';
            } elseif ($a->status2 == 'accepté') {
                $a->resultat = 'accepté';
            } else {
                $a->resultat = 'attente';
            }
        }

        return response()->json($avances);
    }
}
