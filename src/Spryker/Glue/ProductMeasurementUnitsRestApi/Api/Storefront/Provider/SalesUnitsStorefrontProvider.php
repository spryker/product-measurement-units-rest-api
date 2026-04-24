<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductMeasurementUnitsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\SalesUnitsStorefrontResource;
use Generated\Shared\Transfer\ProductMeasurementSalesUnitTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\ProductMeasurementUnitStorage\ProductMeasurementUnitStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Glue\ProductMeasurementUnitsRestApi\ProductMeasurementUnitsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class SalesUnitsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string MAPPING_TYPE_SKU = 'sku';

    protected const string URI_VAR_CONCRETE_SKU = 'concreteProductSku';

    public function __construct(
        protected ProductStorageClientInterface $productStorageClient,
        protected ProductMeasurementUnitStorageClientInterface $productMeasurementUnitStorageClient,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\SalesUnitsStorefrontResource>
     */
    protected function provideCollection(): array
    {
        if (!$this->hasUriVariable(static::URI_VAR_CONCRETE_SKU)) {
            $this->throwMissingConcreteProductSku();
        }

        $sku = (string)$this->getUriVariable(static::URI_VAR_CONCRETE_SKU);

        if ($sku === '') {
            $this->throwMissingConcreteProductSku();
        }

        $localeName = $this->getLocale()->getLocaleNameOrFail();
        $productConcreteIds = $this->productStorageClient->getProductConcreteIdsByMapping(
            static::MAPPING_TYPE_SKU,
            [$sku],
            $localeName,
        );

        if ($productConcreteIds === []) {
            return [];
        }

        $productConcreteMeasurementSalesUnitTransfers = $this->productMeasurementUnitStorageClient
            ->getProductMeasurementSalesUnitsByProductConcreteIds(array_values($productConcreteIds));

        if ($productConcreteMeasurementSalesUnitTransfers === []) {
            return [];
        }

        $resources = [];
        foreach ($productConcreteMeasurementSalesUnitTransfers as $concreteMeasurementTransfer) {
            foreach ($concreteMeasurementTransfer->getProductMeasurementSalesUnits() as $salesUnit) {
                $resources[] = $this->mapSalesUnitToResource($salesUnit);
            }
        }

        return $resources;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return never
     */
    protected function throwMissingConcreteProductSku(): void
    {
        throw new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            ProductMeasurementUnitsRestApiConfig::RESPONSE_CODE_CONCRETE_PRODUCT_SKU_IS_NOT_SPECIFIED,
            ProductMeasurementUnitsRestApiConfig::RESPONSE_DETAIL_CONCRETE_PRODUCT_SKU_IS_NOT_SPECIFIED,
        );
    }

    protected function mapSalesUnitToResource(ProductMeasurementSalesUnitTransfer $salesUnit): SalesUnitsStorefrontResource
    {
        $resource = new SalesUnitsStorefrontResource();
        $resource->idProductMeasurementSalesUnit = (string)$salesUnit->getIdProductMeasurementSalesUnit();
        $conversion = $salesUnit->getConversion();
        $resource->conversion = $conversion !== null ? (int)$conversion : null;
        $resource->precision = $salesUnit->getPrecision();
        $resource->isDisplayed = (bool)$salesUnit->getIsDisplayed();
        $resource->isDefault = (bool)$salesUnit->getIsDefault();
        $resource->productMeasurementUnitCode = $salesUnit->getProductMeasurementUnit()?->getCode();

        return $resource;
    }
}
