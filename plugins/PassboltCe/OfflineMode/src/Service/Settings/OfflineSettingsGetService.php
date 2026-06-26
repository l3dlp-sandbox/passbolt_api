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
namespace Passbolt\OfflineMode\Service\Settings;

use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto;
use Passbolt\OfflineMode\Model\Entity\OfflineModeSetting;

class OfflineSettingsGetService
{
    use LocatorAwareTrait;

    /**
     * Cached settings row.
     *
     * @var \Passbolt\OfflineMode\Model\Entity\OfflineModeSetting|null
     */
    private ?OfflineModeSetting $cachedEntity = null;

    /**
     * Read the offline-mode settings.
     *
     * @return \Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto
     */
    public function get(): OfflineSettingsDto
    {
        $entity = $this->getEntity();
        if ($entity !== null) {
            return OfflineSettingsDto::createFromEntity($entity);
        }

        return OfflineSettingsDto::createFromArray([
            'max_session_duration' => OfflineSettingsDto::DEFAULT_MAX_SESSION_DURATION,
            'data_retention_period' => OfflineSettingsDto::DEFAULT_DATA_RETENTION_PERIOD,
        ]);
    }

    /**
     * Whether Offline Mode is enabled for the organisation.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->get()->id !== null;
    }

    /**
     * @return \Passbolt\OfflineMode\Model\Entity\OfflineModeSetting|null
     */
    private function getEntity(): ?OfflineModeSetting
    {
        if (is_null($this->cachedEntity)) {
            /** @var \Passbolt\OfflineMode\Model\Table\OfflineModeSettingsTable $table */
            $table = $this->fetchTable('Passbolt/OfflineMode.OfflineModeSettings');
            /** @var \Passbolt\OfflineMode\Model\Entity\OfflineModeSetting|null $entity */
            $entity = $table->find()->first();
            $this->cachedEntity = $entity;
        }

        return $this->cachedEntity;
    }
}
