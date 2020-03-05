<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUserGui\Communication\Table;

use Orm\Zed\MerchantUser\Persistence\SpyMerchantUserQuery;
use Orm\Zed\User\Persistence\Map\SpyUserTableMap;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class MerchantUserTable extends AbstractTable
{
    protected const MERCHANT_USER_STATUS = 'status';
    protected const MERCHANT_USER_FIRST_NAME = 'first_name';
    protected const MERCHANT_USER_LAST_NAME = 'last_name';
    protected const MERCHANT_USER_NAME = 'username';
    protected const MERCHANT_USER_ID = 'id';
    protected const ACTIONS = 'actions';

    /**
     * @see \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_STATUS_BLOCKED
     */
    protected const USER_STATUS_BLOCKED = 'blocked';
    /**
     * @see \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_STATUS_ACTIVE
     */
    protected const USER_STATUS_ACTIVE = 'active';
    /**
     * @uses \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_ID_USER
     */
    protected const COL_ID_USER = 'spy_user.id_user';
    /**
     * @uses \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_USERNAME
     */
    protected const COL_USERNAME = 'spy_user.username';
    /**
     * @uses \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_FIRST_NAME
     */
    protected const COL_FIRST_NAME = 'spy_user.first_name';
    /**
     * @uses \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_LAST_NAME
     */
    protected const COL_LAST_NAME = 'spy_user.last_name';
    /**
     * @uses \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_STATUS
     */
    protected const COL_STATUS = 'spy_user.status';
    /**
     * @var string
     */
    protected const COL_ACTIONS = 'actions';
    /**
     * @var array
     */
    protected const STATUS_CLASS_LABEL_MAPPING = [
        self::USER_STATUS_ACTIVE => [
            'title' => 'Active',
            'class' => 'label-info',
            'status_change_action_title' => 'Activate',
            'status_change_action_class' => 'btn-create',
        ],
        self::USER_STATUS_BLOCKED => [
            'title' => 'Blocked',
            'class' => 'label-danger',
            'status_change_action_title' => 'Deny Access',
            'status_change_action_class' => 'btn-remove',
        ],
    ];

    /**
     * @var \Orm\Zed\MerchantUser\Persistence\SpyMerchantUserQuery
     */
    protected $merchantUserQuery;

    /**
     * @var string
     */
    protected $baseUrl = '/merchant-user-gui/index';

    /**
     * @var int
     */
    private $merchantId;

    /**
     * @param \Orm\Zed\MerchantUser\Persistence\SpyMerchantUserQuery $merchantUserQuery
     * @param int $merchantId
     */
    public function __construct(SpyMerchantUserQuery $merchantUserQuery, int $merchantId)
    {
        $this->merchantUserQuery = $merchantUserQuery;
        $this->merchantId = $merchantId;
    }

    /**
     * @inheritDoc
     */
    protected function configure(TableConfiguration $config)
    {
        $config->setHeader([
            static::MERCHANT_USER_ID => 'Merchant User Id',
            static::MERCHANT_USER_NAME => 'E-mail',
            static::MERCHANT_USER_FIRST_NAME => 'First Name',
            static::MERCHANT_USER_LAST_NAME => 'Last Name',
            static::MERCHANT_USER_STATUS => 'Status',
            static::ACTIONS => 'Actions',
        ]);

        $config->setSortable([
            static::MERCHANT_USER_NAME,
            static::MERCHANT_USER_FIRST_NAME,
            static::MERCHANT_USER_LAST_NAME,
            static::MERCHANT_USER_STATUS,
        ]);

        $config->setRawColumns([
            static::ACTIONS,
            static::MERCHANT_USER_STATUS,
        ]);

        $config->setDefaultSortField(static::MERCHANT_USER_ID, TableConfiguration::SORT_DESC);

        $config->setSearchable([
            static::MERCHANT_USER_NAME,
            static::MERCHANT_USER_STATUS,
        ]);

        $config->setUrl(sprintf('table?%s=%d', 'merchant-id', $this->merchantId));

        return $config;
    }

    /**
     * @inheritDoc
     */
    protected function prepareData(TableConfiguration $config)
    {
        $query = $this->merchantUserQuery
            ->innerJoinSpyUser()
            ->filterByFkMerchant($this->merchantId)
            ->withColumn(static::COL_STATUS, static::MERCHANT_USER_STATUS)
            ->withColumn(static::COL_FIRST_NAME, static::MERCHANT_USER_FIRST_NAME)
            ->withColumn(static::COL_LAST_NAME, static::MERCHANT_USER_LAST_NAME)
            ->withColumn(static::COL_USERNAME, static::MERCHANT_USER_NAME);

        $queryResults = $this->runQuery($query, $config);
        $results = [];

        foreach ($queryResults as $item) {
            $item['status'] = $this->convertStatusEnumKeyToValue($item['status']);
            $results[] = [
                static::MERCHANT_USER_ID => $item['spy_merchant_user.id_merchant_user'],
                static::MERCHANT_USER_NAME => $item[static::MERCHANT_USER_NAME],
                static::MERCHANT_USER_FIRST_NAME => $item[static::MERCHANT_USER_FIRST_NAME],
                static::MERCHANT_USER_LAST_NAME => $item[static::MERCHANT_USER_LAST_NAME],
                static::MERCHANT_USER_STATUS => $this->createStatusLabel($item),
                static::ACTIONS => implode(
                    ' ',
                    $this->createActionColumn($item)
                ),

            ];
        }

        return $results;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function createActionColumn(array $item): array
    {
        $buttons = [];

        $buttons[] = $this->generateEditButton(
            Url::generate(
                '/merchant-user-gui/edit-merchant-user',
                [
                    'merchant-user-id' => $item['spy_merchant_user.id_merchant_user'],
                    'merchant-id' => $this->merchantId,
                ]
            ),
            'Edit'
        );

        $buttons[] = $this->buildAvailableStatusButton($item);

        return $buttons;
    }

    /**
     * @param array $merchantUserData
     *
     * @return string
     */
    protected function createStatusLabel(array $merchantUserData): string
    {
        $currentStatus = $merchantUserData[static::MERCHANT_USER_STATUS];

        if (!isset(static::STATUS_CLASS_LABEL_MAPPING[$currentStatus])) {
            return '';
        }

        return $this->generateLabel(
            static::STATUS_CLASS_LABEL_MAPPING[$currentStatus]['title'],
            static::STATUS_CLASS_LABEL_MAPPING[$currentStatus]['class']
        );
    }

    /**
     * @param array $item
     *
     * @return string
     */
    protected function buildAvailableStatusButton(array $item): string
    {
        $availableStatus = $item['status'] === static::USER_STATUS_ACTIVE ?
            static::USER_STATUS_BLOCKED  : static::USER_STATUS_ACTIVE;

        $statusButtonCode = $this->generateButton(
            Url::generate(
                '/merchant-user-gui/merchant-user-status',
                [
                    'merchant-user-id' => $item['spy_merchant_user.id_merchant_user'],
                    'merchant-id' => $this->merchantId,
                    'status' => $availableStatus,
                ]
            ),
            static::STATUS_CLASS_LABEL_MAPPING[$availableStatus]['status_change_action_title'],
            [
                'icon' => 'fa fa-key', 'class' =>
                static::STATUS_CLASS_LABEL_MAPPING[$availableStatus]['status_change_action_class'],
            ]
        );

        return $statusButtonCode;
    }

    /**
     * @param int $status
     *
     * @return string
     */
    protected function convertStatusEnumKeyToValue(int $status): string
    {
        $options = SpyUserTableMap::getValueSet(static::COL_STATUS);

        return $options[$status];
    }
}
