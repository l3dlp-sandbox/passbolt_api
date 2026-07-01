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
 * @since         5.14.0
 */
namespace Passbolt\OfflineMode\Test\TestCase\Middleware;

use App\Test\Lib\AppIntegrationTestCase;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\ServerRequest;
use Passbolt\OfflineMode\Middleware\OfflineModeItemsGuardMiddleware;
use Passbolt\OfflineMode\Test\Factory\OfflineModeSettingFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \Passbolt\OfflineMode\Middleware\OfflineModeItemsGuardMiddleware
 */
class OfflineModeItemsGuardMiddlewareTest extends AppIntegrationTestCase
{
    public function testOfflineModeItemsGuardMiddleware_Success_PassesThroughWhenEnabled(): void
    {
        OfflineModeSettingFactory::make()->persist();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn($response);

        $result = (new OfflineModeItemsGuardMiddleware())->process(new ServerRequest(), $handler);

        $this->assertSame($response, $result);
    }

    public function testOfflineModeItemsGuardMiddleware_Error_ForbiddenWhenDisabled(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Offline Mode is not enabled at the org level.');

        (new OfflineModeItemsGuardMiddleware())->process(new ServerRequest(), $handler);
    }
}
