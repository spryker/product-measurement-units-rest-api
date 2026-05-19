<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductMeasurementUnitsRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\CartItemsStorefrontResource;
use Generated\Api\Storefront\GuestCartItemsStorefrontResource;
use Generated\Api\Storefront\SalesUnitsStorefrontResource;
use Generated\Shared\Transfer\ProductMeasurementSalesUnitTransfer;
use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\Client\ProductMeasurementUnitStorage\ProductMeasurementUnitStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;

/**
 * Emits exactly one `SalesUnits` resource per cart-item — the sales-unit the customer
 * selected when adding the item to the cart (`item.idProductMeasurementSalesUnit`).
 *
 * Mirrors the legacy `?include=sales-units` behavior on cart-items where only the chosen
 * sales-unit was expanded into `included`, not every sales-unit configured on the
 * underlying concrete product.
 *
 * Without this resolver the declarative relationship `sku → concreteProductSku` on
 * {@see \Generated\Api\Storefront\SalesUnitsStorefrontResource} would fan out into the
 * full collection of sales-units of the product (e.g. cable-vga has both METR and CMET
 * sales-units), producing duplicates in the JSON:API `included` array.
 */
class CartItemSalesUnitsRelationshipResolver extends AbstractRelationshipResolver
{
    protected const string MAPPING_TYPE_SKU = 'sku';

    public function __construct(
        protected ProductStorageClientInterface $productStorageClient,
        protected ProductMeasurementUnitStorageClientInterface $productMeasurementUnitStorageClient,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\SalesUnitsStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        if (!$this->hasLocale()) {
            return [];
        }

        [$skus, $selectedSalesUnitIds] = $this->collectSkusAndSelectedSalesUnitIds();

        if ($skus === [] || $selectedSalesUnitIds === []) {
            return [];
        }

        $productConcreteIdsBySku = $this->productStorageClient->getProductConcreteIdsByMapping(
            static::MAPPING_TYPE_SKU,
            $skus,
            $this->getLocale()->getLocaleNameOrFail(),
        );

        if ($productConcreteIdsBySku === []) {
            return [];
        }

        $salesUnitContainerTransfers = $this->productMeasurementUnitStorageClient
            ->getProductMeasurementSalesUnitsByProductConcreteIds(array_values(array_map('intval', $productConcreteIdsBySku)));

        $resources = [];

        foreach ($salesUnitContainerTransfers as $salesUnitContainerTransfer) {
            foreach ($salesUnitContainerTransfer->getProductMeasurementSalesUnits() as $salesUnitTransfer) {
                $salesUnitId = $salesUnitTransfer->getIdProductMeasurementSalesUnit();

                if ($salesUnitId === null || !isset($selectedSalesUnitIds[$salesUnitId])) {
                    continue;
                }

                $resources[] = $this->mapSalesUnitToResource($salesUnitTransfer);
                unset($selectedSalesUnitIds[$salesUnitId]);
            }
        }

        return $resources;
    }

    /**
     * @return array{0: array<string>, 1: array<int, true>}
     */
    protected function collectSkusAndSelectedSalesUnitIds(): array
    {
        $skus = [];
        $selectedSalesUnitIds = [];

        foreach ($this->getParentResources() as $parent) {
            if (!$parent instanceof CartItemsStorefrontResource && !$parent instanceof GuestCartItemsStorefrontResource) {
                continue;
            }

            $sku = $parent->sku;
            $salesUnitId = $this->resolveSelectedSalesUnitId($parent);

            if ($sku === null || $sku === '' || $salesUnitId === null) {
                continue;
            }

            $skus[$sku] = true;
            $selectedSalesUnitIds[$salesUnitId] = true;
        }

        return [array_keys($skus), $selectedSalesUnitIds];
    }

    /**
     * Returns the {@see ProductMeasurementSalesUnitTransfer::getIdProductMeasurementSalesUnit()}
     * the customer selected on the cart-item, or null if the item is not a measurement-unit item.
     *
     * Reads from either the structured `salesUnit.id` payload (incoming write) or the
     * flat `idProductMeasurementSalesUnit` projection (persisted read view).
     */
    protected function resolveSelectedSalesUnitId(object $cartItem): ?int
    {
        $flatId = $cartItem->idProductMeasurementSalesUnit ?? null;

        if (is_numeric($flatId)) {
            return (int)$flatId;
        }

        $salesUnit = $cartItem->salesUnit ?? null;

        if (is_array($salesUnit) && isset($salesUnit['id']) && is_numeric($salesUnit['id'])) {
            return (int)$salesUnit['id'];
        }

        if (is_object($salesUnit) && isset($salesUnit->id) && is_numeric($salesUnit->id)) {
            return (int)$salesUnit->id;
        }

        return null;
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
