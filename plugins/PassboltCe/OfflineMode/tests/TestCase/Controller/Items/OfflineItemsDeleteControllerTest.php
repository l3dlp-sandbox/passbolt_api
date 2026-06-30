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
namespace Passbolt\OfflineMode\Test\TestCase\Controller\Items;

use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Utility\UuidFactory;
use Passbolt\OfflineMode\OfflineModePlugin;
use Passbolt\OfflineMode\Test\Factory\OfflineItemFactory;

/**
 * @covers \Passbolt\OfflineMode\Controller\Items\OfflineItemsDeleteController
 */
class OfflineItemsDeleteControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(OfflineModePlugin::class);
    }

    public function testOfflineItemsDeleteController_Success(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $offlineItem = OfflineItemFactory::make()->setUser($user)->setResource($resource)->persist();
        $this->logInAs($user);

        $id = $offlineItem->get('id');
        $this->deleteJson("/offline/item/$id.json");
        $this->assertSuccess();

        $this->assertSame(0, OfflineItemFactory::count());
    }

    public function testOfflineItemsDeleteController_Error_InvalidUuid(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $this->logInAs($user);
        $this->deleteJson('/offline/item/invalid-id.json');
        $this->assertBadRequestError('The offline item identifier should be a valid UUID');
    }

    public function testOfflineItemsDeleteController_Error_IdDoesNotExist(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $this->logInAs($user);

        $missingId = UuidFactory::uuid('not-here');
        $this->deleteJson("/offline/item/$missingId.json");
        $this->assertNotFoundError('The offline item does not exist.');
    }

    public function testOfflineItemsDeleteController_Error_RowBelongsToAnotherUser(): void
    {
        $owner = UserFactory::make()->user()->active()->persist();
        $intruder = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($owner)->persist();
        $offlineItem = OfflineItemFactory::make()->setUser($owner)->setResource($resource)->persist();
        $this->logInAs($intruder);

        $id = $offlineItem->get('id');
        $this->deleteJson("/offline/item/$id.json");

        $this->assertNotFoundError('The offline item does not exist.');
        $this->assertSame(1, OfflineItemFactory::count());
    }

    public function testOfflineItemsDeleteController_Error_NotAuthenticated(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $offlineItem = OfflineItemFactory::make()->setUser($user)->setResource($resource)->persist();

        $id = $offlineItem->get('id');
        $this->deleteJson("/offline/item/$id.json");

        $this->assertAuthenticationError();
    }

    public function testOfflineItemsDeleteController_Error_CsrfToken(): void
    {
        $this->disableCsrfToken();
        $user = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $offlineItem = OfflineItemFactory::make()->setUser($user)->setResource($resource)->persist();
        $this->logInAs($user);

        $id = $offlineItem->get('id');
        $this->deleteJson("/offline/item/$id.json");
        $this->assertForbiddenError('Missing or incorrect CSRF cookie type.');
    }

    public function testOfflineItemsDeleteController_Error_NotJson(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $offlineItem = OfflineItemFactory::make()->setUser($user)->setResource($resource)->persist();
        $this->logInAs($user);

        $id = $offlineItem->get('id');
        $this->delete("/offline/item/$id");

        $this->assertResponseCode(404);
    }
}
