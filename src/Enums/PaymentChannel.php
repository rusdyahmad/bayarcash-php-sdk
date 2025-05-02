<?php

namespace Webimpian\BayarcashSdk\Enums;

/**
 * Payment channel constants for BayarCash SDK
 * 
 * This enum class provides constants and helper methods for working with
 * BayarCash payment channels in a type-safe manner.
 */
class PaymentChannel
{
    /**
     * Financial Process Exchange
     */
    public const FPX = 1;
    
    /**
     * Manual Bank Transfer
     */
    public const MANUAL_TRANSFER = 2;
    
    /**
     * FPX Direct Debit
     */
    public const FPX_DIRECT_DEBIT = 3;
    
    /**
     * FPX Line of Credit
     */
    public const FPX_LINE_OF_CREDIT = 4;
    
    /**
     * DuitNow Direct On-Boarding Web
     */
    public const DUITNOW_DOBW = 5;
    
    /**
     * DuitNow QR
     */
    public const DUITNOW_QR = 6;
    
    /**
     * Shopee Pay Later
     */
    public const SPAYLATER = 7;
    
    /**
     * Boost PayFlex
     */
    public const BOOST_PAYFLEX = 8;
    
    /**
     * QRIS On-Boarding
     */
    public const QRISOB = 9;
    
    /**
     * QRIS Wallet
     */
    public const QRISWALLET = 10;
    
    /**
     * NETS
     */
    public const NETS = 11;
    
    /**
     * Get all payment channels as an associative array
     * 
     * @return array<int, string> Array of payment channels with ID as key and name as value
     */
    public static function all(): array
    {
        return [
            self::FPX => 'FPX',
            self::MANUAL_TRANSFER => 'Manual Bank Transfer',
            self::FPX_DIRECT_DEBIT => 'FPX Direct Debit',
            self::FPX_LINE_OF_CREDIT => 'FPX Line of Credit',
            self::DUITNOW_DOBW => 'DuitNow DOBW',
            self::DUITNOW_QR => 'DuitNow QR',
            self::SPAYLATER => 'Shopee Pay Later',
            self::BOOST_PAYFLEX => 'Boost PayFlex',
            self::QRISOB => 'QRIS On-Boarding',
            self::QRISWALLET => 'QRIS Wallet',
            self::NETS => 'NETS',
        ];
    }
    
    /**
     * Check if a payment channel is valid
     * 
     * @param int $channel The payment channel ID to check
     * @return bool True if the payment channel is valid, false otherwise
     */
    public static function isValid(int $channel): bool
    {
        return array_key_exists($channel, self::all());
    }
    
    /**
     * Get the name of a payment channel
     * 
     * @param int $channel The payment channel ID
     * @return string|null The payment channel name or null if not found
     */
    public static function getName(int $channel): ?string
    {
        return self::all()[$channel] ?? null;
    }
    
    /**
     * Get payment channels by type/category
     * 
     * @param string $type The type of payment channels to get (e.g., 'fpx', 'duitnow', 'qris')
     * @return array<int, string> Array of payment channels of the specified type
     */
    public static function getByType(string $type): array
    {
        $type = strtolower($type);
        $channels = self::all();
        $result = [];
        
        foreach ($channels as $id => $name) {
            $channelName = strtolower($name);
            if (strpos($channelName, $type) !== false) {
                $result[$id] = $name;
            }
        }
        
        return $result;
    }
    
    /**
     * Get a payment channel ID by its name
     * 
     * @param string $name The payment channel name (case-insensitive)
     * @return int|null The payment channel ID or null if not found
     */
    public static function getIdByName(string $name): ?int
    {
        $name = strtolower($name);
        foreach (self::all() as $id => $channelName) {
            if (strtolower($channelName) === $name) {
                return $id;
            }
        }
        
        return null;
    }
}
