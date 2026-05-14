<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductMeasurementUnitsRestApi\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\ProductMeasurementUnitsRestApi\ProductMeasurementUnitsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class ProductMeasurementUnitsExceptionFactory
{
    public function createProductMeasurementUnitCodeMissingException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            ProductMeasurementUnitsRestApiConfig::RESPONSE_CODE_PRODUCT_MEASUREMENT_UNIT_CODE_IS_NOT_SPECIFIED,
            ProductMeasurementUnitsRestApiConfig::RESPONSE_DETAIL_MEASUREMENT_UNIT_CODE_IS_NOT_SPECIFIED,
        );
    }

    public function createProductMeasurementUnitNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            ProductMeasurementUnitsRestApiConfig::RESPONSE_CODE_PRODUCT_MEASUREMENT_UNIT_NOT_FOUND,
            ProductMeasurementUnitsRestApiConfig::RESPONSE_DETAIL_PRODUCT_MEASUREMENT_UNIT_NOT_FOUND,
        );
    }

    public function createConcreteProductSkuMissingException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            ProductMeasurementUnitsRestApiConfig::RESPONSE_CODE_CONCRETE_PRODUCT_SKU_IS_NOT_SPECIFIED,
            ProductMeasurementUnitsRestApiConfig::RESPONSE_DETAIL_CONCRETE_PRODUCT_SKU_IS_NOT_SPECIFIED,
        );
    }

    public function createConcreteProductNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            ProductMeasurementUnitsRestApiConfig::RESPONSE_CODE_CANT_FIND_CONCRETE_PRODUCT,
            ProductMeasurementUnitsRestApiConfig::RESPONSE_DETAIL_CANT_FIND_CONCRETE_PRODUCT,
        );
    }
}
