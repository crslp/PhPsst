<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst;

/**
 */
interface StorageInterface
{
    /**
     * @param Password $password
     * @return void
     */
    public function insert(Password $password);

    /**
     * @param Password $password
     * @return void
     */
    public function update(Password $password);

    /**
     * @param $key
     * @return Password
     */
    public function get($key);

    /**
     * @param Password $password
     * @return void
     */
    public function delete(Password $password);
}
