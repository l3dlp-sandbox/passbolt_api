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
use Passbolt\OfflineMode\Service\Items\OfflineItemsDeleteService;

class OfflineItemsDeleteController extends AppController
{
    /**
     * Unmark the caller's offline item.
     *
     * @param string $id The offline_items row id.
     * @return void
     * @throws \Cake\Http\Exception\BadRequestException Invalid uuid (raised by the service).
     * @throws \Cake\Http\Exception\NotFoundException Id missing, or row belongs to another user.
     */
    public function delete(string $id): void
    {
        $this->assertJson();

        (new OfflineItemsDeleteService())->delete($this->User->getAccessControl(), $id);

        $this->success(__('The operation was successful.'));
    }
}
