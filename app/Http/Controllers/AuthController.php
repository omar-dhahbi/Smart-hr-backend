<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Mail\SignupEmail;
use Illuminate\Validation\Rule;

use App\Mail\Restarpasword;

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
                'error' => $validator->errors()
            ], 401);
        }
        $randomPassword = Str::random(6);
        $user = new User();
        $user->nom = $request->nom;
        $user->prenom = $request->prenom;
        $user->email = $request->email;
        $user->password = Hash::make($randomPassword);
        $user->verif_email = false;
        $user->status = true;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('/images'), $filename);
            $user->photo = "/images/" . $filename;
        }
                $user->date_naissance = $request->date_naissance ;

        if ($request->hasFile('Contrat')) {
            $file = $request->file('Contrat');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('/contrat'), $filename);
            $user->Contrat = "/contrat/" . $filename;
        }
        $user->prix_heure = $request->prix_heure;
        $user->role = $request->role ;
        $user->departement_id = $request->departement_id;


        $user->grade = $request->grade;
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
            'data' => $user
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
                'error' => $validator->errors()
            ], 401);
        }
        if (!$token = auth()->attempt($validator->validated())) {
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
            'expired' => null, // token infini, pas d'expiration
            'role' => auth()->user()->role,
            // 'first_login' => $user->first_login
        ]);
    }


   public function logout(){
        try {
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'User logged out successfully'
                ],
                Response::HTTP_OK
            );
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out'
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
                'body' => 'Code de vérification : ' . $code,
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

        if (!$user) {
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

        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }
            if ($user->verif_email == 1) {
        return response()->json([
            'error' => 'Compte déjà vérifié'
        ], 410);
    }

        if (!$user->verif_email) {
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

    if (!$user) {
        return response()->json(['error' => "Utilisateur introuvable"], 404);
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
                'error' => $validator->errors()
            ], 401);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }
          $user->nom = $request->nom;
        $user->prenom = $request->prenom;
            $user->email = $request->email;
        if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('/images'), $filename);
        $user->photo = "/images/" . $filename;
    }

        $user->save();
        return response()->json(['user' => $user], 200);
    }
  public function ouvrirSession()
{
    $user = auth()->user();

if ($user->role !== 'employee' && $user->role !== 'RH') {
        return response()->json(['error' => 'user non utiliser'], 403);
    }

    if ($user->session_ouverte && !$user->session_fermee) {
        return response()->json(['error' => 'Session déjà ouverte'], 400);
    }

    $user->session_ouverte = now();
    $user->session_fermee = null;
    $user->save();

    return response()->json([
        'message' => 'Session ouverte avec succès',
        'heure_debut' => $user->session_ouverte
    ]);
}




public function fermerSession()
{
    $user = auth()->user();

    if (!$user->session_ouverte) {
        return response()->json(['error' => 'Aucune session ouverte'], 400);
    }

    if ($user->session_fermee) {
        return response()->json(['error' => 'Session déjà fermée'], 400);
    }

    $user->session_fermee = now();

    // 🔹 Calcul heures travaillées
    $heures = Carbon::parse($user->session_ouverte)
        ->diffInMinutes($user->session_fermee) / 60;

    $heures = round($heures, 2);

    $user->nb_heure_par_jour = $heures;

    // 🔹 Salaire normal (max 8h)
    if ($heures <= 8) {
        $salaireJour = $heures * $user->prix_heure;
    } else {

        // 🔹 8h normales
        $salaireNormal = 8 * $user->prix_heure;

        // 🔹 Heures supplémentaires
        $heuresSupp = $heures - 8;

        // 🔹 Bonus heure sup = prix_heure normal
        $salaireSupp = $heuresSupp * $user->prix_heure;

        $salaireJour = $salaireNormal + $salaireSupp;
    }

    // 🔹 Ajouter au salaire total
    $user->salaire += $salaireJour;

    $user->jours_presence += 1;
    $user->derniere_presence = now()->toDateString();

    $user->save();

    return response()->json([
        'message' => 'Session fermée avec succès',
        'heures_travaillees' => $heures,
        'gain_du_jour' => $salaireJour,
        'salaire_total' => $user->salaire
    ]);
}

public function Absence()
{
    $today = now()->toDateString();
    $employees = User::where('role', 'employee')->get();
    foreach ($employees as $emp) {
        if ($emp->derniere_presence != $today) {
            $emp->jours_absence += 1;
            $penalite = $emp->prix_heure * 8;
            $emp->salaire -= $penalite;
            if ($emp->salaire < 0) {
                $emp->salaire = 0;
            }
            $emp->save();
        }
    }
    return response()->json(['message' => 'Absences vérifiées']);
}
    public function resetSalaireMensuel()
    {
        User::where('role', 'employee')->update([
            'salaire' => 0,
            'jours_absence' => 0,
            'jours_presence' => 0
        ]);

        return response()->json(['message' => 'Reset mensuel effectué']);
    }

public function absenceEmployee($id)
{
    $user = User::find($id);

    return response()->json([
        'nom' => $user->nom,
        'absence' => $user->jours_absence,
        'presence' => $user->jours_presence
    ]);
}
    public function updatePassword1(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json(['error' => 'User not found'], 404);
        }
        if (!Hash::check($request->current_password, $user->password)) {
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
    public function index()
    {
        $employees = User::where('role', 'employee')->get();
        return response()->json($employees);
    }
      public function getData()
    {
        $users = User::whereIn('role', ['employee', 'RH'])->get();
        return response()->json($users);
    }
    public function  statUser(){
    {
        $employees = User::where('role', 'employee')->count();
        return response()->json(['count' => $employees]);
    }
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
        'session_fermee' => $user->session_fermee ? true : false
    ]);
}


}
