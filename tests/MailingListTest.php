<?php

use PHPUnit\Framework\TestCase;
use App\MailingList;

class MailingListTest extends TestCase
{
    public function testCanConnectToDatabase()
    {
        $this->assertInstanceOf(PDO::class, MailingList::getInstance());
    }

    /**
     * @depends testCanConnectToDatabase
     */
    public function testUpload()
    {
        $this->assertSame(10000, MailingList::upload('mailing_list.csv'));
    }

    /**
     * @depends testUpload
     */
    public function testSendAll()
    {
        $this->assertSame(10000, MailingList::sendAll(1, 'test', 'test'));
    }

    /**
     * @depends testSendAll
     */
    public function testPreventResending()
    {
        $this->assertSame(0, MailingList::sendAll(1, 'test', 'test'));
    }

    /**
     * @depends testUpload
     */
    public function testSend4000() {
        $this->assertSame(4000, MailingList::sendAll(2, 'test', 'test', 4000));
    }

    /**
     * @depends testSend4000
     */
    public function testSendAnother6000() {
        $this->assertSame(6000, MailingList::sendAll(2, 'test', 'test'));
    }
}
