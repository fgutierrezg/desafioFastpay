<?php

namespace Tests\Feature;

use App\Jobs\ProcessPaymentJob;
use App\Models\Merchant;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Tests\TestCase;
use Illuminate\Support\Facades\Bus;

class PaymentProcessIntegrationTest extends TestCase
{

    /** @test */
    public function it_returns_error_when_invalid_payment_data()
    {
        // Datos de prueba con datos inválidos
        $data = [
            'merchantId' => 7,
            'amount' => -1000,
            'paymentMethodId' => 'invalid',
            'expectedPaymentFinalStatus' => true,
        ];

        // Realiza la solicitud POST a la función processPayment
        $response = $this->post('/api/payments/process', $data);

        // Verifica la respuesta de error
        $response->assertStatus(422); // HTTP 422 Unprocessable Entity
        //$response->assertJsonValidationErrors(['The amount must be a number.', 'The payment method id must be a number.']);
    }
}
