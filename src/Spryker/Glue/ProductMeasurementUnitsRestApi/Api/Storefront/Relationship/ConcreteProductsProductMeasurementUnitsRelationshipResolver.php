<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductMeasurementUnitsRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\ConcreteProductsStorefrontResource;
use Generated\Api\Storefront\ProductMeasurementUnitsStorefrontResource;
use Generated\Shared\Transfer\ProductConcreteProductMeasurementSalesUnitTransfer;
use Generated\Shared\Transfer\ProductMeasurementUnitTransfer;
use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\Client\ProductMeasurementUnitStorage\ProductMeasurementUnitStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;

class ConcreteProductsProductMeasurementUnitsRelationshipResolver extends AbstractRelationshipResolver
{
    protected const string MAPPING_TYPE_SKU = 'sku';

    public function __construct(
        protected ProductStorageClientInterface $productStorageClient,
        protected ProductMeasurementUnitStorageClientInterface $productMeasurementUnitStorageClient,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\ProductMeasurementUnitsStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        if (!$this->hasLocale()) {
            return [];
        }

        $localeName = $this->getLocale()->getLocaleNameOrFail();
        $skus = [];

        foreach ($this->getParentResources() as $parent) {
            if (!$parent instanceof ConcreteProductsStorefrontResource) {
                continue;
            }

            if ($parent->sku !== null && $parent->sku !== '') {
                $skus[] = $parent->sku;
            }
        }

        if ($skus === []) {
            return [];
        }

        $productConcreteIds = $this->productStorageClient->getProductConcreteIdsByMapping(
            static::MAPPING_TYPE_SKU,
            $skus,
            $localeName,
        );

        if ($productConcreteIds === []) {
            return [];
        }

        $productMeasurementSalesUnitTransfers = $this->productMeasurementUnitStorageClient
            ->getProductMeasurementSalesUnitsByProductConcreteIds(array_values($productConcreteIds));

        return $this->extractUniqueMeasurementUnitResources($productMeasurementSalesUnitTransfers);
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductConcreteProductMeasurementSalesUnitTransfer> $productConcreteProductMeasurementSalesUnitTransfers
     *
     * @return array<\Generated\Api\Storefront\ProductMeasurementUnitsStorefrontResource>
     */
    protected function extractUniqueMeasurementUnitResources(array $productConcreteProductMeasurementSalesUnitTransfers): array
    {
        $seenCodes = [];
        $resources = [];

        foreach ($productConcreteProductMeasurementSalesUnitTransfers as $salesUnitContainerTransfer) {
            $resources = array_merge(
                $resources,
                $this->extractMeasurementUnitResourcesFromContainer($salesUnitContainerTransfer, $seenCodes),
            );
        }

        return $resources;
    }

    /**
     * @param array<string, bool> $seenCodes
     *
     * @return array<\Generated\Api\Storefront\ProductMeasurementUnitsStorefrontResource>
     */
    protected function extractMeasurementUnitResourcesFromContainer(
        ProductConcreteProductMeasurementSalesUnitTransfer $salesUnitContainerTransfer,
        array &$seenCodes
    ): array {
        $resources = [];

        foreach ($salesUnitContainerTransfer->getProductMeasurementSalesUnits() as $salesUnitTransfer) {
            $unitTransfer = $salesUnitTransfer->getProductMeasurementUnit();
            $code = $unitTransfer?->getCode();

            if ($code === null || isset($seenCodes[$code])) {
                continue;
            }

            $seenCodes[$code] = true;
            $resources[] = $this->mapUnitToResource($unitTransfer);
        }

        return $resources;
    }

    protected function mapUnitToResource(ProductMeasurementUnitTransfer $productMeasurementUnitTransfer): ProductMeasurementUnitsStorefrontResource
    {
        $resource = new ProductMeasurementUnitsStorefrontResource();
        $resource->code = $productMeasurementUnitTransfer->getCode();
        $resource->name = $productMeasurementUnitTransfer->getName();
        $resource->defaultPrecision = $productMeasurementUnitTransfer->getDefaultPrecision();

        return $resource;
    }
}
