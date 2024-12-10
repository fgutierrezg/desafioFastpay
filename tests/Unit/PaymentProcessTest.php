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
    public function it_returns_error_when_merchant_does_not_exist()
    {
        // Datos de prueba con un comercio no existente o inválido
        $data = [
            'merchantId' => null,
            'amount' => 1000,
            'paymentMethodId' => 1,
            'expectedPaymentFinalStatus' => true,
        ];

        // Realiza la solicitud POST a la función processPayment
        $response = $this->post('/api/payments/process', $data);

        // Verifica la respuesta de error
        $response->assertStatus(422);

        $jsonResponse = $response->json();

        $this->assertTrue(
            isset($jsonResponse['message']) && $jsonResponse['message'] === "Comercio no valido o inexistente" ||
            (isset($jsonResponse['errors']['merchantId']) && $jsonResponse['errors']['merchantId'][0] === "The merchant id field is required.")
        );
    }

    /** @test */
    public function it_returns_error_when_invalid_payment_input()
    {
        // Datos de prueba con un comercio no existente o inválido
        $data = [
            'merchantId' => null,
            'amount' => true,
            'paymentMethodId' => "dsa",
            'expectedPaymentFinalStatus' => 'hello',
        ];

        // Realiza la solicitud POST a la función processPayment
        $response = $this->post('/api/payments/process', $data);

        // Verifica la respuesta de error
        $response->assertStatus(422);

    }

       /** @test */
       public function it_validates_merchant_email_when_token_is_generated()
       {
           // Datos de prueba con un comercio no existente o inválido
           $data = [
               'email' => 'asdasd@store.cl',
               'password' => "123Tienda"
           ];

           // Realiza la solicitud POST a la función processPayment
           $response = $this->post('/api/auth/login', $data);

           // Verifica la respuesta de error
           $response->assertStatus(401);
           $response->assertJson(['message' => 'Unauthorized']);
       }

   /** @test */
    public function it_validates_merchant_pass_when_token_is_generated()
    {
        // Datos de prueba con un comercio no existente o inválido
        $data = [
            'email' => 'tienda1@store.cl',
            'password' => "asdasd"
        ];

        // Realiza la solicitud POST a la función processPayment
        $response = $this->post('/api/auth/login', $data);

        // Verifica la respuesta de error
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthorized']);
    }
}
