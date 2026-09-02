<?php
declare(strict_types=1);

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         5.16.0
 */
namespace App\Cache\Engine;

use Cake\Cache\Engine\RedisEngine;

/**
 * A Redis cache engine compatible with the phpredis extension.
 * It sends the stream context optioms to the `Redis::connect()` and `Redis::pconnect()` instead of ssl.
 *
 * The `stream` and `auth` are the only possible options here.
 *
 * @link https://github.com/phpredis/phpredis/blob/develop/redis.c#L589
 */
class PhpRedisEngine extends RedisEngine
{
    /**
     * Connects to a Redis server with a new connection.
     *
     * @param string $server Server to connect to.
     * @param array $ssl Stream context options. The options are flat,
     *   because phpredis adds the `ssl` wrapper itself.
     * @return bool True if the Redis server was connected.
     * @throws \RedisException When the connection cannot be established.
     */
    protected function _connectTransient(string $server, array $ssl): bool
    {
        if ($ssl === []) {
            return parent::_connectTransient($server, $ssl);
        }

        return $this->_Redis->connect(
            $server,
            (int)$this->_config['port'],
            (int)$this->_config['timeout'],
            null,
            0,
            0.0,
            ['stream' => $ssl],
        );
    }

    /**
     * Connects to a Redis server with a persistent connection.
     *
     * @param string $server Server to connect to.
     * @param array $ssl Stream context options. The options are flat,
     *   because phpredis adds the `ssl` wrapper itself.
     * @return bool True if the Redis server was connected.
     * @throws \RedisException When the connection cannot be established.
     */
    protected function _connectPersistent(string $server, array $ssl): bool
    {
        if ($ssl === []) {
            return parent::_connectPersistent($server, $ssl);
        }

        $persistentId = $this->_config['port'] . $this->_config['timeout'] . $this->_config['database'];

        return $this->_Redis->pconnect(
            $server,
            (int)$this->_config['port'],
            (int)$this->_config['timeout'],
            $persistentId,
            0,
            0.0,
            ['stream' => $ssl],
        );
    }
}
