<?php

namespace Webimpian\BayarcashSdk\Actions;

use Webimpian\BayarcashSdk\Resources\FpxDirectDebitApplicationResource;
use Webimpian\BayarcashSdk\Resources\FpxDirectDebitResource;
use Webimpian\BayarcashSdk\Resources\TransactionResource;

/**
 * Trait for FPX Direct Debit payment intent operations
 */
trait FpxDirectDebitPaymentIntent
{
    /**
     * Create a new FPX Direct Debit enrollment
     *
     * @param array $data The enrollment data including order_number, amount, payer details, etc.
     * @return \Webimpian\BayarcashSdk\Resources\FpxDirectDebitApplicationResource
     * @throws \Webimpian\BayarcashSdk\Exceptions\ValidationException If validation fails
     * @throws \Webimpian\BayarcashSdk\Exceptions\ApiException If API request fails
     */
    public function createFpxDirectDebitEnrollment(array $data): FpxDirectDebitApplicationResource
    {
        return new FpxDirectDebitApplicationResource(
            $this->post('mandates', $data),
            $this
        );
    }

    /**
     * Update an existing FPX Direct Debit mandate
     *
     * @param string $mandateId The mandate ID to update
     * @param array $data The maintenance data including amount, frequency_mode, etc.
     * @return \Webimpian\BayarcashSdk\Resources\FpxDirectDebitApplicationResource
     * @throws \Webimpian\BayarcashSdk\Exceptions\ValidationException If validation fails
     * @throws \Webimpian\BayarcashSdk\Exceptions\NotFoundException If mandate not found
     * @throws \Webimpian\BayarcashSdk\Exceptions\ApiException If API request fails
     */
    public function createFpxDirectDebitMaintenance(string $mandateId, array $data): FpxDirectDebitApplicationResource
    {
        return new FpxDirectDebitApplicationResource(
            $this->put('mandates/' . $mandateId, $data),
            $this
        );
    }

    /**
     * Terminate an existing FPX Direct Debit mandate
     *
     * @param string $mandateId The mandate ID to terminate
     * @param array $data The termination data including application_reason, etc.
     * @return \Webimpian\BayarcashSdk\Resources\FpxDirectDebitApplicationResource
     * @throws \Webimpian\BayarcashSdk\Exceptions\ValidationException If validation fails
     * @throws \Webimpian\BayarcashSdk\Exceptions\NotFoundException If mandate not found
     * @throws \Webimpian\BayarcashSdk\Exceptions\ApiException If API request fails
     */
    public function createFpxDirectDebitTermination(string $mandateId, array $data): FpxDirectDebitApplicationResource
    {
        return new FpxDirectDebitApplicationResource(
            $this->delete('mandates/' . $mandateId, $data),
            $this
        );
    }

    /**
     * Get an FPX Direct Debit transaction by ID (original method with typo)
     *
     * @param string|mixed $id The transaction ID
     * @return TransactionResource
     * @deprecated Use getFpxDirectDebitTransaction instead
     */
    public function getfpxDirectDebitransaction($id)
    {
        return $this->getFpxDirectDebitTransaction($id);
    }
    
    /**
     * Get an FPX Direct Debit transaction by ID (corrected naming)
     *
     * @param string|mixed $id The transaction ID
     * @return TransactionResource
     */
    public function getFpxDirectDebitTransaction($id)
    {
        return new TransactionResource(
            $this->get('mandates/transactions/' . $id),
            $this
        );
    }

    /**
     * Get an FPX Direct Debit mandate by ID
     *
     * @param string|mixed $id The mandate ID
     * @return FpxDirectDebitResource
     */
    public function getFpxDirectDebit($id)
    {
        return new FpxDirectDebitResource(
            $this->get('mandates/' . $id),
            $this
        );
    }
}
