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
namespace Passbolt\OfflineMode\Service;

use App\Error\Exception\ValidationException;
use App\Utility\UserAccessControl;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Validation\Validation;
use Passbolt\OfflineMode\Model\Entity\OfflineItem;
use Passbolt\OfflineMode\Model\Table\OfflineItemsTable;

/**
 * Marks a foreign object (phase 1: a resource) as available offline for the
 * caller. Idempotent: returns the existing row if the user has already marked
 * the same `(foreign_model, foreign_key)`.
 */
class OfflineItemsAddService
{
    use LocatorAwareTrait;

    /**
     * @param \App\Utility\UserAccessControl $uac The caller.
     * @param string $foreignModel Object type (`resource` for phase 1).
     * @param string $foreignKey The target object id.
     * @return \Passbolt\OfflineMode\Model\Entity\OfflineItem
     * @throws \Cake\Http\Exception\BadRequestException Invalid uuid or unknown foreign model.
     * @throws \Cake\Http\Exception\NotFoundException Resource missing, soft-deleted, or no read access.
     */
    public function add(UserAccessControl $uac, string $foreignModel, string $foreignKey): OfflineItem
    {
        if (!in_array($foreignModel, OfflineItemsTable::ALLOWED_FOREIGN_MODELS, true)) {
            throw new BadRequestException(__(
                'The offline item object type should be one of the following: {0}.',
                implode(', ', OfflineItemsTable::ALLOWED_FOREIGN_MODELS)
            ));
        }
        if (!Validation::uuid($foreignKey)) {
            throw new BadRequestException(__('The resource identifier should be a valid UUID.'));
        }

        /** @var \Passbolt\OfflineMode\Model\Table\OfflineItemsTable $OfflineItems */
        $OfflineItems = $this->fetchTable('Passbolt/OfflineMode.OfflineItems');

        // Idempotency: a second mark on the same (user, model, key) returns the
        // existing row rather than erroring. The endpoint contract is 200 OK
        // on both first mark and re-mark.
        /** @var \Passbolt\OfflineMode\Model\Entity\OfflineItem|null $existing */
        $existing = $OfflineItems->find()
            ->where([
                'user_id' => $uac->getId(),
                'foreign_model' => $foreignModel,
                'foreign_key' => $foreignKey,
            ])
            ->first();
        if ($existing !== null) {
            return $existing;
        }

        // The entity defaults to `$_accessible = ['*' => false]`. Open the
        // four service-pinned fields per call via `accessibleFields` rather
        // than relaxing the entity-level whitelist — keeps mass-assignment
        // out of any future caller that builds entities from raw input.
        // `created` is stamped by TimestampBehavior on save.
        $entity = $OfflineItems->newEntity(
            [
                'user_id' => $uac->getId(),
                'foreign_model' => $foreignModel,
                'foreign_key' => $foreignKey,
                'created_by' => $uac->getId(),
            ],
            [
                'accessibleFields' => [
                    'user_id' => true,
                    'foreign_model' => true,
                    'foreign_key' => true,
                    'created_by' => true,
                ],
            ]
        );
        $this->handleValidationErrors($entity, $OfflineItems);

        $OfflineItems->save($entity);
        $this->handleValidationErrors($entity, $OfflineItems);

        return $entity;
    }

    /**
     * Map known buildRules / validation errors to HTTP exceptions.
     *
     * @param \Passbolt\OfflineMode\Model\Entity\OfflineItem $entity Entity to inspect.
     * @param \Passbolt\OfflineMode\Model\Table\OfflineItemsTable $table The owning table.
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException Resource missing, soft-deleted, or no access.
     * @throws \App\Error\Exception\ValidationException Any other unhandled validation error.
     */
    private function handleValidationErrors(OfflineItem $entity, OfflineItemsTable $table): void
    {
        $errors = $entity->getErrors();
        if (empty($errors)) {
            return;
        }

        if (
            isset($errors['foreign_key']['resource_exists'])
            || isset($errors['foreign_key']['resource_is_not_soft_deleted'])
            || isset($errors['foreign_key']['has_resource_access'])
        ) {
            throw new NotFoundException(__('The resource does not exist.'));
        }
        throw new ValidationException(__('Could not validate offline item data.'), $entity, $table);
    }
}
