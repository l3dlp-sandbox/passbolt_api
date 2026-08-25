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
namespace Passbolt\OfflineMode\Test\TestCase\Form;

use Cake\TestSuite\TestCase;
use Passbolt\OfflineMode\Form\OfflineSettingsForm;
use Passbolt\OfflineMode\Model\Dto\OfflineSettingsDto;

/**
 * @covers \Passbolt\OfflineMode\Form\OfflineSettingsForm
 */
class OfflineSettingsFormTest extends TestCase
{
    protected OfflineSettingsForm $form;

    public function setUp(): void
    {
        parent::setUp();
        $this->form = new OfflineSettingsForm();
    }

    public function tearDown(): void
    {
        unset($this->form);
        parent::tearDown();
    }

    public static function getDefaultData(): array
    {
        return [
            'max_session_duration' => OfflineSettingsDto::DEFAULT_MAX_SESSION_DURATION,
            'data_retention_period' => OfflineSettingsDto::DEFAULT_DATA_RETENTION_PERIOD,
            'max_items' => OfflineSettingsDto::DEFAULT_MAX_ITEMS,
        ];
    }

    public function testOfflineSettingsForm_Success(): void
    {
        $this->assertTrue($this->form->execute(self::getDefaultData()));
        $this->assertSame([], $this->form->getErrors());
    }

    public function testOfflineSettingsForm_Error_Empty(): void
    {
        $this->assertFalse($this->form->execute([]));
        $errors = $this->form->getErrors();
        $this->assertArrayHasKey('_empty', $errors['max_session_duration']);
        $this->assertArrayHasKey('_empty', $errors['data_retention_period']);
        $this->assertArrayHasKey('_empty', $errors['max_items']);
    }

    public static function invalidValueProvider(): array
    {
        return [
            'string' => ['string'],
            'array' => [[]],
            'zero' => [0],
            'negative' => [-1],
        ];
    }

    /**
     * @dataProvider invalidValueProvider
     * @param mixed $value Invalid value.
     * @return void
     */
    public function testOfflineSettingsForm_Error_InvalidMaxSessionDuration(mixed $value): void
    {
        $data = array_merge(self::getDefaultData(), ['max_session_duration' => $value]);

        $this->assertFalse($this->form->execute($data));
        $this->assertNotEmpty($this->form->getError('max_session_duration'));
    }

    /**
     * @dataProvider invalidValueProvider
     * @param mixed $value Invalid value.
     * @return void
     */
    public function testOfflineSettingsForm_Error_InvalidDataRetentionPeriod(mixed $value): void
    {
        $data = array_merge(self::getDefaultData(), ['data_retention_period' => $value]);

        $this->assertFalse($this->form->execute($data));
        $this->assertNotEmpty($this->form->getError('data_retention_period'));
    }

    /**
     * @dataProvider invalidValueProvider
     * @param mixed $value Invalid value.
     * @return void
     */
    public function testOfflineSettingsForm_Error_InvalidMaxItems(mixed $value): void
    {
        $data = array_merge(self::getDefaultData(), ['max_items' => $value]);

        $this->assertFalse($this->form->execute($data));
        $this->assertNotEmpty($this->form->getError('max_items'));
    }

    public static function maxItemsBoundaryProvider(): array
    {
        return [
            'lower bound' => [OfflineSettingsDto::MIN_MAX_ITEMS, true],
            'upper bound' => [OfflineSettingsDto::MAX_MAX_ITEMS, true],
            'above upper bound' => [OfflineSettingsDto::MAX_MAX_ITEMS + 1, false],
        ];
    }

    /**
     * @dataProvider maxItemsBoundaryProvider
     * @param int $value Value under test.
     * @param bool $expected Whether the form should accept the value.
     * @return void
     */
    public function testOfflineSettingsForm_MaxItemsBoundaries(int $value, bool $expected): void
    {
        $data = array_merge(self::getDefaultData(), ['max_items' => $value]);

        $this->assertSame($expected, $this->form->execute($data));
    }

    public static function maxSessionDurationBoundaryProvider(): array
    {
        return [
            'below lower bound' => [OfflineSettingsDto::MIN_MAX_SESSION_DURATION - 1, false],
            'lower bound' => [OfflineSettingsDto::MIN_MAX_SESSION_DURATION, true],
            'upper bound' => [OfflineSettingsDto::MAX_MAX_SESSION_DURATION, true],
            'above upper bound' => [OfflineSettingsDto::MAX_MAX_SESSION_DURATION + 1, false],
        ];
    }

    /**
     * @dataProvider maxSessionDurationBoundaryProvider
     * @param int $value Value under test.
     * @param bool $expected Whether the form should accept the value.
     * @return void
     */
    public function testOfflineSettingsForm_MaxSessionDurationBoundaries(int $value, bool $expected): void
    {
        $data = array_merge(self::getDefaultData(), ['max_session_duration' => $value]);

        $this->assertSame($expected, $this->form->execute($data));
    }

    public static function dataRetentionPeriodBoundaryProvider(): array
    {
        return [
            'below lower bound' => [OfflineSettingsDto::MIN_DATA_RETENTION_PERIOD - 1, false],
            'lower bound' => [OfflineSettingsDto::MIN_DATA_RETENTION_PERIOD, true],
            'upper bound' => [OfflineSettingsDto::MAX_DATA_RETENTION_PERIOD, true],
            'above upper bound' => [OfflineSettingsDto::MAX_DATA_RETENTION_PERIOD + 1, false],
        ];
    }

    /**
     * @dataProvider dataRetentionPeriodBoundaryProvider
     * @param int $value Value under test.
     * @param bool $expected Whether the form should accept the value.
     * @return void
     */
    public function testOfflineSettingsForm_DataRetentionPeriodBoundaries(int $value, bool $expected): void
    {
        $data = array_merge(self::getDefaultData(), ['data_retention_period' => $value]);

        $this->assertSame($expected, $this->form->execute($data));
    }
}
