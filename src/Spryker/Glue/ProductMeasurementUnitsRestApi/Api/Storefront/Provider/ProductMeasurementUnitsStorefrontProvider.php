<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductMeasurementUnitsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\ProductMeasurementUnitsStorefrontResource;
use Generated\Shared\Transfer\ProductMeasurementUnitTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\ProductMeasurementUnitStorage\ProductMeasurementUnitStorageClientInterface;

class ProductMeasurementUnitsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string URI_VAR_CODE = 'code';

    protected const string MAPPING_TYPE_CODE = 'code';

    public function __construct(
        protected ProductMeasurementUnitStorageClientInterface $productMeasurementUnitStorageClient,
    ) {
    }

    protected function provideItem(): ?object
    {
        if (!$this->hasUriVariable(static::URI_VAR_CODE)) {
            return null;
        }

        $code = (string)$this->getUriVariable(static::URI_VAR_CODE);

        if ($code === '') {
            return null;
        }

        $productMeasurementUnits = $this->productMeasurementUnitStorageClient->getProductMeasurementUnitsByMapping(
            static::MAPPING_TYPE_CODE,
            [$code],
        );

        if ($productMeasurementUnits === []) {
            return null;
        }

        return $this->mapUnitToResource(reset($productMeasurementUnits));
    }

    protected function mapUnitToResource(ProductMeasurementUnitTransfer $unit): ProductMeasurementUnitsStorefrontResource
    {
        $resource = new ProductMeasurementUnitsStorefrontResource();
        $resource->code = $unit->getCode();
        $resource->name = $unit->getName();
        $resource->defaultPrecision = $unit->getDefaultPrecision();

        return $resource;
    }
}
