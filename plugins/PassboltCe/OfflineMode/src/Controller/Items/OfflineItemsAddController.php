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
namespace Passbolt\OfflineMode\Controller\Items;

use App\Controller\AppController;
use Passbolt\OfflineMode\Model\Table\OfflineItemsTable;
use Passbolt\OfflineMode\Service\Items\OfflineItemsAddService;

/**
 * HTTP entry point for `POST /offline/resource/<uuid>.json`.
 */
class OfflineItemsAddController extends AppController
{
    /**
     * Mark a resource as available offline for the authenticated user.
     *
     * @param string $foreignKey The target resource id.
     * @return void
     * @throws \Cake\Http\Exception\BadRequestException Invalid uuid (raised by the service).
     * @throws \Cake\Http\Exception\NotFoundException Resource missing / soft-deleted / no access.
     */
    public function add(string $foreignKey): void
    {
        $this->assertJson();

        $result = (new OfflineItemsAddService())->add(
            $this->User->getAccessControl(),
            OfflineItemsTable::FOREIGN_MODEL_RESOURCE,
            $foreignKey
        );

        $this->success(__('The operation was successful.'), $result);
    }
}
