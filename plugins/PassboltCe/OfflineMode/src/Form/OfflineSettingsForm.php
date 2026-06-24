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
namespace Passbolt\OfflineMode\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class OfflineSettingsForm extends Form
{
    /**
     * @param \Cake\Form\Schema $schema Schema.
     * @return \Cake\Form\Schema
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('max_session_duration', ['type' => 'integer'])
            ->addField('data_retention_period', ['type' => 'integer']);
    }

    /**
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('max_session_duration', true, __('The setting is required.'))
            ->integer('max_session_duration', __('The setting should be a valid integer.'))
            ->greaterThan('max_session_duration', 0, __('The setting should be a positive integer.'));

        $validator
            ->requirePresence('data_retention_period', true, __('The setting is required.'))
            ->integer('data_retention_period', __('The setting should be a valid integer.'))
            ->greaterThan('data_retention_period', 0, __('The setting should be a positive integer.'));

        return $validator;
    }

    /**
     * @param array $data Input payload.
     * @param array $options Cake Form options.
     * @return bool
     */
    public function execute(array $data, array $options = []): bool
    {
        return parent::execute($this->sanitizeData($data), $options);
    }

    /**
     * @param array $data Input payload.
     * @return array{max_session_duration: mixed, data_retention_period: mixed}
     */
    protected function sanitizeData(array $data): array
    {
        return [
            'max_session_duration' => $data['max_session_duration'] ?? null,
            'data_retention_period' => $data['data_retention_period'] ?? null,
        ];
    }
}
