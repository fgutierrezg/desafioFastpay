<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Merchant;
use App\Models\PaymentMethod;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessPaymentJob;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::all();
    }

    public function show($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'message' => 'Pago NO encontrado.'
            ], 404); // 404 Not Found
        }

        return response()->json([
            'message' => 'Pago encontrado.',
            'data' => $payment
        ], 200); // 200 OK
    }

    /**
 * @OA\Get(
 *     path="/payments/merchant/{id}",
 *     summary="Obtener pagos por ID de comercio",
 *     description="Devuelve los pagos asociados a un comercio específico por su ID.",
 *     operationId="showPaymentsByMerchantId",
 *     tags={"Pagos"},
*      security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del comercio",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *              example=1
 *         )
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Pagos encontrados para el comercio especificado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Pagos encontrados para comercio ID 3"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=121),
 *                     @OA\Property(property="merchant_id", type="integer", example=3),
 *                     @OA\Property(property="amount", type="number", format="float", example=6956.00),
 *                     @OA\Property(property="fee", type="number", format="float", example=41.74),
 *                     @OA\Property(property="payment_method", type="integer", example=3),
 *                     @OA\Property(property="status", type="string", example="approved"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-10T01:32:29.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-10T01:32:29.000000Z")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="No autorizado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="No autorizado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No se encontraron pagos",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="No se encontraron pagos asociados al comercio ID X.")
 *         )
 *     ),
 *      @OA\Response(
 *         response=403,
 *         description="Acceso denegado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Acceso denegado al recurso.")
 *         )
 *     )
 * )
 */
    public function showByMerchantId($id)
    {
        // Obtener los pagos asociados al merchant
        $payments = Payment::where('merchant_id', $id)->get();

        // Validar si no existen pagos
        if ($payments->isEmpty()) {
            Log::info("No se encontraron pagos asociados al comercio con ID {$id}");
            return response()->json([
                'message' => "No se encontraron pagos asociados al comercio ID {$id}."
            ], 404); // 404 Not Found
        }

        Log::info('Se encontraron pagos asociados al comercio con ID '.$id.'. Detalle de pagos: '.$payments);
        // Responder con los pagos encontrados
        return response()->json([
            'message' => "Pagos encontrados para comercio ID {$id}",
            'data' => $payments
        ], 200); // 200 OK

    }

    /**
 * @OA\Get(
 *     path="/payments/merchant/{merchantId}/payment/{paymentId}",
 *     summary="Obtener un pago por ID de comercio y ID de pago",
 *     description="Devuelve un pago específico asociado a un comercio dado por su ID y el ID de pago.",
 *     operationId="showPaymentByMerchantIdAndPaymentId",
 *     tags={"Pagos"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="merchantId",
 *         in="path",
 *         description="ID del comercio",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=3
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="paymentId",
 *         in="path",
 *         description="ID del pago",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=121
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Pago encontrado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Pago encontrado para comercio ID X."),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=121),
 *                 @OA\Property(property="merchant_id", type="integer", example=3),
 *                 @OA\Property(property="amount", type="number", format="float", example=6956.00),
 *                 @OA\Property(property="fee", type="number", format="float", example=41.74),
 *                 @OA\Property(property="payment_method", type="integer", example=3),
 *                 @OA\Property(property="status", type="string", example="approved"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-10T01:32:29.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-10T01:32:29.000000Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="No autorizado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="No autorizado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Acceso denegado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Acceso denegado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No se encontró un pago coincidente con la busqueda.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="No se encontró un pago coincidente con la busqueda.")
 *         )
 *     )
 * )
 */

    public function showByMerchantIdAndPaymentId($merchantId, $paymentId)
    {
        // Obtener el pago asociado al merchant y al payment ID
        $payment = Payment::where('merchant_id', $merchantId)
            ->where('id', $paymentId)
            ->first();

        // Validar si no existe el pago
        if (!$payment) {
            Log::info("No se encontró un pago con ID {$paymentId} para el comercio con ID {$merchantId}");
            return response()->json([
                'message' => "No se encontró un pago coincidente con la busqueda."
            ], 404);
        }

        Log::info('Se encontró un pago asociado al comercio con ID ' . $merchantId . '. Detalle del pago: ' . $payment);

        return response()->json([
            'message' => "Pago encontrado.",
            'data' => $payment
        ], 200);
    }

     /**
     * @OA\Post(
     *     path="/payments/process",
     *     operationId="processPayment",
     *     tags={"Pagos"},
     *     summary="Procesar un pago",
     *     description="Procesa un pago y lo guarda en la base de datos",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="merchantId", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", example=5000),
     *             @OA\Property(property="paymentMethodId", type="integer", example=2),
     *             @OA\Property(property="expectedPaymentFinalStatus", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Validación de pago en proceso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validacion de pago en proceso."),
     *             @OA\Property(property="data", type="object",
     *             @OA\Property(property="merchant_id", type="number", example=1),
     *             @OA\Property(property="amount", type="number", example=5000),
     *             @OA\Property(property="fee", type="number", example=0),
     *             @OA\Property(property="status", type="string", example="in validation"),
     *             @OA\Property(property="payment_method", type="number", example=2),
     *             @OA\Property(property="updated_at", type="string", example="2024-12-10T15:28:35.000000Z"),
     *             @OA\Property(property="created_at", type="string", example="2024-12-10T15:28:35.000000Z"),
     *             @OA\Property(property="id", type="number", example=123),
     *
     * ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al intentar procesar el pago",
     *       @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error al intentar procesar el pago:")
     *         )
     *     ),
     *      @OA\Response(
     *         response=422,
     *         description="El contenido enviado no se pudo procesar.",
     *       @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="merchantId", type="array",
    *     @OA\Items(
    *         type="string",
    *         example="The merchant id field is required."
    *     )
 *      ),
 *                 @OA\Property(property="amount", type="array",
 *
    *     @OA\Items(
    *         type="string",
    *         example="The amount must be a number."
    *     )
    *),
 *                 @OA\Property(property="paymentMethodId", type="array",
    *     @OA\Items(
    *         type="string",
    *         example="The payment method id field is required."
    *     )
 * ),
 *                 @OA\Property(property="expectedPaymentFinalStatus", type="array",
 *          @OA\Items(
    *         type="string",
    *         example="The expected payment final status field must be true or false."
    *     )
 * )
 *             )
     *         )
     *     ),
     *
     * )
     */
    public function processPayment(Request $request)
    {
        $validatedData = Validator::make($request->all(),[
            'merchantId' => 'required|numeric',
            'amount' => 'required|numeric|min:0.01',
            'paymentMethodId' => 'required|numeric',
            'expectedPaymentFinalStatus' => 'required|boolean',
        ]);

        if($validatedData->fails()){
            return response()->json([
                'errors' => $validatedData->errors(),
            ], 422);
        }

        $paymentMethodId = $request->paymentMethodId;
        $merchantId = $request->merchantId;

        $paymentMethod = cache()->get("pm_{$paymentMethodId}");

        if(!$paymentMethod){

            $paymentMethod = cache()->remember("pm_{$paymentMethodId}", 60, function () use ($paymentMethodId)  {
                return PaymentMethod::find($paymentMethodId);
            });

            if (!$paymentMethod) {
                Log::error("Método de pago no encontrado. ID: {$paymentMethodId}");

                return response()->json(['message' => 'Metodo de pago no valido o inexistente'], 422);
            }

        }

        $merchant = cache()->get("mer_{$merchantId}");
        if (!$merchant) {

            $merchant = cache()->remember("mer_{$merchantId}", 60, function () use ($merchantId) {
                return Merchant::find($merchantId);
            });

            if (!$merchant) {
                Log::error("Comercio no encontrado. ID: {$merchantId}");
                return response()->json(['message' => 'Comercio no valido o inexistente'], 422);
            }
        }

        try {

            $payment = Payment::create([
                'merchant_id' => $request->merchantId,
                'amount' => $request->amount,
                'fee' => 0,
                'status' => 'in validation',
                'payment_method' => $request->paymentMethodId,
            ]);

            if ($payment) {
                Log::info("Validación de pago en proceso. ID del pago: {$payment->id}");
            } else {
                Log::error("No se pudo crear el registro del pago para el comercio ID: {$request->merchantId} por el monto de {$request->amount}");
                 return response()->json(['message' => "Error al intentar crear registro de pago para el comercio ID: {$request->merchantId}"], 500);
            }

            ProcessPaymentJob::dispatch(
                $request->merchantId,
                $request->amount,
                $request->paymentMethodId,
                $payment->id,
                $request->expectedPaymentFinalStatus
            );

            return response()->json(['message' => 'Validacion de pago en proceso.',"data" => $payment], 202);

        } catch (\Throwable $e) {
            Log::error("Error al intentar procesar el pago: " . $e->getMessage() . $e->getLine());
            return response()->json(['message' => "Error al intentar procesar el pago: {$e->getMessage()} {$e->getLine()}"], 500);
        }

    }

}
