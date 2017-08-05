<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst;

use Illuminate\Encryption\Encrypter;
use InvalidArgumentException;
use PhPsst\Storage\FileStorage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PhPsstTest extends TestCase
{
    /**
     * @var PhPsst
     */
    private $phPsst;

    /**
     */
    public function setUp()
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $this->phPsst = new PhPsst($storageMock);
    }

    /**
     * @covers PhPsst\PhPsst::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf(PhPsst::class, $this->phPsst);
    }

    /**
     * @covers PhPsst\PhPsst::__construct
     */
    public function testConstructWithCipher()
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock, 'AES-256-CBC');
        $this->assertInstanceOf(PhPsst::class, $phPsst);
    }

    /**
     * @covers PhPsst\PhPsst::store
     * @covers PhPsst\PhPsst::generateKey
     */
    public function testNonDefaultCipher()
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();
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
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock, 'invalid-cipher');

        $this->expectException(RuntimeException::class);
        $phPsst->store('test', 300, 3);
    }

    /**
     * @covers PhPsst\PhPsst::store
     * @covers PhPsst\PhPsst::generateKey
     */
    public function testStore()
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();
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
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->expectException(InvalidArgumentException::class);
        $phPsst->store('');
    }

    /**
     * @covers PhPsst\PhPsst::store
     */
    public function testStoreInvalidTtl()
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->expectException(InvalidArgumentException::class);
        $phPsst->store('test', -1);
    }

    /**
     * @covers PhPsst\PhPsst::store
     */
    public function testStoreInvalidViews()
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->expectException(InvalidArgumentException::class);
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

        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();
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
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->expectException(InvalidArgumentException::class);
        $phPsst->retrieve('');
    }

    /**
     * @covers PhPsst\PhPsst::retrieve
     */
    public function testRetrieveNoPasswordFound()
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->expectException(PhPsstException::class);
        $this->expectExceptionCode(PhPsstException::NO_PASSWORD_WITH_ID_FOUND);
        $phPsst->retrieve('id;secret');
    }

}
