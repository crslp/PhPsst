<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Password
     */
    private $password;

    /**
     */
    public function setUp()
    {
        $this->password = new Password('id', 'password', 123, 3);
    }

    /**
     * @covers PhPsst\Password::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('PhPsst\Password', $this->password);
    }

    /**
     * @covers PhPsst\Password::getId
     */
    public function testGetId()
    {
        $this->assertEquals('id', $this->password->getId());
    }

    /**
     * @covers PhPsst\Password::getPassword
     */
    public function testGetPassword()
    {
        $this->assertEquals('password', $this->password->getPassword());
    }

    /**
     * @covers PhPsst\Password::getTtl
     */
    public function testGetTtl()
    {
        $this->assertEquals(123, $this->password->getTtl());
    }

    /**
     * @covers PhPsst\Password::getViews
     */
    public function testGetViews()
    {
        $this->assertEquals(3, $this->password->getViews());
    }

    /**
     * @covers PhPsst\Password::decreaseViews
     */
    public function testDecreaseViews()
    {
        $password = new Password('id', 'password', 123, 2);
        $this->assertEquals(2, $password->getViews());
        $password->decreaseViews();
        $this->assertEquals(1, $password->getViews());
        $password->decreaseViews();
        $this->assertEquals(0, $password->getViews());
    }

    /**
     * @covers PhPsst\Password::decreaseViews
     */
    public function testDecreaseViewsException()
    {
        $this->setExpectedException('LogicException');
        $password = new Password('id', 'password', 123, 1);
        $password->decreaseViews();
        $password->decreaseViews();
    }
}
