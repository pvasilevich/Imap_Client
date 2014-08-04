<?php
/**
 * Copyright 2013-2014 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category   Horde
 * @copyright  2013-2014 Horde LLC
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package    Imap_Client
 * @subpackage UnitTests
 */

/**
 * Package testing on a (live) POP3 server.
 *
 * @author     Michael Slusarz <slusarz@horde.org>
 * @category   Horde
 * @copyright  2013-2014 Horde LLC
 * @ignore
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package    Imap_Client
 * @subpackage UnitTests
 */
class Horde_Imap_Client_Live_Pop3 extends Horde_Imap_Client_Live_Base
{
    static public $config;

    static public function setUpBeforeClass()
    {
        $c = array_shift(self::$config);

        try {
            $c['client_config']['cache'] = array(
                'cacheob' => new Horde_Cache(
                    new Horde_Cache_Storage_Mock(),
                    array('compress' => true)
                )
            );
        } catch (Exception $e) {}

        self::$live = new Horde_Imap_Client_Socket_Pop3(
            $c['client_config']
        );
    }

    /* Tests */

    public function testPreLoginCommands()
    {
        $c = self::$live->capability;

        $this->assertInstanceOf(
            'Horde_Imap_Client_Data_Capability',
            $c
        );

        if (!$c->query('USER')) {
            $this->fail('Capability command failed.');
        }
    }

    /**
     * @depends testPreLoginCommands
     */
    public function testLogin()
    {
        /* Throws exception on error, which will prevent all further testing
         * on this server. */
        self::$live->login();
    }

    /**
     * @depends testLogin
     */
    public function testPostLoginCapability()
    {
        /* Re-use testPreLoginCommands(). */
        $this->testPreLoginCommands();
    }

    /**
     * @depends testLogin
     */
    public function testOpenMailbox()
    {
        self::$live->openMailbox('INBOX', Horde_Imap_Client::OPEN_READONLY);
        self::$live->openMailbox('INBOX', Horde_Imap_Client::OPEN_READWRITE);
        self::$live->openMailbox('INBOX', Horde_Imap_Client::OPEN_AUTO);
    }

    /**
     * @depends testLogin
     */
    public function testListMailbox()
    {
        // Listing all mailboxes (flat format).
        $l = self::$live->listMailboxes(
            '*',
            Horde_Imap_Client::MBOX_ALL,
            array('flat' => true)
        );

        $this->assertEquals(1, count($l));
    }

    /**
     * @depends testLogin
     */
    public function testStatus()
    {
        self::$live->status('INBOX', Horde_Imap_Client::STATUS_ALL);
    }

}
