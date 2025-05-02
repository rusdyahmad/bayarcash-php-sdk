<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webimpian\BayarcashSdk\Actions\ChecksumGenerator;

class ChecksumGeneratorTest extends TestCase
{
    /**
     * Test class that uses the ChecksumGenerator trait
     */
    private $checksumGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test class that uses the ChecksumGenerator trait
        $this->checksumGenerator = new class {
            use ChecksumGenerator;
        };
    }

    /**
     * Test that createChecksumValue generates a valid HMAC-SHA256 hash
     */
    public function testCreateChecksumValue()
    {
        $secretKey = 'test-secret-key';
        $payload = [
            'payment_channel' => 1,
            'order_number' => 'ORDER123',
            'amount' => 100.00
        ];
        
        // Generate the checksum
        $checksum = $this->checksumGenerator->createChecksumValue($secretKey, $payload);
        
        // Verify it's a valid SHA-256 hash (64 characters long)
        $this->assertIsString($checksum);
        $this->assertEquals(64, strlen($checksum));
        
        // Verify that the same payload and key always generates the same checksum
        $checksum2 = $this->checksumGenerator->createChecksumValue($secretKey, $payload);
        $this->assertEquals($checksum, $checksum2);
        
        // Verify that changing the payload generates a different checksum
        $modifiedPayload = $payload;
        $modifiedPayload['amount'] = 200.00;
        $differentChecksum = $this->checksumGenerator->createChecksumValue($secretKey, $modifiedPayload);
        $this->assertNotEquals($checksum, $differentChecksum);
    }

    /**
     * Test that createPaymentIntentChecksumValue properly formats the payload
     */
    public function testCreatePaymentIntentChecksumValue()
    {
        $secretKey = 'test-secret-key';
        $data = [
            'payment_channel' => 1,
            'order_number' => 'ORDER123',
            'amount' => 100.00,
            'payer_name' => 'John Doe',
            'payer_email' => 'john@example.com',
            'callback_url' => 'https://example.com/callback',
            'redirect_url' => 'https://example.com/redirect',
            'extra_field' => 'This should be ignored'
        ];
        
        // Generate the checksum
        $checksum = $this->checksumGenerator->createPaymentIntentChecksumValue($secretKey, $data);
        
        // Verify it's a valid SHA-256 hash
        $this->assertIsString($checksum);
        $this->assertEquals(64, strlen($checksum));
        
        // Create a manual checksum with only the expected fields to verify it matches
        $expectedPayload = [
            'payment_channel' => $data['payment_channel'],
            'order_number' => $data['order_number'],
            'amount' => $data['amount'],
            'payer_name' => $data['payer_name'],
            'payer_email' => $data['payer_email']
        ];
        
        ksort($expectedPayload);
        $payloadString = implode('|', $expectedPayload);
        $expectedChecksum = hash_hmac('sha256', $payloadString, $secretKey);
        
        $this->assertEquals($expectedChecksum, $checksum);
    }

    /**
     * Test that the deprecated createPaymentIntenChecksumValue method still works
     */
    public function testDeprecatedCreatePaymentIntenChecksumValue()
    {
        $secretKey = 'test-secret-key';
        $data = [
            'payment_channel' => 1,
            'order_number' => 'ORDER123',
            'amount' => 100.00,
            'payer_name' => 'John Doe',
            'payer_email' => 'john@example.com'
        ];
        
        // Generate checksums with both methods
        $oldChecksum = $this->checksumGenerator->createPaymentIntenChecksumValue($secretKey, $data);
        $newChecksum = $this->checksumGenerator->createPaymentIntentChecksumValue($secretKey, $data);
        
        // Verify they match
        $this->assertEquals($newChecksum, $oldChecksum);
    }
}
