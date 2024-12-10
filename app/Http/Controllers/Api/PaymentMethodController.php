<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return PaymentMethod::all();
    }

    /**
 * @OA\Post(
 *     path="/payment-methods",
 *     summary="Crear un nuevo método de pago",
 *     description="Permite crear un nuevo método de pago con nombre y tarifa.",
 *     tags={"Métodos de pago"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"name", "fee"},
 *             @OA\Property(property="name", type="string", example="Bitcoin"),
 *             @OA\Property(property="fee", type="number", format="float", example="0.10"),
 *              @OA\Property(property="description", type="string", example="Moneda digital descentralizada, segura, basada en blockchain, sin intermediarios."),
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Método de pago creado exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="El método de pago se ha creado exitosamente."),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=6),
 *                 @OA\Property(property="name", type="string", example="Bitcoin"),
 *                 @OA\Property(property="fee", type="number", format="float", example="0.10"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-10T15:49:31.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-10T15:49:31.000000Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Error al procesar la solicitud.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="El campo nombre es obligatorio.")),
 *                 @OA\Property(property="fee", type="array", @OA\Items(type="string", example="El campo tarifa debe ser un número."))
 *             )
 *         )
 *     )
 * )
 */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:payment_methods,name',
                'fee' => 'required|numeric|min:0',
            ]);

            $paymentMethod = PaymentMethod::create($validatedData);

            Log::info('Se ha creado un nuevo método de pago: ' . $paymentMethod);

            return response()->json([
                'message' => 'El método de pago se ha creado exitosamente.',
                'data' => $paymentMethod
            ], 201); // 201 Created

    } catch (\Throwable $th) {
        return response()->json(["message"=>"Error al procesar la solicitud. {$th->getMessage()}"], 500);
    }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json([
                'message' => 'Método de pago NO encontrado.'
            ], 404); // 404 Not Found
        }

        return response()->json([
            'message' => 'Método de pago encontrado.',
            'data' => $paymentMethod
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
        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json([
                'message' => 'Método de pago no encontrado.'
            ], 404); // 404 Not Found
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255|unique:payment_methods,name,' . $id,
            'fee' => 'sometimes|numeric|min:0',
        ]);

        $paymentMethod->update($validatedData);

        return response()->json([
            'message' => 'El método de pago se ha actualizado exitosamente.',
            'data' => $paymentMethod
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
        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json([
                'message' => 'Método de pago no encontrado.'
            ], 404); // 404 Not Found
        }

        $paymentMethod->delete();

        return response()->json([
            'message' => 'El método de pago se ha eliminado exitosamente.'
        ], 200); // 200 OK
    }
}
