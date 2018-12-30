<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2018 Felix Sandström
 * @license   MIT
 */

namespace PhPsst;

use Illuminate\Encryption\Encrypter;
use PhPsst\Storage\Storage;

/**
 * A PHP library for distributing (one time) passwords/secrets in a more secure way.
 *
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PhPsst
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var string
     */
    protected $cipher;

    /**
     * @const string
     */
    public const CIPHER_DEFAULT = 'AES-256-CBC';

    public function __construct(Storage $storage, string $cipher = null)
    {
        $this->storage = $storage;
        if ($cipher !== null) {
            $this->cipher = $cipher;
        } else {
            $this->cipher = self::CIPHER_DEFAULT;
        }
    }

    public function store(string $password, int $ttl = 3600, int $views = 1): string
    {
        if (empty($password)) {
            throw new \InvalidArgumentException('The password has to be set');
        }

        if ($ttl < 1) {
            throw new \InvalidArgumentException('TTL has to be higher than 0');
        }

        if ($views < 1) {
            throw new \InvalidArgumentException('Views has to be highter han 0');
        }

        $id = uniqid('', false);
        $key = $this->generateKey();
        $encrypter = new Encrypter($key, $this->cipher);

        $this->storage->store(new Password($id, $encrypter->encrypt($password), ($ttl + time()), $views));

        return $id . ';' . $key;
    }

    public function retrieve(string $secret): string
    {
        $idKeyArray = explode(';', $secret);
        if (\count($idKeyArray) !== 2) {
            throw new \InvalidArgumentException('Invalid secret');
        }
        [$id, $key] = $idKeyArray;
        $id = preg_replace("/[^a-zA-Z\d]/", '', $id);

        if (!($password = $this->storage->get($id))) {
            throw new PhPsstException('No password with that ID found', PhPsstException::NO_PASSWORD_WITH_ID_FOUND);
        }
        $encrypter = new Encrypter($key, $this->cipher);

        $password->decreaseViews();
        if ($password->getViews() > 0) {
            $this->storage->store($password, true);
        } else {
            $this->storage->delete($password);
        }

        return $encrypter->decrypt($password->getPassword());
    }

    protected function generateKey(): string
    {
        switch ($this->cipher) {
            case 'AES-128-CBC':
                $key = bin2hex(random_bytes(8));
                break;
            case 'AES-256-CBC':
                $key = bin2hex(random_bytes(16));
                break;
            default:
                throw new \RuntimeException('Only supported ciphers are AES-128-CBC and AES-256-CBC');
        }

        return $key;
    }
}
