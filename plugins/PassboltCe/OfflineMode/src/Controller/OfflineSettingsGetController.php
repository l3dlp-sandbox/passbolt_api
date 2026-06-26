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
namespace Passbolt\OfflineMode\Controller;

use App\Controller\AppController;
use Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto;
use Passbolt\OfflineMode\Service\Settings\OfflineSettingsGetService;

class OfflineSettingsGetController extends AppController
{
    /**
     * Read the org-level Offline Mode settings.
     *
     * @return void
     */
    public function get(): void
    {
        $this->assertJson();
        $dto = (new OfflineSettingsGetService())->get();
        $this->success(__('The operation was successful.'), $this->filterNullAuditFields($dto));
    }

    /**
     * Strip null audit properties so the wire body only carries fields that actually have
     * values. When no settings row exists, this leaves just the two settings keys — the
     * absence of `id` is the client's "not configured" signal (specs.md §14.2).
     *
     * @param \Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto $dto Source DTO.
     * @return \Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto
     */
    private function filterNullAuditFields(OfflineSettingsDto $dto): OfflineSettingsDto
    {
        foreach (['id', 'created', 'created_by', 'modified', 'modified_by'] as $property) {
            if ($dto->{$property} === null) {
                unset($dto->{$property});
            }
        }

        return $dto;
    }
}
