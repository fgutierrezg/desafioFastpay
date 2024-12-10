<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Merchant;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class MerchantController extends Controller
{

    /**
 * @OA\Post(
 *     path="/auth/login",
 *     summary="Autenticación de comercios",
 *     description="Permite a un comercio autenticarse y obtener un token JWT.",
 *     tags={"Comercios"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", example="merchant@example.com"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Autenticación exitosa",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Credenciales inválidas",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Unauthorized")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Error al procesar la solicitud.")
 *         )
 *     )
 * )
 */
    public function login(Request $request)
    {
        try {
            $merchant = Merchant::where('email', $request->email)->first();

            if (!$merchant || !\Hash::check($request->password, $merchant->password)) {
                Log::error('Error al autenticar comercio: ' . $request->email);
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $token = JWTAuth::fromUser($merchant);

            return response()->json(['token' => $token], 200);
        } catch (\Throwable $th) {
            return response()->json(["message"=>"Error al procesar la solicitud."], 500);
        }

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Merchant::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:250',
            'email' => 'required|email',
            'password' => 'required|string|max:10'
        ]);

        $merchant = Merchant::create($validatedData);

        Log::info('Se ha creado un nuevo comercio: ' . $merchant);

        return response()->json([
            'message' => 'El comercio se ha creado exitosamente.',
            'data' => $merchant
        ], 201); // 201 Created
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $merchant = Merchant::find($id);

        if (!$merchant) {
            return response()->json([
                'message' => 'Comercio NO encontrado.'
            ], 404); // 404 Not Found
        }

        return response()->json([
            'message' => 'Comercio encontrado.',
            'data' => $merchant
        ], 200); // 200 OK
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $merchant = Merchant::find($id);

        if (!$merchant) {
            return response()->json([
                'message' => 'Comercio no encontrado.'
            ], 404);
        }

        $merchant->update($request->all());

        return response()->json([
            'message' => 'El comercio se ha actualizado correctamente.',
            'data' => $merchant
        ], 200); // 200 OK
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $merchant = Merchant::find($id);

        if (!$merchant) {
            return response()->json([
                'message' => 'Comercio no encontrado.'
            ], 404);
        }

        $merchant->delete();

        return response()->json([
            'message' => 'El comercio se ha eliminado correctamente.'
        ], 204); // 204 No Content
    }
}
