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
use Passbolt\OfflineMode\Model\Table\OfflineItemsTable;
use Passbolt\OfflineMode\OfflineModePlugin;
use Passbolt\OfflineMode\Test\Factory\OfflineItemFactory;

/**
 * @covers \Passbolt\OfflineMode\Controller\Items\OfflineItemsAddController
 */
class OfflineItemsAddControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(OfflineModePlugin::class);
    }

    public function testOfflineItemsAddController_Success(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $this->logInAs($user);

        $resourceId = $resource->get('id');
        $this->postJson("/offline/resource/$resourceId.json");
        $this->assertSuccess();

        $body = $this->getResponseBodyAsArray();
        $this->assertNotEmpty($body['id']);
        $this->assertSame($user->get('id'), $body['user_id']);
        $this->assertSame(OfflineItemsTable::FOREIGN_MODEL_RESOURCE, $body['foreign_model']);
        $this->assertSame($resourceId, $body['foreign_key']);
        $this->assertSame($user->get('id'), $body['created_by']);
        $this->assertNotEmpty($body['created']);

        $this->assertSame(1, OfflineItemFactory::count());
    }

    public function testOfflineItemsAddController_Success_Idempotent(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $this->logInAs($user);

        $resourceId = $resource->get('id');
        $this->postJson("/offline/resource/$resourceId.json");
        $this->assertSuccess();
        $firstId = $this->getResponseBodyAsArray()['id'];

        $this->postJson("/offline/resource/$resourceId.json");
        $this->assertSuccess();
        $secondId = $this->getResponseBodyAsArray()['id'];

        $this->assertSame($firstId, $secondId);
        $this->assertSame(1, OfflineItemFactory::count());
    }

    public function testOfflineItemsAddController_Error_BadRequest_InvalidUuid(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $this->logInAs($user);

        $this->postJson('/offline/resource/invalid-id.json');
        $this->assertError(400, 'The resource identifier should be a valid UUID.');
    }

    public function testOfflineItemsAddController_Error_NotFound_ResourceMissing(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $this->logInAs($user);

        $missingId = '00000000-0000-0000-0000-000000000000';
        $this->postJson("/offline/resource/$missingId.json");
        $this->assertNotFoundError('The resource does not exist.');
    }

    public function testOfflineItemsAddController_Error_NotFound_ResourceSoftDeleted(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->setDeleted()->persist();
        $this->logInAs($user);

        $resourceId = $resource->get('id');
        $this->postJson("/offline/resource/$resourceId.json");
        $this->assertNotFoundError('The resource does not exist.');
    }

    public function testOfflineItemsAddController_Error_NotFound_NoAccess(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->persist(); // no permission for $user
        $this->logInAs($user);

        $resourceId = $resource->get('id');
        $this->postJson("/offline/resource/$resourceId.json");
        $this->assertNotFoundError('The resource does not exist.');
    }

    public function testOfflineItemsAddController_Error_NotAuthenticated(): void
    {
        $resource = ResourceFactory::make()->persist();
        $resourceId = $resource->get('id');

        $this->postJson("/offline/resource/$resourceId.json");
        $this->assertAuthenticationError();
    }

    public function testOfflineItemsAddController_Error_CsrfToken(): void
    {
        $this->disableCsrfToken();
        $user = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $this->logInAs($user);

        $resourceId = $resource->get('id');
        $this->postJson("/offline/resource/$resourceId.json");
        $this->assertForbiddenError('Missing or incorrect CSRF cookie type.');
    }

    public function testOfflineItemsAddController_Error_NotJson(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $this->logInAs($user);

        $resourceId = $resource->get('id');
        $this->post("/offline/resource/$resourceId");
        $this->assertResponseCode(404);
    }
}
