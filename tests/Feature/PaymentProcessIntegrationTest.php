<?php

namespace Tests\Feature;

use App\Jobs\ProcessPaymentJob;
use App\Models\Merchant;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\Services\FeeCalculator;

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

    /** @test */
    public function it_validates_if_new_payment_method_is_created_and_stored_in_db()
    {
        $randomName = "test_method_".Str::random(10);

        $data = [
            'name' => $randomName,
            'fee' => "0.10",
            'description' => "Este es el metodo de pago {$randomName}",
        ];

        $response = $this->post('/api/payment-methods', $data);

        // Verificar que la respuesta es correcta
        $response->assertStatus(201)
                ->assertJson(["message" => "El método de pago se ha creado exitosamente."]);

        // Verificar que el metodo existe en la base de datos
        $this->assertDatabaseHas('payment_methods', [
            'name' => $randomName
        ]);
    }

    /** @test */
    public function it_validates_if_rate_calculation_service_is_working_correctly()
    {
        $expectedFee = 10;
        $calculatedFee = FeeCalculator::ratePaymentCalculation(10000,0.10);

        $this->assertEquals(
            $expectedFee,
            $calculatedFee,
            "Cálculo. Esperado:{$expectedFee}. Obtenido:{$calculatedFee}"
        );
    }



}
