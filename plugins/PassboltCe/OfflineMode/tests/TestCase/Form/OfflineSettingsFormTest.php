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
namespace Passbolt\OfflineMode\Test\TestCase\Form;

use Cake\TestSuite\TestCase;
use Passbolt\OfflineMode\Form\OfflineSettingsForm;

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
            'max_session_duration' => 3600,
            'data_retention_period' => 7200,
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
}
