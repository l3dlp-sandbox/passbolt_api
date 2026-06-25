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

use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto;

class OfflineSettingsGetService
{
    use LocatorAwareTrait;

    /**
     * Read the offline-mode settings row and return a typed DTO.
     * Returns default values when no row exists.
     *
     * @return \Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto
     * @throws \Cake\Http\Exception\InternalErrorException When the stored value is not a decodable JSON object.
     */
    public function get(): OfflineSettingsDto
    {
        /** @var \Passbolt\OfflineMode\Model\Table\OfflineModeSettingsTable $offlineModeSettingsTable */
        $offlineModeSettingsTable = $this->fetchTable('Passbolt/OfflineMode.OfflineModeSettings');

        /** @var \Passbolt\OfflineMode\Model\Entity\OfflineModeSetting|null $offlineModeSetting */
        $offlineModeSetting = $offlineModeSettingsTable->find()->first();

        if ($offlineModeSetting !== null) {
            $raw = $offlineModeSetting->get('value');
            if (!is_string($raw)) {
                throw new InternalErrorException('The offline settings value should be a JSON string.');
            }
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                throw new InternalErrorException('The offline settings value should decode to an array.');
            }

            return OfflineSettingsDto::createFromArray($decoded);
        }

        return OfflineSettingsDto::createFromArray([
            'max_session_duration' => OfflineSettingsDto::DEFAULT_MAX_SESSION_DURATION,
            'data_retention_period' => OfflineSettingsDto::DEFAULT_DATA_RETENTION_PERIOD,
        ]);
    }
}
