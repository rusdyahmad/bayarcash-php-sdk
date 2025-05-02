# BayarCash PHP SDK

A comprehensive PHP SDK for integrating with the BayarCash payment gateway. This SDK supports both API v2 and v3, providing functionality for processing payments through multiple channels including FPX, DuitNow, ShopeePayLater, and others.

## Table of Contents

- [Introduction](#introduction)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Payment Channels](#payment-channels)
- [Core Features](#core-features)
  - [Portal Management](#portal-management)
  - [FPX Bank Integration](#fpx-bank-integration)
  - [Payment Processing](#payment-processing)
  - [Callback Verification](#callback-verification)
  - [Transaction Management](#transaction-management)
  - [FPX Direct Debit](#fpx-direct-debit)
- [Transaction Status Codes](#transaction-status-codes)
- [Complete Integration Example](#complete-integration-example)
- [Security Recommendations](#security-recommendations)
- [Error Handling](#error-handling)
- [API Reference](#api-reference)
- [Troubleshooting](#troubleshooting)
- [Testing](#testing)
- [Support](#support)
- [License](#license)

## Introduction

The [BayarCash](https://bayarcash.com/) SDK provides an expressive interface for integrating with BayarCash's Payment Gateway API. This SDK supports both API v2 and v3, with enhanced features available in v3. The SDK includes features for payment intent creation, transaction management, FPX Direct Debit operations, and security features like checksum generation and callback verification.

### Version Compatibility

| SDK Version | PHP Version                            | API Version | Features                                                                             |
| ----------- | -------------------------------------- | ----------- | ------------------------------------------------------------------------------------ |
| 1.x         | PHP 7.2+                               | v2          | Basic payment processing, transaction management                                     |
| 2.x         | PHP 7.4+, 8.0+, 8.1+, 8.2+, 8.3+, 8.4+ | v2, v3      | Enhanced error handling, FPX Direct Debit, PaymentChannel enum, Webhook verification |

We recommend using the latest version of the SDK to take advantage of all features and improvements.

## Installation

To install the SDK in your project, use Composer:

```bash
composer require webimpian/bayarcash-php-sdk
```

## Quick Start

### 1. Initialize the SDK

```php
// Initialize with your Personal Access Token
$bayarcash = new Webimpian\BayarcashSdk\Bayarcash('your-personal-access-token');

// Use sandbox environment for testing
$bayarcash->useSandbox();

// Optional: Use API v3 (default is v2)
$bayarcash->setApiVersion('v3');
```

### 2. Create a Payment Intent

```php
// Import the PaymentChannel enum for better code readability
use Webimpian\BayarcashSdk\Enums\PaymentChannel;

// Prepare payment data
$paymentData = [
    'payment_channel' => PaymentChannel::FPX,
    'order_number' => 'ORD-' . time(),
    'amount' => 100.00, // RM 100.00
    'payer_name' => 'John Doe',
    'payer_email' => 'john@example.com',
    'payer_telephone_number' => '60123456789',
    'callback_url' => 'https://your-website.com/callback',
    'redirect_url' => 'https://your-website.com/redirect'
];

// Generate checksum for enhanced security (recommended)
$secretKey = 'your-secret-key';
$checksum = $bayarcash->createPaymentIntentChecksumValue($secretKey, $paymentData);
$paymentData['checksum'] = $checksum;

// Create payment intent
$paymentIntent = $bayarcash->createPaymentIntent($paymentData);

// Redirect user to payment page
header('Location: ' . $paymentIntent->url);
exit;
```

### 3. Handle Callback

```php
// Verify callback data
$callbackData = $_POST; // or use your framework's request handling
$secretKey = 'your-secret-key';

// Verify the callback data integrity
$isValid = $bayarcash->verifyTransactionCallbackData($callbackData, $secretKey);

if ($isValid) {
    // Process the successful payment
    $transactionId = $callbackData['transaction_id'];
    $orderNumber = $callbackData['order_number'];
    $status = $callbackData['status'];

    // Update your database with payment status
    // ...

    // Return success response
    echo json_encode(['status' => 'success']);
} else {
    // Invalid callback data
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid callback data']);
}
```

## Configuration

### General Configuration

```php
// Initialize with Personal Access Token
$bayarcash = new Webimpian\BayarcashSdk\Bayarcash('your-personal-access-token');

// Use sandbox environment for testing
$bayarcash->useSandbox();

// Set API version (v2 or v3)
$bayarcash->setApiVersion('v3');

// Set request timeout (in seconds)
$bayarcash->setTimeout(30);
```

### Laravel Integration

For Laravel users, add these environment variables to your `.env` file:

```env
BAYARCASH_API_TOKEN=your-personal-access-token
BAYARCASH_API_SECRET_KEY=your-secret-key
BAYARCASH_SANDBOX=true
```

Then, you can use the SDK in your controllers:

```php
// In your controller
public function checkout(Request $request)
{
    $bayarcash = app(\Webimpian\BayarcashSdk\Bayarcash::class);

    // If BAYARCASH_SANDBOX is true in .env
    if (config('services.bayarcash.sandbox')) {
        $bayarcash->useSandbox();
    }

    // Create payment intent
    // ...
}
```

## Payment Channels

The SDK supports multiple payment channels through the `PaymentChannel` enum class for better code organization and readability:

```php
use Webimpian\BayarcashSdk\Enums\PaymentChannel;

// Available payment channels
PaymentChannel::FPX                // 1 - FPX Online Banking
PaymentChannel::MANUAL_TRANSFER    // 2 - Manual Bank Transfer
PaymentChannel::FPX_DIRECT_DEBIT   // 3 - FPX Direct Debit
PaymentChannel::FPX_LINE_OF_CREDIT // 4 - FPX Line of Credit
PaymentChannel::DUITNOW_DOBW       // 5 - DuitNow Online Banking
PaymentChannel::DUITNOW_QR         // 6 - DuitNow QR
PaymentChannel::SPAYLATER          // 7 - ShopeePayLater
PaymentChannel::BOOST_PAYFLEX      // 8 - Boost PayFlex
PaymentChannel::QRISOB             // 9 - QRIS Online Banking
PaymentChannel::QRISWALLET         // 10 - QRIS Wallet
PaymentChannel::NETS               // 11 - NETS
```

For backward compatibility, the payment channels are also available as constants in the `Bayarcash` class:

```php
Bayarcash::FPX                 // 1 - FPX Online Banking
Bayarcash::MANUAL_TRANSFER      // 2 - Manual Bank Transfer
// ... and so on
```

The `PaymentChannel` enum provides additional helper methods:

```php
// Get all payment channels as an associative array
$channels = PaymentChannel::all(); // [1 => 'FPX', 2 => 'Manual Bank Transfer', ...]

// Check if a payment channel is valid
$isValid = PaymentChannel::isValid(1); // true

// Get the name of a payment channel
$name = PaymentChannel::getName(1); // "FPX"

// Get payment channels by type/category
$fpxChannels = PaymentChannel::getByType('fpx'); // [1 => 'FPX', 3 => 'FPX Direct Debit', 4 => 'FPX Line of Credit']

// Get a payment channel ID by its name
$id = PaymentChannel::getIdByName('FPX'); // 1
```

## Core Features

### Portal Management

```php
// Get all available portals
$portals = $bayarcash->getPortals();

// Access portal properties
foreach ($portals as $portal) {
    echo "Portal Name: {$portal->name}\n";
    echo "Portal Key: {$portal->portalKey}\n";
    echo "Available Payment Channels: " . implode(', ', $portal->paymentChannels) . "\n";
}

// Get available payment channels for a specific portal
$channels = $bayarcash->getChannels('your_portal_key');
```

### FPX Bank Integration

```php
// Get list of available FPX banks
$banks = $bayarcash->fpxBanksList();

// Access bank properties
foreach ($banks as $bank) {
    echo "Bank Name: {$bank->name}\n";
    echo "Bank Code: {$bank->code}\n";
    echo "Status: " . ($bank->status ? 'Active' : 'Inactive') . "\n";
}
```

### Payment Processing

```php
// Import the PaymentChannel enum for better code readability
use Webimpian\BayarcashSdk\Enums\PaymentChannel;

// Prepare payment data
$paymentData = [
    'payment_channel' => PaymentChannel::FPX,
    'order_number' => 'ORD-' . time(),
    'amount' => 100.00,
    'payer_name' => 'John Doe',
    'payer_email' => 'john@example.com',
    'callback_url' => 'https://your-website.com/callback',
    'redirect_url' => 'https://your-website.com/redirect'
];

// Generate checksum for enhanced security (recommended)
$secretKey = 'your-secret-key';

// Use the corrected method name (note the proper spelling of "Intent")
$checksum = $bayarcash->createPaymentIntentChecksumValue($secretKey, $paymentData);
$paymentData['checksum'] = $checksum;

// Create payment intent
$paymentIntent = $bayarcash->createPaymentIntent($paymentData);

// Access payment intent properties
echo "Payment URL: {$paymentIntent->url}\n";
echo "Payment ID: {$paymentIntent->id}\n";
echo "Status: {$paymentIntent->status}\n";

// Redirect user to payment page
header('Location: ' . $paymentIntent->url);
exit;
```

### Callback Verification

```php
// Verify different types of callbacks
$secretKey = 'your-secret-key';

// Pre-transaction callback (before payment is processed)
$preTransactionData = $_POST; // or use your framework's request handling
$validPreTransaction = $bayarcash->verifyPreTransactionCallbackData($preTransactionData, $secretKey);

// Transaction callback (after payment is processed)
$transactionData = $_POST;
$validTransaction = $bayarcash->verifyTransactionCallbackData($transactionData, $secretKey);

// Return URL callback (when user is redirected back to your site)
$returnUrlData = $_GET; // typically sent as GET parameters
$validReturnUrl = $bayarcash->verifyReturnUrlCallbackData($returnUrlData, $secretKey);

// Process verified callback data
if ($validTransaction) {
    // Extract important information
    $transactionId = $transactionData['transaction_id'];
    $orderNumber = $transactionData['order_number'];
    $status = $transactionData['status'];
    $amount = $transactionData['amount'];
    $payerEmail = $transactionData['payer_email'];

    // Update your database with payment status
    // ...
}
```

### Transaction Management

```php
// Get single transaction by ID
$transaction = $bayarcash->getTransaction('transaction_id');

// Access transaction properties
echo "Transaction ID: {$transaction->id}\n";
echo "Order Number: {$transaction->orderNumber}\n";
echo "Amount: {$transaction->amount}\n";
echo "Status: {$transaction->status}\n";
echo "Payment Channel: {$transaction->paymentChannel}\n";

// API v3 Features (enhanced transaction management)
if ($bayarcash->getApiVersion() === 'v3') {
    // Get all transactions with filters
    $transactions = $bayarcash->getAllTransactions([
        'order_number' => 'ORDER123',
        'status' => '3', // Completed status
        'payment_channel' => PaymentChannel::FPX,
        'exchange_reference_number' => 'REF123',
        'payer_email' => 'customer@example.com'
    ]);

    // Access paginated results
    $transactionsList = $transactions['data'];
    $pagination = $transactions['meta'];

    // Specialized transaction queries
    $orderTransactions = $bayarcash->getTransactionByOrderNumber('ORDER123');
    $emailTransactions = $bayarcash->getTransactionsByPayerEmail('customer@example.com');
    $statusTransactions = $bayarcash->getTransactionsByStatus('3'); // Completed status
    $channelTransactions = $bayarcash->getTransactionsByPaymentChannel(PaymentChannel::FPX);
    $refTransaction = $bayarcash->getTransactionByReferenceNumber('REF123');
}
```

### FPX Direct Debit

#### 1. FPX Direct Debit Enrollment

```php
// Prepare enrollment data
$enrollmentData = [
    'order_number' => 'ORD-' . time(),
    'amount' => 100.00,
    'payer_name' => 'John Doe',
    'payer_email' => 'john@example.com',
    'payer_telephone_number' => '60123456789',
    'payer_id_type' => 'NRIC',
    'payer_id' => '123456789012',
    'application_reason' => 'Subscription',
    'frequency_mode' => 'ADHOC',
    'callback_url' => 'https://your-website.com/callback',
    'redirect_url' => 'https://your-website.com/redirect'
];

// Generate checksum (use the corrected method name)
$secretKey = 'your-secret-key';
$checksum = $bayarcash->createFpxDirectDebitEnrollmentChecksumValue($secretKey, $enrollmentData);
$enrollmentData['checksum'] = $checksum;

// Create enrollment
$enrollment = $bayarcash->createFpxDirectDebitEnrollment($enrollmentData);

// Redirect user to enrollment page
header('Location: ' . $enrollment->url);
exit;
```

#### 2. FPX Direct Debit Maintenance

```php
// Prepare maintenance data
$maintenanceData = [
    'amount' => 150.00,
    'payer_email' => 'john@example.com',
    'payer_telephone_number' => '60123456789',
    'application_reason' => 'Subscription Update',
    'frequency_mode' => 'ADHOC',
    'callback_url' => 'https://your-website.com/callback',
    'redirect_url' => 'https://your-website.com/redirect'
];

// Generate checksum (use the corrected method name)
$secretKey = 'your-secret-key';
$checksum = $bayarcash->createCorrectFpxDirectDebitMaintenanceChecksumValue($secretKey, $maintenanceData);
$maintenanceData['checksum'] = $checksum;

// Update mandate
$mandateId = 'mandate-id-from-enrollment';
$maintenance = $bayarcash->createFpxDirectDebitMaintenance($mandateId, $maintenanceData);

// Redirect user to maintenance page
header('Location: ' . $maintenance->url);
exit;
```

#### 3. FPX Direct Debit Termination

```php
// Prepare termination data
$terminationData = [
    'application_reason' => 'Subscription Cancellation',
    'callback_url' => 'https://your-website.com/callback',
    'redirect_url' => 'https://your-website.com/redirect'
];

// Terminate mandate
$mandateId = 'mandate-id-from-enrollment';
$termination = $bayarcash->createFpxDirectDebitTermination($mandateId, $terminationData);

// Redirect user to termination page
header('Location: ' . $termination->url);
exit;
```

#### 4. Get FPX Direct Debit Information

```php
// Get mandate details
$mandate = $bayarcash->getFpxDirectDebit('mandate-id');

// Get direct debit transaction
$transaction = $bayarcash->getFpxDirectDebitTransaction('transaction-id');
```

## Security Recommendations

1. Always use checksums for payment requests when possible
2. Verify all callbacks using the provided verification methods
3. Store and validate transaction IDs to prevent duplicate processing
4. Use HTTPS for all API communications
5. Keep your API tokens and secret keys secure

## Error Handling

The SDK provides robust error handling for API interactions. Here are some common error scenarios and how to handle them:

```php
try {
    // Attempt to create a payment intent
    $paymentIntent = $bayarcash->createPaymentIntent($paymentData);

    // Process successful response
    header('Location: ' . $paymentIntent->url);
    exit;
} catch (\Webimpian\BayarcashSdk\Exceptions\ValidationException $e) {
    // Handle validation errors
    $errors = $e->errors();
    echo "Validation errors: " . json_encode($errors);
} catch (\Webimpian\BayarcashSdk\Exceptions\NotFoundException $e) {
    // Handle 404 errors
    echo "Resource not found: " . $e->getMessage();
} catch (\Webimpian\BayarcashSdk\Exceptions\AuthenticationException $e) {
    // Handle authentication errors
    echo "Authentication failed: " . $e->getMessage();
} catch (\Webimpian\BayarcashSdk\Exceptions\RateLimitException $e) {
    // Handle rate limiting
    echo "Rate limit exceeded. Try again in " . $e->retryAfter . " seconds";
} catch (\Webimpian\BayarcashSdk\Exceptions\ApiException $e) {
    // Handle general API errors
    echo "API error: " . $e->getMessage();
} catch (\Exception $e) {
    // Handle unexpected errors
    echo "Unexpected error: " . $e->getMessage();
}
```

The SDK throws specific exception types based on the HTTP status code returned by the API:

- `ValidationException`: 422 Validation errors
- `NotFoundException`: 404 Resource not found
- `AuthenticationException`: 401 Authentication failed
- `RateLimitException`: 429 Too many requests
- `ApiException`: Other API errors

Each exception type provides access to relevant error information through methods like `getMessage()`, `getCode()`, and specific methods like `errors()` for validation exceptions.

## SDK Enhancements

### Type Declarations

The SDK now includes proper type declarations for improved IDE support and code reliability:

```php
// Methods with type declarations for better IDE support
public function createChecksumValue(string $secretKey, array $payload): string
public function createPaymentIntentChecksumValue(string $secretKey, array $data): string
public function verifyWebhookSignature(string $signature, string $payload, string $secretKey): bool
```

### Webhook Signature Verification

The SDK now includes a dedicated method for securely verifying webhook signatures:

```php
// Verify that a webhook came from BayarCash using HMAC-SHA256
$isValid = $bayarcash->verifyWebhookSignature(
    $headers['X-Bayarcash-Signature'],
    $rawPayload,
    $secretKey
);
```

## API Version Differences

The BayarCash SDK supports both API v2 and v3. Here are the key differences between the versions:

| Feature             | API v2                  | API v3                                        |
| ------------------- | ----------------------- | --------------------------------------------- |
| Payment Channels    | Basic channels only     | All payment channels including newer options  |
| Transaction Queries | Limited filtering       | Enhanced filtering with multiple parameters   |
| Pagination          | Not supported           | Supported for transaction listings            |
| Error Responses     | Basic error information | Detailed error messages and validation errors |
| FPX Direct Debit    | Basic support           | Enhanced support with maintenance operations  |

To set the API version:

```php
// Use API v3 (recommended)
$bayarcash->setApiVersion('v3');

// Check current API version
$currentVersion = $bayarcash->getApiVersion(); // Returns 'v2' or 'v3'
```

## Webhook Integration

Webhooks provide real-time notifications about payment events. Here's how to set up webhook handling:

### Step 1: Configure Webhook URL

Set your `callback_url` when creating payment intents to receive webhook notifications.

### Step 2: Secure Webhook Verification

Always verify incoming webhook data to prevent fraud:

```php
// Webhook handler (webhook.php)
$payload = file_get_contents('php://input');
$headers = getallheaders();

// Log the raw webhook for debugging
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - " . $payload . "\n", FILE_APPEND);

// Parse the JSON payload
$data = json_decode($payload, true);

// Verify the webhook signature if available
$signature = $headers['X-Bayarcash-Signature'] ?? null;

if ($signature) {
    // Use the new verifyWebhookSignature method to ensure the webhook is from BayarCash
    $isValid = $bayarcash->verifyWebhookSignature($signature, $payload, $secretKey);

    if (!$isValid) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }
}

// Process the webhook based on event type
$eventType = $data['event'] ?? '';

switch ($eventType) {
    case 'payment.completed':
        // Handle successful payment
        $transactionId = $data['data']['transaction_id'];
        $orderNumber = $data['data']['order_number'];

        // Update order status
        updateOrderStatus($orderNumber, 'paid', $transactionId);
        break;

    case 'payment.failed':
        // Handle failed payment
        $orderNumber = $data['data']['order_number'];
        updateOrderStatus($orderNumber, 'failed');
        break;

    // Handle other event types
    // ...
}

// Acknowledge receipt of webhook
http_response_code(200);
echo json_encode(['status' => 'success']);
```

### Step 3: Test Webhooks

Use the sandbox environment to test webhook integration before going live.

## API Reference

Please refer to the [Official Bayarcash API Documentation](https://api.webimpian.support/bayarcash) for detailed information about our API.

## Transaction Status Codes

Understanding transaction status codes is essential when processing payments. Here are the common status codes returned by the BayarCash API:

| Status Code | Description | Action Required                                                                    |
| ----------- | ----------- | ---------------------------------------------------------------------------------- |
| 1           | Pending     | Payment has been initiated but not completed. Wait for callback.                   |
| 2           | Processing  | Payment is being processed by the bank. Wait for final status.                     |
| 3           | Completed   | Payment has been successfully processed. Fulfill the order.                        |
| 4           | Failed      | Payment attempt failed. Ask customer to try again or use different payment method. |
| 5           | Refunded    | Payment has been refunded. Update order status accordingly.                        |
| 6           | Cancelled   | Payment was cancelled by the customer or system. No further action needed.         |
| 7           | Expired     | Payment session expired before completion. Create a new payment intent.            |

## Complete Integration Example

Here's a complete end-to-end example showing a full payment flow from initialization to callback handling:

```php
<?php

// 1. Initialize the SDK
require_once 'vendor/autoload.php';

use Webimpian\BayarcashSdk\Bayarcash;
use Webimpian\BayarcashSdk\Enums\PaymentChannel;

// Configuration
$personalAccessToken = 'your-personal-access-token';
$secretKey = 'your-secret-key';
$isSandbox = true;

// Create instance
$bayarcash = new Bayarcash($personalAccessToken);

if ($isSandbox) {
    $bayarcash->useSandbox();
}

// Use API v3 for enhanced features
$bayarcash->setApiVersion('v3');

// 2. Payment Intent Creation (checkout.php)
if (isset($_POST['checkout'])) {
    try {
        // Prepare payment data
        $paymentData = [
            'payment_channel' => PaymentChannel::FPX,
            'order_number' => 'ORD-' . time(),
            'amount' => $_POST['amount'],
            'payer_name' => $_POST['name'],
            'payer_email' => $_POST['email'],
            'callback_url' => 'https://your-website.com/callback.php',
            'redirect_url' => 'https://your-website.com/return.php'
        ];

        // Generate checksum
        $checksum = $bayarcash->createPaymentIntentChecksumValue($secretKey, $paymentData);
        $paymentData['checksum'] = $checksum;

        // Create payment intent
        $paymentIntent = $bayarcash->createPaymentIntent($paymentData);

        // Store payment intent ID in session for later verification
        $_SESSION['payment_intent_id'] = $paymentIntent->id;
        $_SESSION['order_number'] = $paymentData['order_number'];

        // Redirect to payment page
        header('Location: ' . $paymentIntent->url);
        exit;
    } catch (\Exception $e) {
        // Handle errors
        echo "Error: " . $e->getMessage();
    }
}

// 3. Callback Handling (callback.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get callback data
    $callbackData = $_POST;

    // Log the callback for debugging
    file_put_contents('callback_log.txt', date('Y-m-d H:i:s') . " - " . json_encode($callbackData) . "\n", FILE_APPEND);

    // Verify callback data
    $isValid = $bayarcash->verifyTransactionCallbackData($callbackData, $secretKey);

    if ($isValid) {
        // Extract transaction details
        $transactionId = $callbackData['transaction_id'];
        $orderNumber = $callbackData['order_number'];
        $status = $callbackData['status'];
        $amount = $callbackData['amount'];

        // Update database based on status
        switch ($status) {
            case '3': // Completed
                // Update order status to paid
                updateOrderStatus($orderNumber, 'paid', $transactionId);
                break;

            case '4': // Failed
                // Update order status to failed
                updateOrderStatus($orderNumber, 'failed', $transactionId);
                break;

            case '7': // Expired
                // Update order status to expired
                updateOrderStatus($orderNumber, 'expired', $transactionId);
                break;

            default:
                // Handle other statuses
                updateOrderStatus($orderNumber, 'pending', $transactionId);
        }

        // Return success response
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } else {
        // Invalid callback data
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid callback data']);
    }
}

// 4. Return URL Handling (return.php)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get return data
    $returnData = $_GET;

    // Verify return data
    $isValid = $bayarcash->verifyReturnUrlCallbackData($returnData, $secretKey);

    if ($isValid) {
        // Get transaction details
        $orderNumber = $returnData['order_number'] ?? $_SESSION['order_number'] ?? null;

        if ($orderNumber) {
            // Get transaction status
            try {
                $transaction = $bayarcash->getTransactionByOrderNumber($orderNumber);

                // Display appropriate message based on status
                switch ($transaction->status) {
                    case '3': // Completed
                        echo "<h1>Payment Successful!</h1>";
                        echo "<p>Your order #{$orderNumber} has been paid successfully.</p>";
                        break;

                    case '4': // Failed
                        echo "<h1>Payment Failed</h1>";
                        echo "<p>Your payment for order #{$orderNumber} was unsuccessful. Please try again.</p>";
                        break;

                    default:
                        echo "<h1>Payment Processing</h1>";
                        echo "<p>Your payment for order #{$orderNumber} is being processed. We'll notify you once it's complete.</p>";
                }
            } catch (\Exception $e) {
                echo "<h1>Payment Status Unknown</h1>";
                echo "<p>We couldn't retrieve the status of your payment. Please contact support.</p>";
            }
        } else {
            echo "<h1>Invalid Request</h1>";
            echo "<p>No order information found.</p>";
        }
    } else {
        echo "<h1>Invalid Request</h1>";
        echo "<p>The return data could not be verified.</p>";
    }
}

// Helper function to update order status in database
function updateOrderStatus($orderNumber, $status, $transactionId) {
    // In a real application, you would update your database
    // Example with PDO:
    /*
    $db = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');
    $stmt = $db->prepare('UPDATE orders SET status = ?, transaction_id = ? WHERE order_number = ?');
    $stmt->execute([$status, $transactionId, $orderNumber]);
    */

    // For this example, just log the update
    file_put_contents('order_updates.txt', date('Y-m-d H:i:s') . " - Order #{$orderNumber}: status updated to {$status}\n", FILE_APPEND);
}
```

## Troubleshooting

Here are solutions to common issues you might encounter when using the BayarCash PHP SDK:

### Payment Intent Creation Fails

- **Issue**: `ValidationException` when creating a payment intent
- **Solution**: Ensure all required fields are provided and formatted correctly. Common required fields include `payment_channel`, `order_number`, `amount`, `payer_name`, `payer_email`, `callback_url`, and `redirect_url`.

### Callback Verification Fails

- **Issue**: Callback verification returns `false`
- **Solution**: Verify that you're using the correct secret key and that the callback data hasn't been tampered with. Also ensure that you're using the appropriate verification method for the callback type.

### API Connection Issues

- **Issue**: Unable to connect to the BayarCash API
- **Solution**: Check your internet connection and verify that the BayarCash API is operational. If using the sandbox environment, ensure you've called `useSandbox()` on your Bayarcash instance.

### Authentication Errors

- **Issue**: `AuthenticationException` when making API requests
- **Solution**: Verify that your Personal Access Token is correct and that it hasn't expired. If you're using a new token, ensure it's properly configured in your environment variables or directly in your code.

### Incorrect API Version

- **Issue**: Missing features or unexpected API responses
- **Solution**: Check which API version you're using with `getApiVersion()` and set the appropriate version with `setApiVersion('v3')` if you need the latest features.

## Testing

The BayarCash PHP SDK includes a comprehensive PHPUnit test suite to ensure reliability and correctness. The tests cover both unit tests for individual components and feature tests for API integration.

### Running Tests

To run the tests, you need to have PHPUnit installed. It's included as a dev dependency in the composer.json file:

```bash
composer install --dev
./vendor/bin/phpunit
```

### Test Structure

The tests are organized into two main directories:

- `Unit/`: Contains unit tests for individual components of the SDK

  - `PaymentChannelTest.php`: Tests the PaymentChannel enum and its helper methods
  - `ChecksumGeneratorTest.php`: Tests the ChecksumGenerator trait and its methods
  - `WebhookSignatureTest.php`: Tests the webhook signature verification functionality

- `Feature/`: Contains feature tests that test the SDK's integration with the BayarCash API
  - `BayarcashTest.php`: Tests the main Bayarcash class and its methods

For more details about the test suite, see the [tests/README.md](tests/README.md) file.

## Support

If you encounter any issues or have questions about the SDK, please contact our support team at support@webimpian.com or raise an issue in the repository.

## License

This SDK is open-sourced software licensed under the MIT license.
