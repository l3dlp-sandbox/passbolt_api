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
namespace Passbolt\OfflineMode\Test\TestCase\Service\Settings;

use App\Test\Lib\AppTestCase;
use Cake\Http\Exception\InternalErrorException;
use Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto;
use Passbolt\OfflineMode\Service\Settings\OfflineSettingsGetService;
use Passbolt\OfflineMode\Test\Factory\OfflineModeSettingFactory;

/**
 * @covers \Passbolt\OfflineMode\Service\Settings\OfflineSettingsGetService
 */
class OfflineSettingsGetServiceTest extends AppTestCase
{
    private OfflineSettingsGetService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new OfflineSettingsGetService();
    }

    public function tearDown(): void
    {
        unset($this->service);
        parent::tearDown();
    }

    public function testOfflineSettingsGetService_Success_ReturnsDefaultValues(): void
    {
        $dto = $this->service->get();

        $this->assertInstanceOf(OfflineSettingsDto::class, $dto);
        $result = $dto->toArray();
        $this->assertSame(OfflineSettingsDto::DEFAULT_MAX_SESSION_DURATION, $result['max_session_duration']);
        $this->assertSame(OfflineSettingsDto::DEFAULT_DATA_RETENTION_PERIOD, $result['data_retention_period']);
    }

    public function testOfflineSettingsGetService_Success_ReturnsFromDB(): void
    {
        OfflineModeSettingFactory::make()
            ->setField('value', json_encode(['max_session_duration' => 3600, 'data_retention_period' => 7200]))
            ->persist();

        $dto = $this->service->get();

        $result = $dto->toArray();
        $this->assertSame(3600, $result['max_session_duration']);
        $this->assertSame(7200, $result['data_retention_period']);
    }

    public function testOfflineSettingsGetService_Error_InvalidJsonInDB(): void
    {
        OfflineModeSettingFactory::make()
            ->setField('value', '{this is not valid JSON')
            ->persist();

        $this->expectException(InternalErrorException::class);

        $this->service->get();
    }
}
