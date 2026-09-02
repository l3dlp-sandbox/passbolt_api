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
namespace App\Test\TestCase\Cache\Engine;

use App\Cache\Engine\PhpRedisEngine;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Redis;
use RedisException;

#[CoversClass(PhpRedisEngine::class)]
class PhpRedisEngineTest extends TestCase
{
    private const CA_FILE = '/etc/ssl/certs/ca.pem';
    private const KEY_FILE = '/etc/ssl/private/redis.key';
    private const CERT_FILE = '/etc/ssl/certs/redis.crt';

    public function setUp(): void
    {
        parent::setUp();
        $this->skipIf(!extension_loaded('redis'), 'The redis extension is not installed.');
    }

    /**
     * Creates an engine that connects with the given phpredis client.
     *
     * @param \Redis $phpredis The phpredis client.
     * @return \App\Cache\Engine\PhpRedisEngine
     */
    private function createRedisEngineMock(Redis $phpredis): PhpRedisEngine
    {
        $engine = $this->getMockBuilder(PhpRedisEngine::class)
            ->onlyMethods(['_createRedisInstance'])
            ->getMock();
        $engine->method('_createRedisInstance')->willReturn($phpredis);

        return $engine;
    }

    /**
     * Creates a phpredis client that accepts the database selection.
     *
     * @return \Redis&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createRedisInstanceMock(): Redis&MockObject
    {
        $phpredis = $this->createMock(Redis::class);
        $phpredis->method('select')->willReturn(true);

        return $phpredis;
    }

    public function testPhpRedisEngine_ConnectTransient_Success_StreamContext(): void
    {
        $phpredis = $this->createRedisInstanceMock();
        $phpredis->expects($this->once())
            ->method('connect')
            ->with(
                'tls://127.0.0.1',
                6379,
                0.0,
                null,
                0,
                0.0,
                ['stream' => ['cafile' => self::CA_FILE]],
            )
            ->willReturn(true);

        $result = $this->createRedisEngineMock($phpredis)->init([
            'persistent' => false,
            'tls' => true,
            'ssl_ca' => self::CA_FILE,
        ]);

        $this->assertTrue($result);
    }

    public function testPhpRedisEngine_ConnectTransient_Success_AllStreamOptions(): void
    {
        $phpredis = $this->createRedisInstanceMock();
        $phpredis->expects($this->once())
            ->method('connect')
            ->with(
                '127.0.0.1',
                6379,
                0.0,
                null,
                0,
                0.0,
                ['stream' => [
                    'cafile' => self::CA_FILE,
                    'local_pk' => self::KEY_FILE,
                    'local_cert' => self::CERT_FILE,
                ]],
            )
            ->willReturn(true);

        $result = $this->createRedisEngineMock($phpredis)->init([
            'persistent' => false,
            'ssl_ca' => self::CA_FILE,
            'ssl_key' => self::KEY_FILE,
            'ssl_cert' => self::CERT_FILE,
        ]);

        $this->assertTrue($result);
    }

    public function testPhpRedisEngine_ConnectTransient_Success_NoContext(): void
    {
        $phpredis = $this->createRedisInstanceMock();
        $phpredis->expects($this->once())
            ->method('connect')
            ->with('127.0.0.1', 6379, 0.0, null, 0, 0.0, null)
            ->willReturn(true);

        $result = $this->createRedisEngineMock($phpredis)->init(['persistent' => false]);

        $this->assertTrue($result);
    }

    public function testPhpRedisEngine_ConnectPersistent_Success_StreamContext(): void
    {
        $phpredis = $this->createRedisInstanceMock();
        $phpredis->expects($this->once())
            ->method('pconnect')
            ->with(
                'tls://127.0.0.1',
                6379,
                0.0,
                '637900',
                0,
                0.0,
                ['stream' => ['cafile' => self::CA_FILE]],
            )
            ->willReturn(true);

        $result = $this->createRedisEngineMock($phpredis)->init([
            'tls' => true,
            'ssl_ca' => self::CA_FILE,
        ]);

        $this->assertTrue($result);
    }

    public function testPhpRedisEngine_ConnectPersistent_Success_PersistentId(): void
    {
        $phpredis = $this->createRedisInstanceMock();
        $phpredis->expects($this->once())
            ->method('pconnect')
            ->with(
                '127.0.0.1',
                6380,
                2.0,
                '638023',
                0,
                0.0,
                ['stream' => ['cafile' => self::CA_FILE]],
            )
            ->willReturn(true);

        $result = $this->createRedisEngineMock($phpredis)->init([
            'port' => 6380,
            'timeout' => 2,
            'database' => 3,
            'ssl_ca' => self::CA_FILE,
        ]);

        $this->assertTrue($result);
    }

    public function testPhpRedisEngine_ConnectPersistent_Success_NoContext(): void
    {
        $phpredis = $this->createRedisInstanceMock();
        $phpredis->expects($this->once())
            ->method('pconnect')
            ->with('127.0.0.1', 6379, 0.0, '637900', 0, 0.0, null)
            ->willReturn(true);

        $result = $this->createRedisEngineMock($phpredis)->init([]);

        $this->assertTrue($result);
    }

    public function testPhpRedisEngine_Connect_Error_ConnectionFailure(): void
    {
        $phpredis = $this->createMock(Redis::class);
        $phpredis->method('connect')->willThrowException(new RedisException('Connection refused.'));

        $result = $this->createRedisEngineMock($phpredis)->init([
            'persistent' => false,
            'ssl_ca' => self::CA_FILE,
        ]);

        $this->assertFalse($result);
    }
}
