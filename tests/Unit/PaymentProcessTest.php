<?php

namespace Tests\Unit;

use App\Models\Merchant;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Tests\TestCase;

class PaymentProcessTest extends TestCase
{
    /** @test */
    public function it_can_process_a_payment()
    {
        // Datos de prueba

        $data = [
            'merchantId' => mt_rand(1, 3),
            'amount' => mt_rand(1000, 10000),
            'paymentMethodId' => mt_rand(1, 3),
            'expectedPaymentFinalStatus' => true,
        ];

        // Realiza la solicitud POST a la función processPayment
        $response = $this->post('/api/payments/process', $data);

        // Verifica la respuesta
        $response->assertStatus(202);
        $response->assertJson(['message' => 'Validacion de pago en proceso.']);
        $this->assertDatabaseHas('payments', [
            'merchant_id' => $data['merchantId'],
            'amount' => $data['amount'],
            'payment_method' => $data['paymentMethodId'],
        ]);

        return $response;
    }

    /** @test */
/*     public function it_returns_error_when_merchant_does_not_exist()
    {
        // Datos de prueba con un comercio no existente
        $data = [
            'merchantId' => 999, // ID no existente
            'amount' => 1000,
            'paymentMethodId' => 1,
            'expectedPaymentFinalStatus' => true,
        ];

        // Realiza la solicitud POST a la función processPayment
        $response = $this->post('/payments/process', $data);

        // Verifica la respuesta de error
        $response->assertStatus(500);
        $response->assertJson(['message' => 'Error al intentar crear registro de pago para el comercio ID: 999']);
    } */
}
