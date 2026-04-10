<?php

namespace App\Http\Controllers;

use App\Mail\Restarpasword;
use App\Mail\SignupEmail;
use App\Models\fiches_paie;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'nom' => 'required|alpha',
            'prenom' => 'required|alpha',
            'email' => 'required|email|unique:users',
            'date_naissance' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }
        $randomPassword = Str::random(6);
        $user = new User;
        $user->nom = $request->nom;
        $user->prenom = $request->prenom;
        $user->email = $request->email;
        $user->password = Hash::make($randomPassword);
        $user->verif_email = false;
        $user->status = true;

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
        // $user->departement_id = $request->departement_id;

        // $user->grade = $request->grade;
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
        if ($user->role !== 'employee' && $user->role !== 'agentRh' && $user->role !== 'chefProjet') {
            return response()->json(['error' => 'user non utiliser'], 403);
        }
        if ($user->session_ouverte && ! $user->session_fermee) {
            return response()->json(['error' => 'Session déjà ouverte'], 400);
        }
        $now = Carbon::now();

        $heureDebut = Carbon::today()->setTime(8, 0, 0);
        $heureTolerance = Carbon::today()->setTime(8, 15, 0);

        if ($now->lessThanOrEqualTo($heureTolerance)) {
            $user->session_ouverte = $heureDebut;
        } else {
            $user->session_ouverte = $now;
        }
        $user->session_fermee = null;
        // $user->session_fermee = null;

        // 📅 Gestion présence (une seule fois par jour)
        $today = now()->toDateString();

        if ($user->derniere_presence !== $today) {
            $user->jours_presence += 1;
            $user->derniere_presence = $today;
        }
        $user->save();

        return response()->json([
            'message' => 'Session ouverte avec succès',
            'heure_debut' => $user->session_ouverte,
        ]);
    }

    public function fermerSession()
    {
        $user = auth()->user();

        // Vérifier si session ouverte
        if (! $user->session_ouverte) {
            return response()->json(['error' => 'Aucune session ouverte'], 400);
        }

        // Vérifier si déjà fermée
        if ($user->session_fermee) {
            return response()->json(['error' => 'Session déjà fermée'], 400);
        }

        $now = Carbon::now();

        // ⏰ Heure max de fermeture (17:15)
        $heureMaxFermeture = Carbon::today()->setTime(17, 15, 0);

        // ✅ Si dépasse 17:15 → on bloque à 17:15
        if ($now->greaterThan($heureMaxFermeture)) {
            $user->session_fermee = $heureMaxFermeture;
        } else {
            $user->session_fermee = $now;
        }

        $debut = Carbon::parse($user->session_ouverte);
        $fin = Carbon::parse($user->session_fermee);

        // 🧮 Calcul minutes
        $minutesTravail = $debut->diffInMinutes($fin);

        // 🍽 Pause déjeuner
        $debutPause = Carbon::today()->setTime(12, 0, 0);
        $finPause = Carbon::today()->setTime(13, 0, 0);

        if ($debut < $finPause && $fin > $debutPause) {

            $pauseDebut = $debut->copy()->max($debutPause);
            $pauseFin = $fin->copy()->min($finPause);

            $minutesPause = $pauseDebut->diffInMinutes($pauseFin);

            $minutesTravail -= $minutesPause;
        }

        // ⏱ Convertir en heures
        $heures = round($minutesTravail / 60, 2);

        // 💾 Sauvegarde
        $user->nb_heure_par_jour = $heures;

        $gainJour = $user->prix_heure * $heures;

        $user->salaire += $gainJour;
        // $user->jours_presence += 1;
        $user->derniere_presence = now()->toDateString();

        $user->save();

        return response()->json([
            'message' => 'Session fermée',
            'heures_travaillees' => $heures,
            'gain_du_jour' => $gainJour,
            'salaire_total' => $user->salaire,
        ]);
    }

    public function pauseDejeuner()
    {
        $now = Carbon::now();

        $debutPause = Carbon::today()->setTime(12, 0, 0);
        $finPause = Carbon::today()->setTime(13, 0, 0);

        if ($now->between($debutPause, $finPause)) {
            return response()->json([
                'message' => 'Pause déjeuner en cours',
            ]);
        }

        return response()->json([
            'message' => 'Temps de travail',
        ]);
    }

    public function absenceEmployee($id)
    {
        $user = User::find($id);

        return response()->json([
            'nom' => $user->nom,
            'absence' => $user->jours_absence,
            'presence' => $user->jours_presence,
        ]);
    }

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

        return response()->json([
            'session_ouverte' => $user->session_ouverte ? true : false,
            'session_fermee' => $user->session_fermee ? true : false,
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

        if (! in_array($user->role, ['employee', 'chefProjet'])) {
            return response()->json(['error' => 'Rôle invalide'], 403);
        }

        $now = Carbon::now();

        // éviter doublon fiche paie par mois
        $existing = fiches_paie::where('user_id', $user->id)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Fiche déjà générée ce mois',
            ]);
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
}
