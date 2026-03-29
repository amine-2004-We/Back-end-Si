<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(
            [
                'user' => $user,
                'token' => $token,
                'name' => $user->name
            ], 201);
    }


    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Connexion utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1Qi..."),
     *             @OA\Property(property="name", type="string", example="Jean Dupont")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Identifiants invalides")
     * )
     */
    public function login(Request $request)
    {
        $emailInput = strtolower(trim($request->email));

        $user = User::whereRaw('LOWER(email) = ?', [$emailInput])->first();

        if (!$user) {
            return response()->json(['error' => 'Identifiants invalides (email)'], 401);
        }

        if (!\Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Identifiants invalides (password)'], 401);
        }

        if (!$token = JWTAuth::fromUser($user)) {
            return response()->json(['error' => 'Impossible de générer le token'], 500);
        }

        return response()->json([
            'token' => $token,
            'name' => $user->name,
            'roles' => $user->getRoleNames(),
        ]);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ]);
    }

    public function currentCollaborator(Request $request)
    {
        $user = $request->user();

        $collaborator = $user->collaborator;

        if (!$collaborator) {
            return response()->json([
                'error' => 'No collaborator found for this user'
            ], 404);
        }

        return response()->json($collaborator);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'error' => 'Le mot de passe actuel est incorrect'
            ], 400);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Mot de passe changé avec succès'
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}

