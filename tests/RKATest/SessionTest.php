<?php
namespace RKATest;

use RKA\Session;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SESSION = [
            'a' => '1',
        ];
    }

    public function testGet()
    {
        $session = new Session;
        $a = $session->a;
        $b = $session->get('b', '2');

        $this->assertEquals('1', $a);
        $this->assertEquals('2', $b);
    }

    public function testSet()
    {
        $session = new Session;

        $session->set('c', '3');
        $this->assertEquals('3', $session->get('c'));

        $session->d = '4';
        $this->assertEquals('4', $session->get('d'));
    }

    public function testDelete()
    {
        $session = new Session;

        $session->set('c', '3');
        $this->assertEquals('3', $session->get('c'));

        $session->delete('c');
        $this->assertNull($session->get('c'));
    }

    public function testClearAll()
    {
        $session = new Session;

        $this->assertEquals('1', $session->get('a'));

        $session->clearAll();
        $this->assertNull($session->get('a'));
    }

    public function testIsset()
    {
        $session = new Session;
        $this->assertTrue(isset($session->a));
    }

    public function testUnset()
    {
        $session = new Session;

        $session->set('c', '3');
        $this->assertEquals('3', $session->get('c'));

        unset($session->c);
        $this->assertNull($session->get('c'));
    }

    public function testDestroy()
    {
        @session_start();
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
        @Session::destroy(); // silence headers already sent warning
        $this->assertEquals(PHP_SESSION_NONE, session_status());
    }
}
