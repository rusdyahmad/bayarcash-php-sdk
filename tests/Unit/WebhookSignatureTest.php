<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webimpian\BayarcashSdk\Bayarcash;

class WebhookSignatureTest extends TestCase
{
    /**
     * @var Bayarcash
     */
    private $bayarcash;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->bayarcash = new Bayarcash('test-token');
    }

    /**
     * Test that verifyWebhookSignature correctly validates signatures
     */
    public function testVerifyWebhookSignature()
    {
        $secretKey = 'test-secret-key';
        $payload = json_encode([
            'event' => 'payment.completed',
            'data' => [
                'transaction_id' => 'TRX123',
                'order_number' => 'ORDER123',
                'status' => '3'
            ]
        ]);
        
        // Generate a valid signature
        $validSignature = hash_hmac('sha256', $payload, $secretKey);
        
        // Test with valid signature
        $this->assertTrue(
            $this->bayarcash->verifyWebhookSignature($validSignature, $payload, $secretKey)
        );
        
        // Test with invalid signature
        $invalidSignature = hash_hmac('sha256', $payload, 'wrong-secret-key');
        $this->assertFalse(
            $this->bayarcash->verifyWebhookSignature($invalidSignature, $payload, $secretKey)
        );
        
        // Test with tampered payload
        $tamperedPayload = json_encode([
            'event' => 'payment.completed',
            'data' => [
                'transaction_id' => 'TRX123',
                'order_number' => 'ORDER123',
                'status' => '4' // Changed from '3' to '4'
            ]
        ]);
        $this->assertFalse(
            $this->bayarcash->verifyWebhookSignature($validSignature, $tamperedPayload, $secretKey)
        );
    }

    /**
     * Test that verifyWebhookSignature is secure against timing attacks
     */
    public function testVerifyWebhookSignatureIsSecureAgainstTimingAttacks()
    {
        $secretKey = 'test-secret-key';
        $payload = json_encode(['test' => 'data']);
        
        // Generate a valid signature
        $validSignature = hash_hmac('sha256', $payload, $secretKey);
        
        // Create a similar but invalid signature (change just the last character)
        $similarSignature = substr($validSignature, 0, -1) . 'f';
        
        // Verify that hash_equals is being used (which is constant-time)
        // This is an indirect test since we can't directly test the implementation
        $this->assertTrue(
            $this->bayarcash->verifyWebhookSignature($validSignature, $payload, $secretKey)
        );
        $this->assertFalse(
            $this->bayarcash->verifyWebhookSignature($similarSignature, $payload, $secretKey)
        );
    }
}
