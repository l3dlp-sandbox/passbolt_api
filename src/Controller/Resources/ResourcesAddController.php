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
 * @since         2.0.0
 */

namespace App\Controller\Resources;

use App\Controller\AppController;
use App\Service\Resources\ResourcesAddService;
use Passbolt\Folders\Model\Behavior\FolderizableBehavior;
use Passbolt\Metadata\Model\Dto\MetadataResourceDto;
use Passbolt\Metadata\Service\MetadataResourcesRenderService;
use Passbolt\Metadata\Utility\MetadataPopulateUserKeyIdTrait;

/**
 * @property \App\Model\Table\UsersTable $Users
 */
class ResourcesAddController extends AppController
{
    use MetadataPopulateUserKeyIdTrait;

    /**
     * Resource Add action
     *
     * @param \App\Service\Resources\ResourcesAddService $resourcesAddService Service adding the resource
     * @return void
     * @throws \Exception
     * @throws \App\Error\Exception\ValidationException if the resource is not valid.
     * @throws \Cake\Http\Exception\ServiceUnavailableException if parallel requests lead to a table lock albeit multiple attempts.
     */
    public function add(ResourcesAddService $resourcesAddService)
    {
        $this->assertJson();

        // Massage the user provided data
        $data = $this->getRequest()->getData();
        $data = $this->populatedMetadataUserKeyId($this->User->id(), $data);
        $resourceDto = new MetadataResourceDto($data);

        // Add the new resource
        $resource = $resourcesAddService->add(
            $this->User->getAccessControl(),
            $resourceDto
        );

        // Retrieve the saved resource.
        $options = [
            'contain' => [
                'creator' => true, 'favorite' => true, 'modifier' => true,
                'secret' => true, 'permission' => true,
            ],
        ];
        /** @var \App\Model\Table\ResourcesTable $Resources */
        $Resources = $this->fetchTable('Resources');
        $resource = $Resources->findView($this->User->id(), $resource->id, $options)->first();
        $resource = FolderizableBehavior::unsetPersonalPropertyIfNull($resource->toArray());
        $resource = (new MetadataResourcesRenderService())->renderResource($resource, $resourceDto->isV5());

        $this->success(__('The resource has been added successfully.'), $resource);
    }
}
