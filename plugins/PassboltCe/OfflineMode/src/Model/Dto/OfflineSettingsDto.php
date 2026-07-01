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
namespace Passbolt\OfflineMode\Model\Dto;

use Cake\I18n\DateTime;
use InvalidArgumentException;
use Passbolt\OfflineMode\Model\Entity\OfflineModeSetting;

class OfflineSettingsDto
{
    public const DEFAULT_MAX_SESSION_DURATION = 86400;
    public const DEFAULT_DATA_RETENTION_PERIOD = 120000;

    public int $max_session_duration;
    public int $data_retention_period;
    public ?string $id = null;
    public ?DateTime $created = null;
    public ?string $created_by = null;
    public ?DateTime $modified = null;
    public ?string $modified_by = null;

    /**
     * @param int $maxSessionDuration Maximum session duration (in seconds).
     * @param int $dataRetentionPeriod Data retention period (in seconds).
     * @param string|null $id Backing organization-settings row id, when present.
     * @param \Cake\I18n\DateTime|null $created Row creation timestamp, when present.
     * @param string|null $createdBy Creator user id, when present.
     * @param \Cake\I18n\DateTime|null $modified Row modification timestamp, when present.
     * @param string|null $modifiedBy Last-modifier user id, when present.
     */
    public function __construct(
        int $maxSessionDuration,
        int $dataRetentionPeriod,
        ?string $id = null,
        ?DateTime $created = null,
        ?string $createdBy = null,
        ?DateTime $modified = null,
        ?string $modifiedBy = null
    ) {
        $this->max_session_duration = $maxSessionDuration;
        $this->data_retention_period = $dataRetentionPeriod;
        $this->id = $id;
        $this->created = $created;
        $this->created_by = $createdBy;
        $this->modified = $modified;
        $this->modified_by = $modifiedBy;
    }

    /**
     * @param array $data Data to create DTO from.
     * @return self
     * @throws \InvalidArgumentException When data assertions fail.
     */
    public static function createFromArray(array $data): self
    {
        self::assertMaxSessionDuration($data);
        self::assertDataRetentionPeriod($data);

        return new self(
            $data['max_session_duration'],
            $data['data_retention_period'],
            $data['id'] ?? null,
            $data['created'] ?? null,
            $data['created_by'] ?? null,
            $data['modified'] ?? null,
            $data['modified_by'] ?? null,
        );
    }

    /**
     * Build a DTO from a persisted entity. The table's JSON column cast already decoded
     * the `value` column to an array; this method merges audit columns onto it.
     *
     * @param \Passbolt\OfflineMode\Model\Entity\OfflineModeSetting $entity Persisted settings row.
     * @return self
     * @throws \InvalidArgumentException When the stored `value` is not an array.
     */
    public static function createFromEntity(OfflineModeSetting $entity): self
    {
        $value = $entity->get('value');
        if (!is_array($value)) {
            throw new InvalidArgumentException('OfflineSettingsDto: entity `value` must be an array.');
        }

        $value['id'] = $entity->get('id');
        $value['created'] = $entity->get('created');
        $value['created_by'] = $entity->get('created_by');
        $value['modified'] = $entity->get('modified');
        $value['modified_by'] = $entity->get('modified_by');

        return self::createFromArray($value);
    }

    /**
     * Array representation of the DTO.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'max_session_duration' => $this->max_session_duration,
            'data_retention_period' => $this->data_retention_period,
            'created' => $this->created,
            'created_by' => $this->created_by,
            'modified' => $this->modified,
            'modified_by' => $this->modified_by,
        ];
    }

    /**
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException When the max_session_duration value doesn't pass the assertions.
     */
    private static function assertMaxSessionDuration(array $data): void
    {
        if (!array_key_exists('max_session_duration', $data) || !is_int($data['max_session_duration'])) {
            throw new InvalidArgumentException(
                'OfflineSettingsDto: `max_session_duration` is required and must be an integer.'
            );
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException When the data_retention_period value doesn't pass the assertions.
     */
    private static function assertDataRetentionPeriod(array $data): void
    {
        if (!array_key_exists('data_retention_period', $data) || !is_int($data['data_retention_period'])) {
            throw new InvalidArgumentException(
                'OfflineSettingsDto: `data_retention_period` is required and must be an integer.'
            );
        }
    }
}
