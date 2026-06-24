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
namespace Passbolt\OfflineMode\Test\TestCase\Model\Dto;

use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto;

/**
 * @covers \Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto
 */
class OfflineSettingsDtoTest extends TestCase
{
    public function testOfflineSettingsDto_CreateFromArray_Success(): void
    {
        $dto = OfflineSettingsDto::createFromArray([
            'max_session_duration' => 86400,
            'data_retention_period' => 120000,
        ]);

        $result = $dto->toArray();
        $this->assertSame(86400, $result['max_session_duration']);
        $this->assertSame(120000, $result['data_retention_period']);
    }

    public static function invalidMaxSessionDurationValuesProvider(): array
    {
        return [
            [null],
            ['string'],
            [[]],
        ];
    }

    /**
     * @dataProvider invalidMaxSessionDurationValuesProvider
     * @param mixed $invalidValue Invalid value.
     * @return void
     */
    public function testOfflineSettingsDto_CreateFromArray_Error_InvalidMaxSessionDuration(mixed $invalidValue): void
    {
        $data = ['data_retention_period' => 7200, 'max_session_duration' => $invalidValue];
        if (is_null($invalidValue)) {
            unset($data['max_session_duration']);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/max_session_duration/');

        OfflineSettingsDto::createFromArray($data);
    }

    public static function invalidDataRetentionPeriodValuesProvider(): array
    {
        return [
            [null],
            ['string'],
            [[]],
        ];
    }

    /**
     * @dataProvider invalidDataRetentionPeriodValuesProvider
     * @param mixed $invalidValue Invalid value.
     * @return void
     */
    public function testOfflineSettingsDto_CreateFromArray_Error_InvalidDataRetentionPeriod(mixed $invalidValue): void
    {
        $data = ['data_retention_period' => $invalidValue, 'max_session_duration' => 120000];
        if (is_null($invalidValue)) {
            unset($data['data_retention_period']);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/data_retention_period/');

        OfflineSettingsDto::createFromArray($data);
    }
}
