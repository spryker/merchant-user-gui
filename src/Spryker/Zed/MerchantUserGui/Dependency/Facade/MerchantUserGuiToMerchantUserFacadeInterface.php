<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUserGui\Dependency\Facade;

use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserResponseTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserCollectionTransfer;
use Generated\Shared\Transfer\UserCriteriaTransfer;

interface MerchantUserGuiToMerchantUserFacadeInterface
{
    public function createMerchantUser(MerchantUserTransfer $merchantUserTransfer): MerchantUserResponseTransfer;

    public function updateMerchantUser(MerchantUserTransfer $merchantUserTransfer): MerchantUserResponseTransfer;

    public function deleteMerchantUser(MerchantUserTransfer $merchantUserTransfer): MerchantUserResponseTransfer;

    public function findMerchantUser(MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer): ?MerchantUserTransfer;

    public function getUserCollection(UserCriteriaTransfer $userCriteriaTransfer): UserCollectionTransfer;
}
