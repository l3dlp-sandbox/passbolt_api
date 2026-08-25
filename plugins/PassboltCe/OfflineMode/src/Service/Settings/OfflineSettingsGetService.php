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
 * @since         5.16.0
 */
namespace Passbolt\OfflineMode\Service\Settings;

use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto;

class OfflineSettingsGetService
{
    use LocatorAwareTrait;

    /**
     * Read the offline-mode settings. Returns null when the org has not configured the feature.
     *
     * @return \Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto|null
     */
    public function get(): ?OfflineSettingsDto
    {
        /** @var \Passbolt\OfflineMode\Model\Table\OfflineModeSettingsTable $table */
        $table = $this->fetchTable('Passbolt/OfflineMode.OfflineModeSettings');
        /** @var \Passbolt\OfflineMode\Model\Entity\OfflineModeSetting|null $entity */
        $entity = $table->find()->first();
        if ($entity === null) {
            return null;
        }

        return OfflineSettingsDto::createFromEntity($entity);
    }

    /**
     * Whether Offline Mode is enabled for the organisation.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->get() !== null;
    }

    /**
     * @return void
     * @throws \Cake\Http\Exception\ForbiddenException When Offline Mode is not enabled.
     */
    public function throwExceptionIfDisabled(): void
    {
        if (!$this->isEnabled()) {
            throw new ForbiddenException(__('Offline Mode is not enabled at the org level.'));
        }
    }
}
