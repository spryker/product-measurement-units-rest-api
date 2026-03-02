<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductMeasurementUnitsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\ProductMeasurementUnitTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface ProductMeasurementUnitRestResponseBuilderInterface
{
    public function createProductMeasurementUnitRestResponse(ProductMeasurementUnitTransfer $productMeasurementUnitTransfer): RestResponseInterface;

    public function createProductMeasurementUnitRestResource(ProductMeasurementUnitTransfer $productMeasurementUnitTransfer): RestResourceInterface;

    public function createCodeMissingErrorResponse(): RestResponseInterface;

    public function createProductMeasurementUnitNotFoundErrorResponse(): RestResponseInterface;
}
