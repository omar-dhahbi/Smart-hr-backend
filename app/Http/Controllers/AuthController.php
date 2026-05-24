<?php

namespace App\Http\Controllers;

use App\Mail\Restarpasword;
use App\Mail\SignupEmail;
use App\Models\fiches_paie;
use App\Models\sessions;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cin' => 'required|numeric|digits:8',
            'nom' => 'required|alpha',
            'prenom' => 'required|alpha',
            'email' => 'required|email',
            'date_naissance' => 'required|date',
            'Genre' => 'required|in:Male,Female',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }

        if (User::where('cin', $request->cin)->exists()) {
            return response()->json([
                'error' => 'cette Carte identité  est exist',
            ], 401);
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'error' => 'Email est exist',
            ], 401);
        }
        $randomPassword = Str::random(6);
        $user = new User;
        $user->cin = $request->cin;
        $user->nom = $request->nom;
        $user->prenom = $request->prenom;
        $user->email = $request->email;
        $user->password = Hash::make($randomPassword);
        $user->verif_email = false;
        $user->status = true;
        $user->Genre = $request->Genre;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('/images'), $filename);
            $user->photo = '/images/'.$filename;
        }
        $user->date_naissance = $request->date_naissance;

        if ($request->hasFile('Contrat')) {
            $file = $request->file('Contrat');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('/contrat'), $filename);
            $user->Contrat = '/contrat/'.$filename;
        }
        $user->prix_heure = $request->prix_heure;
        $user->role = $request->role;
        $user->save();
        $details = [
            'title' => 'Vérification de votre compte',
            'body' => 'Suite à votre inscription, merci de vérifier votre compte.',
            'email' => $request->email,
            'password' => $randomPassword,
            'id' => $user->id,
        ];

        Mail::to($request->email)->send(new SignupEmail($details));

        return response()->json([
            'status' => true,
            'message' => 'Utilisateur enregistré avec succès.',
            'data' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {

            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'E-mail ou mot de passe incorrect !!!!!'], 401);
        }
        $user = Auth::user();
        if ($user->verif_email == 0) {
            return response()->json(['error' => "Vous n'avez pas accès à la connexion"], 401);
        }
        if ($user->status == 0) {
            return response()->json(['error' => "Vous n'avez pas accès à la connexion"], 401);
        }

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'token' => $token,
            'type' => 'bearer',
            'expired' => auth()->factory()->getTTL() * 60,
            'role' => auth()->user()->role,
            // 'first_login' => $user->first_login
        ]);
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'User logged out successfully',
                ],
                Response::HTTP_OK
            );
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out',
            ], 404);
        }

    }

    public function restarpassword($email)
    {
        $code = Str::random(5);
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->code = $code;
            // $user->code_expire_at = now()->addMinutes(10);

            $user->save();

            $details = [
                'title' => 'Réinitialisation de mot de passe',
                'body' => 'Code de vérification : '.$code,
                'code' => $code,
                'id' => $user->id,
            ];

            Mail::to($email)->send(new Restarpasword($details));

            return response()->json(['message' => 'Code envoyé à votre email']);
        }

        return response()->json(['error' => 'Email non trouvé'], 401);
    }

    public function updatepassword(Request $request, $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['error' => 'Utilisateur non trouvé']);
        }

        if ($user->code === $request->code) {
            if ($request->new_password !== $request->password_confirm) {
                return response()->json(['error' => 'Les mots de passe ne correspondent pas'], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->code = null;
            $user->save();

            return response()->json(['message' => 'Mot de passe mis à jour avec succès'], 200);
        }

        return response()->json(['error' => 'Code incorrect'], 404);
    }

    public function verifMail($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }
        if ($user->verif_email == 1) {
            return response()->json([
                'error' => 'Compte déjà vérifié',
            ], 410);
        }

        if (! $user->verif_email) {
            $user->verif_email = true;
            $user->email_verified_at = now();
            $user->save();

            return response()->json(['message' => 'Compte vérifié avec succès']);
        }

        return response()->json(['error' => 'Compte déjà vérifié'], 400);
    }

    public function getUserById($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        return response()->json($user, 200);
    }

    public function UpdateUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }
        $user = User::find($id);
        if (! $user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }
        $user->nom = $request->nom;
        $user->prenom = $request->prenom;
        $user->email = $request->email;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('/images'), $filename);
            $user->photo = '/images/'.$filename;
        }
        $user->save();

        return response()->json(['user' => $user], 200);
    }

    public function ouvrirSession()
    {
        $user = auth()->user();

        if (! in_array($user->role, ['employee', 'agentRh', 'chefProjet'])) {
            return response()->json(['error' => 'Utilisateur non autorisé'], 403);
        }

        $existing = sessions::where('user_id', $user->id)
            ->whereNull('heure_sortie')
            ->latest()
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Session déjà ouverte'], 400);
        }

        $now = Carbon::now();

        $heureDebut = Carbon::today()->setTime(8, 0, 0);
        $heureTolerance = Carbon::today()->setTime(8, 15, 0);

        if ($now->lessThanOrEqualTo($heureTolerance)) {
            $heureEntree = $heureDebut;
        } else {
            $heureEntree = $now;
        }

        $user->session_ouverte = $heureEntree;
        $user->session_fermee = null;

        $today = $now;

        if ($user->derniere_presence !== $today) {
            $user->jours_presence += 1;
            $user->derniere_presence = $today;
        }

        $user->save();

        sessions::create([
            'user_id' => $user->id,
            'heure_entree' => $heureEntree,
            'date' => $today,
        ]);

        return response()->json([
            'message' => 'Session ouverte avec succès',
            'heure_entree' => $heureEntree->format('Y-m-d H:i:s'),
        ]);
    }

    public function fermerSession()
    {
        $user = auth()->user();

        $session = sessions::where('user_id', $user->id)
            ->whereNull('heure_sortie')
            ->latest()
            ->first();

        if (! $session) {
            return response()->json(['error' => 'Aucune session ouverte'], 400);
        }

        $now = Carbon::now();
        $heureMax = Carbon::today()->setTime(17, 15, 0);

        $heureSortie = $now->greaterThan($heureMax) ? $heureMax : $now;

        $debut = Carbon::parse($session->heure_entree);
        $fin = Carbon::parse($heureSortie);

        $minutes = $debut->diffInMinutes($fin);

        // pause déjeuner
        $pauseStart = Carbon::today()->setTime(12, 0, 0);
        $pauseEnd = Carbon::today()->setTime(13, 0, 0);

        if ($debut < $pauseEnd && $fin > $pauseStart) {
            $pauseMinutes = $debut->copy()->max($pauseStart)
                ->diffInMinutes($fin->copy()->min($pauseEnd));

            $minutes -= $pauseMinutes;
        }

        $heures = round($minutes / 60, 2);

        $gain = ($user->prix_heure ?? 0) * $heures;

        $session->update([
            'heure_sortie' => $heureSortie,
            'nb_heures' => $heures,
            'gain' => $gain,
        ]);

        $user->session_fermee = $heureSortie;
        $user->nb_heure_par_jour = $heures;
        $user->salaire = $user->salaire + $gain;
        $user->save();

        return response()->json([
            'message' => 'Session fermée',
            'heures' => $heures,
            'gain' => $gain,
            'salaire_total' => $user->salaire,
        ]);
    }

    public function pauseDejeuner()
    {
        $now = Carbon::now();

        $pauseStart = Carbon::today()->setTime(12, 0, 0);
        $pauseEnd = Carbon::today()->setTime(13, 0, 0);

        return response()->json([
            'en_pause' => $now->between($pauseStart, $pauseEnd),
            'heure_actuelle' => $now->toDateTimeString(),
        ]);
    }

    // public function absenceEmployee($id)
    // {
    //     $user = User::find($id);

    //     return response()->json([
    //         'nom' => $user->nom,
    //         'absence' => $user->jours_absence,
    //         'presence' => $user->jours_presence,
    //     ]);
    // }

    public function updatePassword1(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json(['error' => 'User not found'], 404);
        }
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Mot de passe actuel incorrect'], 404);
        }
        if ($request->new_password == $request->current_password) {
            return response()->json(['error' => 'Le nouveau mot de passe ne peut pas être le même que le mot de passe actuel'], 404);
        }
        if ($request->new_password != $request->password_confirm) {
            return response()->json(['error' => 'Le nouveau mot de passe et la confirmation du mot de passe ne correspondent pas'], 404);
        }
        $user->password = Hash::make($request->new_password);
        //  $user->first_login = false;
        $user->save();

        return response()->json(['message' => 'Password updated successfully'], 200);
    }

    public function indexAgenRh()
    {
        $employees = User::whereIn('role', ['employee', 'chefProjet'])->get();

        return response()->json($employees);
    }

    public function indexAdmin()
    {
        $employees = User::whereIn('role', ['employee', 'agentRh', 'chefProjet', 'ResponsableRh'])->get();

        return response()->json($employees);
    }

    public function getDataResponsableRH()
    {
        $users = User::whereIn('role', ['employee', 'agentRh', 'chefProjet'])->get();

        return response()->json($users);
    }

    public function statUser()
    {

        $employees = User::where('role', 'employee')->count();

        return response()->json(['count' => $employees]);

    }

    public function activeAccount($id)
    {
        $user = User::find($id);
        $user->status = true;
        $user->save();

        return response()->json(['message' => 'User banned successfully']);
    }

    public function AccounNotActive($id)
    {
        $user = User::find($id);
        $user->status = false;
        $user->save();

        return response()->json(['message' => 'User unbanned successfully']);
    }

    public function getSessionStatus()
    {
        $user = auth()->user();

        $session = sessions::where('user_id', $user->id)
            ->latest()
            ->first();

        $session_ouverte = null;
        $session_fermee = null;
        $en_cours = false;
        if ($session) {
            $session_ouverte = $session->heure_entree;
            if ($session->heure_sortie) {
                $session_fermee = $session->heure_sortie;
                $en_cours = false;
            } else {
                $session_fermee = null;
                $en_cours = true;
            }
        }

        return response()->json([
            'session_ouverte' => $session_ouverte,
            'session_fermee' => $session_fermee,
            'en_cours' => $en_cours,
        ]);
    }

    public function searchUser(Request $request)
    {
        $query = $request->input('search');
        $users = User::where('nom', 'like', "%$query%")
            ->orWhere('prenom', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->orWhere('role', 'like', "%$query%")
            ->get();

        return response()->json($users);
    }

    public function genererFichePaie($user_id)
    {
        $user = User::find($user_id);

        if (! $user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }
        if (! in_array($user->role, ['employee', 'chefProjet', 'agentRh'])) {
            return response()->json(['error' => 'Rôle invalide'], 403);
        }
        $now = Carbon::now();
        $existing = fiches_paie::where('user_id', $user->id)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->first();
        if ($existing) {
            return response()->json([
                'error' => 'Fiche déjà générée ce mois',
            ], 400);

        }
        $data = [
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'date_naissance' => Carbon::parse($user->date_naissance)->format('Y-m-d'),
            'heures' => $user->nb_heure_par_jour,
            'salaire' => $user->salaire,
            'date' => now()->format('Y-m-d'),
        ];
        $pdf = PDF::loadView('pdf.fiche_paie', $data);
        if (! file_exists(public_path('fiches'))) {
            mkdir(public_path('fiches'), 0777, true);
        }
        $fileName = 'fiche_'.$user->id.'_'.now()->format('YmdHis').'.pdf';
        $path = 'fiches/'.$fileName;

        $pdf->save(public_path($path));

        $fiche = fiches_paie::create([
            'user_id' => $user->id,
            'file' => $path,
        ]);

        return response()->json([
            'message' => 'Fiche générée avec succès',
            'file' => asset($path),
            'data' => $fiche,
        ]);
    }

    public function getFcihePaiParUserid($user_id)
    {
        $fiches = fiches_paie::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($fiches);
    }

    public function historiquePointage($date)
    {
        $query = sessions::join('users', 'sessions.user_id', '=', 'users.id')
            ->whereIn('users.role', ['employee', 'agentRh', 'chefProjet'])
            ->whereDate('sessions.date', $date)
            ->select(
                'users.id',
                'users.nom',
                'users.prenom',
                'users.role',
                'users.photo',
                'sessions.heure_entree',
                'sessions.heure_sortie',
                'sessions.nb_heures',
                'sessions.date'
            )
            ->orderBy('sessions.date', 'desc');

        $data = $query->get();

        return response()->json($data);
    }

    public function historiquePointageAgentRH($date)
    {
        $query = sessions::join('users', 'sessions.user_id', '=', 'users.id')
            ->whereIn('users.role', ['employee', 'chefProjet'])
            ->whereDate('sessions.date', $date)
            ->select(
                'users.id',
                'users.nom',
                'users.prenom',
                'users.role',
                'users.photo',
                'sessions.heure_entree',
                'sessions.heure_sortie',
                'sessions.nb_heures',
                'sessions.date'
            )
            ->orderBy('sessions.date', 'desc');

        $data = $query->get();

        return response()->json($data);
    }

    public function historiquePointageByUser($id)
    {
        $data = sessions::join('users', 'sessions.user_id', '=', 'users.id')
            ->where('users.id', $id)
            ->select(
                'users.id',
                'users.nom',
                'users.prenom',
                'users.photo',
                'sessions.heure_entree',
                'sessions.heure_sortie',
                'sessions.nb_heures',
                'sessions.date'
            )
            ->orderBy('sessions.date', 'desc')
            ->get();

        return response()->json($data);
    }


}
