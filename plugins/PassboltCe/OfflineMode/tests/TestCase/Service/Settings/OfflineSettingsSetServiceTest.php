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

use App\Error\Exception\CustomValidationException;
use App\Test\Lib\AppTestCase;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\Http\Exception\ForbiddenException;
use Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto;
use Passbolt\OfflineMode\Service\Settings\OfflineSettingsSetService;
use Passbolt\OfflineMode\Test\Factory\OfflineModeSettingFactory;

/**
 * @covers \Passbolt\OfflineMode\Service\Settings\OfflineSettingsSetService
 */
class OfflineSettingsSetServiceTest extends AppTestCase
{
    private OfflineSettingsSetService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new OfflineSettingsSetService();
        EventManager::instance()->setEventList(new EventList());
    }

    public function tearDown(): void
    {
        unset($this->service);
        parent::tearDown();
    }

    public function testOfflineSettingsSetService_Success_CreatesRow(): void
    {
        $uac = $this->mockAdminAccessControl();

        $dto = $this->service->set($uac, [
            'max_session_duration' => 3600,
            'data_retention_period' => 7200,
        ]);

        $this->assertInstanceOf(OfflineSettingsDto::class, $dto);
        $this->assertSame(3600, $dto->maxSessionDuration);
        $this->assertSame(7200, $dto->dataRetentionPeriod);
        // assert entry saved in the DB
        $row = OfflineModeSettingFactory::find()->firstOrFail();
        $this->assertSame($dto->toJson(), $row->get('value'));
        $this->assertSame($uac->getId(), $row->get('created_by'));
        $this->assertSame($uac->getId(), $row->get('modified_by'));
        // assert event fired
        $this->assertEventFiredWith(OfflineSettingsSetService::EVENT_SETTINGS_UPDATED, 'dto', $dto);
        $this->assertEventFiredWith(OfflineSettingsSetService::EVENT_SETTINGS_UPDATED, 'uac', $uac);
    }

    public function testOfflineSettingsSetService_Success_UpdatesExistingRow(): void
    {
        OfflineModeSettingFactory::make()
            ->setField('value', json_encode([
                'max_session_duration' => 1000,
                'data_retention_period' => 2000,
            ]))
            ->persist();
        $uac = $this->mockAdminAccessControl();

        $dto = $this->service->set($uac, [
            'max_session_duration' => 3600,
            'data_retention_period' => 7200,
        ]);

        $this->assertSame(3600, $dto->maxSessionDuration);
        $this->assertSame(7200, $dto->dataRetentionPeriod);
        $this->assertSame(1, OfflineModeSettingFactory::find()->count());
        $this->assertSame($dto->toJson(), OfflineModeSettingFactory::find()->firstOrFail()->get('value'));
    }

    public function testOfflineSettingsSetService_Error_NotAdmin(): void
    {
        $uac = $this->mockUserAccessControl();

        $this->expectException(ForbiddenException::class);

        $this->service->set($uac, [
            'max_session_duration' => 3600,
            'data_retention_period' => 7200,
        ]);
    }

    public function testOfflineSettingsSetService_Error_InvalidPayload(): void
    {
        $uac = $this->mockAdminAccessControl();

        $this->expectException(CustomValidationException::class);

        $this->service->set($uac, [
            'max_session_duration' => -1,
            'data_retention_period' => 'not-an-int',
        ]);
    }
}
