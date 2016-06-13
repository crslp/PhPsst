<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst;

use Illuminate\Encryption\Encrypter;
use PhPsst\Storage\FileStorage;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PhPsstTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhPsst
     */
    private $phPsst;

    /**
     */
    public function setUp()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $this->phPsst = new PhPsst($storageMock);
    }

    /**
     * @covers PhPsst\PhPsst::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('PhPsst\PhPsst', $this->phPsst);
    }

    /**
     * @covers PhPsst\PhPsst::__construct
     */
    public function testConstructWithCipher()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock, 'AES-256-CBC');
        $this->assertInstanceOf('PhPsst\PhPsst', $phPsst);
    }

    /**
     * @covers PhPsst\PhPsst::store
     * @covers PhPsst\PhPsst::generateKey
     */
    public function testNonDefaultCipher()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();
        $storageMock->expects($this->once())->method('store');

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock, 'AES-128-CBC');
        $secret = $phPsst->store('test', 300, 3);

        $this->assertContains(';', $secret);
    }

    /**
     * @covers PhPsst\PhPsst::store
     * @covers PhPsst\PhPsst::generateKey
     */
    public function testInvalidCipher()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock, 'invalid-cipher');

        $this->setExpectedException('RuntimeException');
        $phPsst->store('test', 300, 3);
    }

    /**
     * @covers PhPsst\PhPsst::store
     * @covers PhPsst\PhPsst::generateKey
     */
    public function testStore()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();
        $storageMock->expects($this->once())->method('store');

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);
        $secret = $phPsst->store('test', 300, 3);

        $this->assertContains(';', $secret);
    }

    /**
     * @covers PhPsst\PhPsst::store
     */
    public function testStoreNoKey()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->setExpectedException('InvalidArgumentException');
        $phPsst->store('');
    }

    /**
     * @covers PhPsst\PhPsst::store
     */
    public function testStoreInvalidTtl()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->setExpectedException('InvalidArgumentException');
        $phPsst->store('test', -1);
    }

    /**
     * @covers PhPsst\PhPsst::store
     */
    public function testStoreInvalidViews()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->setExpectedException('InvalidArgumentException');
        $phPsst->store('test', 300, -1);
    }

    /**
     * @covers PhPsst\PhPsst::retrieve
     */
    public function testRetrieve()
    {
        $id = uniqid();
        $key = bin2hex(random_bytes(16));
        $encryptedPassword = (new Encrypter($key, PhPsst::CIPHER_DEFAULT))->encrypt('secretMessage');
        $password = new Password('id', $encryptedPassword, 300, 3);

        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();
        $storageMock->expects($this->once())->method('get')->willReturn($password);

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $decryptedPassword = $phPsst->retrieve($id . ';' . $key);
        $this->assertEquals('secretMessage', $decryptedPassword);
    }

    /**
     * @covers PhPsst\PhPsst::retrieve
     */
    public function testRetrieveInvalidSecret()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->setExpectedException('InvalidArgumentException');
        $phPsst->retrieve('');
    }

    /**
     * @covers PhPsst\PhPsst::retrieve
     */
    public function testRetrieveNoPasswordFound()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->setExpectedException('PhPsst\PhPsstException', '', PhPsstException::NO_PASSWORD_WITH_ID_FOUND);
        $phPsst->retrieve('id;secret');
    }

}
