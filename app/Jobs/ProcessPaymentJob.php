<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Models\Merchant;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\FeeCalculator;
use App\Services\paymentValidator;


class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $merchantId, $amount, $paymentMethodId,$paymentId, $expectedPaymentFinalStatus;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($merchantId, $amount, $paymentMethodId,$paymentId, $expectedPaymentFinalStatus)
    {
        $this->merchantId = $merchantId;
        $this->amount = $amount;
        $this->paymentMethodId = $paymentMethodId;
        $this->paymentId = $paymentId;
        $this->expectedPaymentFinalStatus = $expectedPaymentFinalStatus;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {

            $paymentMethod = cache()->get("pm_{$this->paymentMethodId}");

            if(!$paymentMethod){

                $paymentMethod = cache()->remember("pm_{$this->paymentMethodId}", 60, function () {
                    return PaymentMethod::find($this->paymentMethodId);
                });

                if (!$paymentMethod) {
                    Log::error("Método de pago no encontrado. ID: {$this->paymentMethodId}");
                    return;
                }

            }

            $merchant = cache()->get("mer_{$this->merchantId}");

            if (!$merchant) {

                $merchant = cache()->remember("mer_{$this->merchantId}", 60, function () {
                    return Merchant::find($this->merchantId);
                });

                if (!$merchant) {
                    Log::error("Comercio no encontrado. ID: {$this->merchantId}");
                    return;
                }
            }

            //Calculando tarifa
            $fee = FeeCalculator::ratePaymentCalculation($this->amount, $paymentMethod->fee);

            //Obteniendo respuesta simulada de proveedor
            $statusPayment = paymentValidator::validatePaymentWithProvider(
                $this->amount,
                $paymentMethod->name,
                $this->expectedPaymentFinalStatus
            );


            //Actualizando estado de pago
            Payment::where('id', $this->paymentId)->update([
                'status' => $statusPayment ? 'approved' : 'rejected',
                'fee' => $fee,
            ]);

            if($statusPayment){

                //Actualizando saldo de comercio
                $merchant->balance += ($this->amount - $fee);
                $merchant->save();

                Log::info("Pago ID {$this->paymentId} aprobado.");
                Log::info("Saldo de comercio {$this->merchantId} actualizado.");
            }else{
                Log::info("Pago ID {$this->paymentId} rechazado.");
            }

        } catch (\Exception $e) {
            Log::error("Error al procesar el pago: " . $e->getMessage() . $e->getLine());
        }
    }
}
