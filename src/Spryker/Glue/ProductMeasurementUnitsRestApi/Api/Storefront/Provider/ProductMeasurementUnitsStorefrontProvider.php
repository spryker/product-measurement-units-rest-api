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
use Spryker\Glue\ProductMeasurementUnitsRestApi\Api\Storefront\Exception\ProductMeasurementUnitsExceptionFactory;

class ProductMeasurementUnitsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string URI_VAR_CODE = 'code';

    protected const string MAPPING_TYPE_CODE = 'code';

    public function __construct(
        protected ProductMeasurementUnitStorageClientInterface $productMeasurementUnitStorageClient,
        protected ProductMeasurementUnitsExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function provideItem(): ?object
    {
        if (!$this->hasUriVariable(static::URI_VAR_CODE)) {
            throw $this->exceptionFactory->createProductMeasurementUnitCodeMissingException();
        }

        $code = (string)$this->getUriVariable(static::URI_VAR_CODE);

        if ($code === '') {
            throw $this->exceptionFactory->createProductMeasurementUnitCodeMissingException();
        }

        $productMeasurementUnits = $this->productMeasurementUnitStorageClient->getProductMeasurementUnitsByMapping(
            static::MAPPING_TYPE_CODE,
            [$code],
        );

        if ($productMeasurementUnits === []) {
            throw $this->exceptionFactory->createProductMeasurementUnitNotFoundException();
        }

        return $this->mapUnitToResource(reset($productMeasurementUnits));
    }

    /**
     * Validation route: `GET /product-measurement-units` (no code) → legacy 400/3401.
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<\Generated\Api\Storefront\ProductMeasurementUnitsStorefrontResource>
     */
    protected function provideCollection(): array
    {
        throw $this->exceptionFactory->createProductMeasurementUnitCodeMissingException();
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
