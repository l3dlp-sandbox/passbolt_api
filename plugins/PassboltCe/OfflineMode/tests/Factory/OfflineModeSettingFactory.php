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
namespace Passbolt\OfflineMode\Test\Factory;

use App\Test\Factory\OrganizationSettingFactory;
use Cake\ORM\TableRegistry;
use Passbolt\OfflineMode\Model\Table\OfflineModeSettingsTable;

/**
 * OfflineModeSettingFactory
 *
 * @method \Passbolt\OfflineMode\Model\Entity\OfflineModeSetting|\Passbolt\OfflineMode\Model\Entity\OfflineModeSetting[] persist()
 * @method \Passbolt\OfflineMode\Model\Entity\OfflineModeSetting getEntity()
 * @method \Passbolt\OfflineMode\Model\Entity\OfflineModeSetting[] getEntities()
 * @method static \Passbolt\OfflineMode\Model\Entity\OfflineModeSetting firstOrFail(\Cake\Database\ExpressionInterface|\Closure|array|string|null $conditions = null)
 */
class OfflineModeSettingFactory extends OrganizationSettingFactory
{
    /**
     * Pin the factory's table registry to the scoped class so persistence routes
     * through `beforeMarshal()` and downstream reads honour `beforeFind()`.
     *
     * @return string fully qualified class name of the scoped table.
     */
    protected function getRootTableRegistryName(): string
    {
        return OfflineModeSettingsTable::class;
    }

    /**
     * @inheritDoc
     */
    protected function setDefaultTemplate(): void
    {
        parent::setDefaultTemplate();

        /** @var \Passbolt\OfflineMode\Model\Table\OfflineModeSettingsTable $registry */
        $registry = TableRegistry::getTableLocator()->get($this->getRootTableRegistryName());
        $this->patchData([
            'property' => $registry->getProperty(),
            'property_id' => $registry->getPropertyId(),
        ]);
    }

    /**
     * Named state — canonical "enabled feature" row.
     *
     * Currently a no-op placeholder. The JSON shape lands in WP 2.2 along with
     * `OfflineSettingsDto`; this method becomes the authoritative way to seed a
     * default-enabled row once that exists.
     *
     * @return $this
     */
    public function default()
    {
        return $this;
    }
}
