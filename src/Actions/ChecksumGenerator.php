<?php

namespace Webimpian\BayarcashSdk\Actions;

/**
 * Trait for generating checksum values for various BayarCash operations
 */
trait ChecksumGenerator
{
    /**
     * Create a generic checksum value from a payload
     *
     * @param string $secretKey The secret key for HMAC generation
     * @param array $payload The data payload to generate checksum from
     * @return string The generated checksum
     */
    public function createChecksumValue(string $secretKey, array $payload): string
    {
        ksort($payload);
        $payloadString = implode('|', $payload);

        return hash_hmac('sha256', $payloadString, $secretKey);
    }

    /**
     * Create a payment intent checksum value (with typo for backward compatibility)
     *
     * @param string $secretKey The secret key for HMAC generation
     * @param array $data The payment intent data
     * @return string The generated checksum
     * @deprecated Use createPaymentIntentChecksumValue instead
     */
    public function createPaymentIntenChecksumValue(string $secretKey, array $data): string
    {
        return $this->createPaymentIntentChecksumValue($secretKey, $data);
    }

    /**
     * Create a payment intent checksum value
     *
     * @param string $secretKey The secret key for HMAC generation
     * @param array $data The payment intent data containing payment_channel, order_number, amount, payer_name, etc.
     * @return string The generated checksum
     */
    public function createPaymentIntentChecksumValue(string $secretKey, array $data): string
    {
        $payload = [
            'payment_channel' => $data['payment_channel'],
            'order_number' => $data['order_number'],
            'amount' => $data['amount'],
            'payer_name' => $data['payer_name'],
            'payer_email' => $data['payer_email'],
        ];

        return $this->createChecksumValue($secretKey, $payload);
    }

    /**
     * Create an FPX Direct Debit enrollment checksum value (original naming with typo)
     * 
     * @param string|mixed $secretKey The secret key for HMAC generation
     * @param array $data The enrollment data
     * @return string The generated checksum
     */
    public function createFpxDIrectDebitEnrolmentChecksumValue($secretKey, array $data)
    {
        $payload = [
            'order_number' => $data['order_number'],
            'amount' => $data['amount'],
            'payer_name' => $data['payer_name'],
            'payer_email' => $data['payer_email'],
            'payer_telephone_number' => $data['payer_telephone_number'],
            'payer_id_type' => $data['payer_id_type'],
            'payer_id' => $data['payer_id'],
            'application_reason' => $data['application_reason'],
            'frequency_mode' => $data['frequency_mode'],
        ];

        return $this->createChecksumValue($secretKey, $payload);
    }
    
    /**
     * Create an FPX Direct Debit enrollment checksum value (corrected naming)
     * 
     * @param string|mixed $secretKey The secret key for HMAC generation
     * @param array $data The enrollment data
     * @return string The generated checksum
     */
    public function createFpxDirectDebitEnrollmentChecksumValue($secretKey, array $data)
    {
        return $this->createFpxDIrectDebitEnrolmentChecksumValue($secretKey, $data);
    }

    /**
     * Create an FPX Direct Debit maintenance checksum value (original naming with typo)
     * 
     * @param string|mixed $secretKey The secret key for HMAC generation
     * @param array $data The maintenance data
     * @return string The generated checksum
     */
    public function createFpxDIrectDebitMaintenanceChecksumValue($secretKey, array $data)
    {
        $payload = [
            'amount' => $data['amount'],
            'payer_email' => $data['payer_email'],
            'payer_telephone_number' => $data['payer_telephone_number'],
            'application_reason' => $data['application_reason'],
            'frequency_mode' => $data['frequency_mode'],
        ];

        return $this->createChecksumValue($secretKey, $payload);
    }
    
    /**
     * Create an FPX Direct Debit maintenance checksum value (corrected naming)
     * 
     * @param string|mixed $secretKey The secret key for HMAC generation
     * @param array $data The maintenance data
     * @return string The generated checksum
     */
    public function createCorrectFpxDirectDebitMaintenanceChecksumValue($secretKey, array $data)
    {
        return $this->createFpxDIrectDebitMaintenanceChecksumValue($secretKey, $data);
    }
}
