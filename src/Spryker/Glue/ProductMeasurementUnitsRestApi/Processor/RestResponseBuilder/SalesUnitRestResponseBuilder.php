<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductMeasurementUnitsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\ProductMeasurementSalesUnitTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Generated\Shared\Transfer\RestSalesUnitsAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\ProductMeasurementUnitsRestApi\Processor\Mapper\SalesUnitMapperInterface;
use Spryker\Glue\ProductMeasurementUnitsRestApi\ProductMeasurementUnitsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class SalesUnitRestResponseBuilder implements SalesUnitRestResponseBuilderInterface
{
    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @var \Spryker\Glue\ProductMeasurementUnitsRestApi\Processor\Mapper\SalesUnitMapperInterface
     */
    protected $salesUnitMapper;

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     * @param \Spryker\Glue\ProductMeasurementUnitsRestApi\Processor\Mapper\SalesUnitMapperInterface $salesUnitMapper
     */
    public function __construct(
        RestResourceBuilderInterface $restResourceBuilder,
        SalesUnitMapperInterface $salesUnitMapper
    ) {
        $this->restResourceBuilder = $restResourceBuilder;
        $this->salesUnitMapper = $salesUnitMapper;
    }

    /**
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createRestResponse(): RestResponseInterface
    {
        return $this->restResourceBuilder->createRestResponse();
    }
    
    /**
     * @param \Generated\Shared\Transfer\ProductMeasurementSalesUnitTransfer $productMeasurementSalesUnitTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createSalesUnitRestResponse(ProductMeasurementSalesUnitTransfer $productMeasurementSalesUnitTransfer): RestResponseInterface
    {
        return $this->createRestResponse()->addResource($this->createSalesUnitRestResource($productMeasurementSalesUnitTransfer));
    }

    /**
     * @param \Generated\Shared\Transfer\ProductMeasurementSalesUnitTransfer $productMeasurementSalesUnitTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface
     */
    public function createSalesUnitRestResource(ProductMeasurementSalesUnitTransfer $productMeasurementSalesUnitTransfer): RestResourceInterface
    {
        $restProductMeasurementUnitsAttributesTransfer = $this->salesUnitMapper
            ->mapProductMeasurementSalesUnitTransferToRestSalesUnitsAttributesTransfer(
                $productMeasurementSalesUnitTransfer,
                new RestSalesUnitsAttributesTransfer()
            );

        $resourceId = (string)$productMeasurementSalesUnitTransfer->getIdProductMeasurementSalesUnit();

        return $this->restResourceBuilder->createRestResource(
            ProductMeasurementUnitsRestApiConfig::RESOURCE_SALES_UNITS,
            $resourceId,
            $restProductMeasurementUnitsAttributesTransfer
        );
    }

    /**
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createProductConcreteSkuMissingErrorResponse(): RestResponseInterface
    {
        $restErrorTransfer = (new RestErrorMessageTransfer())
            ->setCode(ProductMeasurementUnitsRestApiConfig::RESPONSE_CODE_CONCRETE_PRODUCT_SKU_IS_NOT_SPECIFIED)
            ->setStatus(Response::HTTP_BAD_REQUEST)
            ->setDetail(ProductMeasurementUnitsRestApiConfig::RESPONSE_DETAIL_CONCRETE_PRODUCT_SKU_IS_NOT_SPECIFIED);

        return $this->restResourceBuilder->createRestResponse()->addError($restErrorTransfer);
    }

    /**
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createProductConcreteNotFoundErrorResponse(): RestResponseInterface
    {
        $restErrorTransfer = (new RestErrorMessageTransfer())
            ->setCode(ProductMeasurementUnitsRestApiConfig::RESPONSE_CODE_CANT_FIND_CONCRETE_PRODUCT)
            ->setStatus(Response::HTTP_NOT_FOUND)
            ->setDetail(ProductMeasurementUnitsRestApiConfig::RESPONSE_DETAIL_CANT_FIND_CONCRETE_PRODUCT);

        return $this->restResourceBuilder->createRestResponse()->addError($restErrorTransfer);
    }
}
