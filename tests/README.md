# Testing the BayarCash PHP SDK

This directory contains unit and feature tests for the BayarCash PHP SDK. The tests are written using PHPUnit and are designed to ensure that the SDK functions correctly.

## Running Tests

To run the tests, you need to have PHPUnit installed. You can install it via Composer:

```bash
composer install
```

Then, you can run the tests using the PHPUnit command:

```bash
./vendor/bin/phpunit
```

## Test Structure

The tests are organized into two main directories:

- `Unit/`: Contains unit tests for individual components of the SDK
- `Feature/`: Contains feature tests that test the SDK's integration with the BayarCash API

### Unit Tests

Unit tests focus on testing individual components of the SDK in isolation:

- `PaymentChannelTest.php`: Tests the PaymentChannel enum and its helper methods
- `ChecksumGeneratorTest.php`: Tests the ChecksumGenerator trait and its methods
- `WebhookSignatureTest.php`: Tests the webhook signature verification functionality

### Feature Tests

Feature tests focus on testing the SDK's integration with the BayarCash API:

- `BayarcashTest.php`: Tests the main Bayarcash class and its methods, using mocked HTTP responses

## Writing New Tests

When adding new features to the SDK, please also add corresponding tests to ensure that the features work as expected. Follow the existing test structure and naming conventions.

## Test Environment

The tests use environment variables defined in the `phpunit.xml` file:

```xml
<php>
    <env name="BAYARCASH_API_TOKEN" value="test-token"/>
    <env name="BAYARCASH_API_SECRET_KEY" value="test-secret-key"/>
    <env name="BAYARCASH_SANDBOX" value="true"/>
</php>
```

These values are used for testing purposes only and are not real credentials.
