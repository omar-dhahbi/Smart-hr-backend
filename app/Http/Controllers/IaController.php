<?php

namespace App\Http\Controllers;

use App\Models\sessions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IaController extends Controller
{
    public function chatbot(Request $request)
    {

        $request->validate([
            'message' => 'required|string',
            'user_id' => 'required',
        ]);

        // récupérer user
        $user = User::find($request->user_id);

        if (! $user) {
            return response()->json([
                'error' => 'Utilisateur introuvable',
            ], 404);
        }

        // dernière session
        $session = sessions::where('user_id', $user->id)
            ->latest()
            ->first();

        $nb_heures = 0;

        if ($session) {
            $nb_heures = $session->nb_heures;
        }

        // envoyer vers FastAPI
        $response = Http::post(
            'http://192.168.1.50:5000/chatbot',
            [
                'message' => $request->message,

                'user' => [

                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'role' => $user->role,

                    'salaire' => $user->salaire,

                    'jours_conge' => $user->nb_jour_conge,

                    'jours_presence' => $user->jours_presence,

                    'jours_absence' => $user->jours_absence,

                    'nb_heures' => $nb_heures,
                ],
            ]
        );

        if ($response->failed()) {

            return response()->json([
                'status' => $response->status(),
                'body' => $response->body(),
            ], 500);
        }

        return response()->json([
            'question' => $request->message,
            'response_ai' => $response->json()['response'],
        ]);
    }
}
