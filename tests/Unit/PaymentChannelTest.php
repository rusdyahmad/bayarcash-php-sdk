<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webimpian\BayarcashSdk\Enums\PaymentChannel;

class PaymentChannelTest extends TestCase
{
    /**
     * Test that all payment channels are defined correctly
     */
    public function testAllPaymentChannelsAreDefined()
    {
        $this->assertEquals(1, PaymentChannel::FPX);
        $this->assertEquals(2, PaymentChannel::MANUAL_TRANSFER);
        $this->assertEquals(3, PaymentChannel::FPX_DIRECT_DEBIT);
        $this->assertEquals(4, PaymentChannel::FPX_LINE_OF_CREDIT);
        $this->assertEquals(5, PaymentChannel::DUITNOW_DOBW);
        $this->assertEquals(6, PaymentChannel::DUITNOW_QR);
        $this->assertEquals(7, PaymentChannel::SPAYLATER);
        $this->assertEquals(8, PaymentChannel::BOOST_PAYFLEX);
        $this->assertEquals(9, PaymentChannel::QRISOB);
        $this->assertEquals(10, PaymentChannel::QRISWALLET);
        $this->assertEquals(11, PaymentChannel::NETS);
    }

    /**
     * Test the all() method returns all payment channels
     */
    public function testAllMethodReturnsAllPaymentChannels()
    {
        $channels = PaymentChannel::all();
        
        $this->assertIsArray($channels);
        $this->assertCount(11, $channels);
        $this->assertArrayHasKey(PaymentChannel::FPX, $channels);
        $this->assertArrayHasKey(PaymentChannel::MANUAL_TRANSFER, $channels);
        $this->assertEquals('FPX', $channels[PaymentChannel::FPX]);
        $this->assertEquals('Manual Bank Transfer', $channels[PaymentChannel::MANUAL_TRANSFER]);
    }

    /**
     * Test the isValid() method correctly validates payment channels
     */
    public function testIsValidMethodValidatesPaymentChannels()
    {
        $this->assertTrue(PaymentChannel::isValid(1));
        $this->assertTrue(PaymentChannel::isValid(11));
        $this->assertFalse(PaymentChannel::isValid(0));
        $this->assertFalse(PaymentChannel::isValid(12));
    }

    /**
     * Test the getName() method returns correct channel names
     */
    public function testGetNameMethodReturnsCorrectChannelNames()
    {
        $this->assertEquals('FPX', PaymentChannel::getName(1));
        $this->assertEquals('Manual Bank Transfer', PaymentChannel::getName(2));
        $this->assertEquals('NETS', PaymentChannel::getName(11));
        $this->assertNull(PaymentChannel::getName(0));
        $this->assertNull(PaymentChannel::getName(12));
    }

    /**
     * Test the getByType() method filters channels by type
     */
    public function testGetByTypeMethodFiltersChannelsByType()
    {
        $fpxChannels = PaymentChannel::getByType('fpx');
        
        $this->assertIsArray($fpxChannels);
        $this->assertArrayHasKey(PaymentChannel::FPX, $fpxChannels);
        $this->assertArrayHasKey(PaymentChannel::FPX_DIRECT_DEBIT, $fpxChannels);
        $this->assertArrayHasKey(PaymentChannel::FPX_LINE_OF_CREDIT, $fpxChannels);
        $this->assertArrayNotHasKey(PaymentChannel::DUITNOW_QR, $fpxChannels);
        
        $duitnowChannels = PaymentChannel::getByType('duitnow');
        
        $this->assertIsArray($duitnowChannels);
        $this->assertArrayHasKey(PaymentChannel::DUITNOW_DOBW, $duitnowChannels);
        $this->assertArrayHasKey(PaymentChannel::DUITNOW_QR, $duitnowChannels);
        $this->assertArrayNotHasKey(PaymentChannel::FPX, $duitnowChannels);
    }

    /**
     * Test the getIdByName() method returns correct channel IDs
     */
    public function testGetIdByNameMethodReturnsCorrectChannelIds()
    {
        $this->assertEquals(PaymentChannel::FPX, PaymentChannel::getIdByName('FPX'));
        $this->assertEquals(PaymentChannel::MANUAL_TRANSFER, PaymentChannel::getIdByName('Manual Bank Transfer'));
        $this->assertEquals(PaymentChannel::FPX, PaymentChannel::getIdByName('fpx')); // Case insensitive
        $this->assertNull(PaymentChannel::getIdByName('Invalid Channel'));
    }
}
