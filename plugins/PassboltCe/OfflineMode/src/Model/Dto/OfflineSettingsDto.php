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

use InvalidArgumentException;

class OfflineSettingsDto
{
    /**
     * Default max session duration (seconds) — used when no settings row exists.
     */
    public const DEFAULT_MAX_SESSION_DURATION = 86400;

    /**
     * Default offline-cache retention period (seconds) — used when no settings row exists.
     */
    public const DEFAULT_DATA_RETENTION_PERIOD = 120000;

    /**
     * @var int Maximum offline session duration in seconds.
     */
    public int $maxSessionDuration;

    /**
     * @var int Offline-cache retention window in seconds.
     */
    public int $dataRetentionPeriod;

    /**
     * @param int $maxSessionDuration Maximum session duration (in seconds).
     * @param int $dataRetentionPeriod Data retention period (in seconds).
     */
    public function __construct(int $maxSessionDuration, int $dataRetentionPeriod)
    {
        $this->maxSessionDuration = $maxSessionDuration;
        $this->dataRetentionPeriod = $dataRetentionPeriod;
    }

    /**
     * @param array $data Data to create DTO from.
     * @return self
     * @throws \InvalidArgumentException When data assertions fails.
     */
    public static function createFromArray(array $data): self
    {
        self::assertMaxSessionDuration($data);
        self::assertDataRetentionPeriod($data);

        return new self($data['max_session_duration'], $data['data_retention_period']);
    }

    /**
     * Array representation of the DTO.
     *
     * @return array{max_session_duration: int, data_retention_period: int}
     */
    public function toArray(): array
    {
        return [
            'max_session_duration' => $this->maxSessionDuration,
            'data_retention_period' => $this->dataRetentionPeriod,
        ];
    }

    /**
     * JSON representation of the DTO.
     *
     * @return string
     * @throws \JsonException When the payload cannot be encoded.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
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
