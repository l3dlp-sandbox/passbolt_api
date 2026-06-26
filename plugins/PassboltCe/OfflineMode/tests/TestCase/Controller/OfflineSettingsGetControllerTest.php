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
namespace Passbolt\OfflineMode\Test\TestCase\Controller;

use App\Test\Lib\AppIntegrationTestCase;
use Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto;
use Passbolt\OfflineMode\OfflineModePlugin;
use Passbolt\OfflineMode\Test\Factory\OfflineModeSettingFactory;

/**
 * @covers \Passbolt\OfflineMode\Controller\OfflineSettingsGetController
 */
class OfflineSettingsGetControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(OfflineModePlugin::class);
    }

    public function testOfflineSettingsGetController_Success_DefaultsWhenNoRow(): void
    {
        $this->logInAsUser();
        $this->getJson('/offline/settings.json');

        $this->assertResponseOk();
        $this->assertArrayEqualsCanonicalizing(
            [
                'max_session_duration' => OfflineSettingsDto::DEFAULT_MAX_SESSION_DURATION,
                'data_retention_period' => OfflineSettingsDto::DEFAULT_DATA_RETENTION_PERIOD,
            ],
            $this->getResponseBodyAsArray()
        );
    }

    public function testOfflineSettingsGetController_Success_StoredValuesWithAuditFields(): void
    {
        $setting = OfflineModeSettingFactory::make()
            ->setField('value', json_encode(['max_session_duration' => 3600, 'data_retention_period' => 7200]))
            ->persist();

        $this->logInAsUser();
        $this->getJson('/offline/settings.json');

        $this->assertResponseOk();
        $body = $this->getResponseBodyAsArray();
        $this->assertSame($setting->get('id'), $body['id']);
        $this->assertSame(3600, $body['max_session_duration']);
        $this->assertSame(7200, $body['data_retention_period']);
        $this->assertArrayHasKey('created', $body);
        $this->assertArrayHasKey('created_by', $body);
        $this->assertArrayHasKey('modified', $body);
        $this->assertArrayHasKey('modified_by', $body);
    }

    public function testOfflineSettingsGetController_Success_AdminSeesSameShape(): void
    {
        OfflineModeSettingFactory::make()
            ->setField('value', json_encode(['max_session_duration' => 3600, 'data_retention_period' => 7200]))
            ->persist();

        $this->logInAsAdmin();
        $this->getJson('/offline/settings.json');

        $this->assertResponseOk();
        $this->assertArrayHasKey('id', $this->getResponseBodyAsArray());
    }

    public function testOfflineSettingsGetController_Error_NotAuthenticated(): void
    {
        $this->getJson('/offline/settings.json');
        $this->assertAuthenticationError();
    }

    public function testOfflineSettingsGetController_Error_CorruptStoredValue(): void
    {
        OfflineModeSettingFactory::make()
            ->setField('value', 'not-json')
            ->persist();

        $this->logInAsUser();
        $this->getJson('/offline/settings.json');

        $this->assertResponseCode(500);
    }
}
