<?php
/*
 * This file is part of the FODDBALClickHouse package -- Doctrine DBAL library
 * for ClickHouse (a column-oriented DBMS for OLAP <https://clickhouse.yandex/>)
 *
 * (c) FriendsOfDoctrine <https://github.com/FriendsOfDoctrine/>.
 *
 * For the full copyright and license inflormation, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOD\DBALClickHouse;

use Doctrine\DBAL\ConnectionException;
use ClickHouseDB\Client as Smi2CHClient;

/**
 * ClickHouse implementation for the Connection interface.
 *
 * @author Mochalygin <a@mochalygin.ru>
 */
class ClickHouseConnection implements \Doctrine\DBAL\Driver\Connection
{
    /**
     * @var Smi2CHClient
     */
    protected $smi2CHClient;

    /**
     * Connection constructor
     *
     * @param string $username      The username to use when connecting.
     * @param string $password      The password to use when connecting.
     * @param string $host
     * @param int $port
     * @param string $database
     */
    public function __construct($username = 'default', $password = '', $host = 'localhost', $port = 8123, $database = 'default')
    {
        $this->smi2CHClient = new Smi2CHClient([
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'settings' => ['database' => $database]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function prepare($prepareString)
    {
        if (! $this->smi2CHClient) {
            throw new \Exception('ClickHouse\Client was not initialized');
        }

        return new ClickHouseStatement($this->smi2CHClient, $prepareString);
    }

    /**
     * {@inheritDoc}
     */
    public function query()
    {
        $args = func_get_args();
        $stmt = $this->prepare($args[0]);
        $stmt->execute();

        return $stmt;
    }

    /**
     * {@inheritDoc}
     */
    public function quote($input, $type = \PDO::PARAM_STR)
    {
        if (\PDO::PARAM_INT == $type) {
            return $input;
        }

        return '\'' . addslashes($input) . '\'';
    }

    /**
     * {@inheritDoc}
     */
    public function exec($statement)
    {
        $stmt = $this->prepare($statement);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function lastInsertId($name = null)
    {
        throw new \LogicException('Unable to get last insert id in ClickHouse');
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        throw new \LogicException('Transactions are not allowed in ClickHouse');
    }

    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        throw new \Exception('Transactions are not allowed in ClickHouse');
    }

    /**
     * {@inheritDoc}
     */
    public function rollBack()
    {
        throw new \LogicException('Transactions are not allowed in ClickHouse');
    }

    /**
     * {@inheritDoc}
     */
    public function errorCode()
    {
        throw new \LogicException('You need to implement ClickHouseConnection::errorCode()');
    }

    /**
     * {@inheritDoc}
     */
    public function errorInfo()
    {
        throw new \LogicException('You need to implement ClickHouseConnection::errorInfo()');
    }

}