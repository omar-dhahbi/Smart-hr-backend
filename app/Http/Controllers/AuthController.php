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
            // 'password' => 'required|confirmed|min:6',
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
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('/images'), $filename);
            $user->photo = "/images/" . $filename;
        }
        if ($request->hasFile('Contrat')) {
            $file = $request->file('Contrat');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('/contrat'), $filename);
            $user->Contrat = "/contrat/" . $filename;
        }
        $user->prix_heure = $request->prix_heure;
        $user->role = $request->role ;
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

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'token' => $token,
            'type' => 'bearer',
            'expired' => auth()->factory()->getTTL()*60,
            'role' => auth()->user()->role,
            'first_login' => $user->first_login
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
     public function ouvrirsession(){
        $user = auth()->user();
        if ($user->role !== 'employee') {
            return response()->json(['error' => 'Action réservée aux employés'], 403);
        }

        // $user->connecte = true;
        $user->session_ouverte = now();
        $user->save();

        return response()->json(['message' => 'Session ouverte avec succès', 'user' => $user]);

    }
    public function fermerSession(){
         $user = auth()->user();
        if ($user->role !== 'employee') {
            return response()->json(['error' => 'Action réservée aux employés'], 403);
        }
        $user->session_fermee = now();
        if ($user->session_ouverte) {
            $heures = \Carbon\Carbon::parse($user->session_ouverte)
                ->diffInMinutes($user->session_fermee) / 60;
            $user->nb_heure_par_jour = round($heures, 2);
            $user->salaire += $user->prix_heure * $user->nb_heure_par_jour;
        }

        $user->save();

        return response()->json(['message' => 'Session fermée avec succès', 'user' => $user]);
    }
    // public function absence(){
    //     $yesterday = Carbon::yesterday()->format('Y-m-d');
    //     $employees = User::where('role', 'employee')->get();
    //     foreach ($employees as $emp){
    //         $lastSession = $emp->updated_at->format('Y-m-d');
    //         if ($lastSession != $yesterday) {
    //             if ($emp->nb_jour_conge > 0) {
    //                 $emp->nb_jour_conge -= 1;
    //             } else {
    //                 $user->salaire -= $user->prix_heure * $user->nb_heure_par_jour;
    //                  if ($emp->salaire < 0) {
    //                     $emp->salaire = 0;
    //                 }
    //             }
    //             $emp->save();
    //         }
    //     }
    //     return response()->json(['message' => 'Vérification d’absence effectuée']);
    // }
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
         $user->first_login = false;
        $user->save();
        return response()->json(['message' => 'Password updated successfully'], 200);
    }
    public function index()
    {
        $employees = User::where('role', 'employee')->get();
        return response()->json($employees);
    }
    public function  statUser(){
    {
    $employees = User::where('role', 'employee')->count();
    return response()->json(['count' => $employees]);
    }
}


}
