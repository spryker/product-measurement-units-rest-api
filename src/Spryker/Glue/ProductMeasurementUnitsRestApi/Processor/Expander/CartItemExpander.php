<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductMeasurementUnitsRestApi\Processor\Expander;

use Generated\Shared\Transfer\CartItemRequestTransfer;
use Generated\Shared\Transfer\RestCartItemsAttributesTransfer;

class CartItemExpander implements CartItemExpanderInterface
{
    public function expand(
        CartItemRequestTransfer $cartItemRequestTransfer,
        RestCartItemsAttributesTransfer $restCartItemsAttributesTransfer
    ): CartItemRequestTransfer {
        $restCartItemsSalesUnitAttributesTransfer = $restCartItemsAttributesTransfer->getSalesUnit();
        if (!$restCartItemsSalesUnitAttributesTransfer) {
            return $cartItemRequestTransfer;
        }

        return $cartItemRequestTransfer
            ->setAmount($restCartItemsSalesUnitAttributesTransfer->getAmount())
            ->setIdProductMeasurementSalesUnit($restCartItemsSalesUnitAttributesTransfer->getId());
    }
}
