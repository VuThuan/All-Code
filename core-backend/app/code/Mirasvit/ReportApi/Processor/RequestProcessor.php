<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report-api
 * @version   1.0.8
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Processor;

use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Config\Schema;
use Mirasvit\ReportApi\Handler\CollectionFactory;

class RequestProcessor
{
    private $collectionFactory;

    private $schema;

    private $responseBuilder;

    public function __construct(
        CollectionFactory $collectionFactory,
        Schema $schema,
        ResponseBuilder $responseBuilder
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->schema = $schema;
        $this->responseBuilder = $responseBuilder;
    }

    public function process(RequestInterface $request)
    {
        $collections = $this->assembleCollections($request);

        $query = [];
        foreach ($collections as $collection) {
            $query[] = $collection->__toString();
        }
        $request->setQuery(PHP_EOL . implode(PHP_EOL, $query));
        return $this->responseBuilder->create($request, $collections);
    }

    private function assembleCollections(RequestInterface $request)
    {
        /** @var \Mirasvit\ReportApi\Handler\Collection[] $collections */
        $collections = [];

        foreach ($request->getFilters() as $filter) {
            if ($filter->getGroup()) {
                $collections[$filter->getGroup()] = $this->collectionFactory->create();
            }
        }
        
        if (!$collections) {
            $collections['A'] = $this->collectionFactory->create();
        }

        foreach ($collections as $group => $collection) {
            $groupRequest = clone $request;

            # add filters only for current group or without group
            $filters = [];
            foreach ($groupRequest->getFilters() as $filter) {
                if (!$filter->getGroup() || $filter->getGroup() == $group) {
                    $filters[] = $filter;
                }
            }
            $groupRequest->setFilters($filters);

            $collection->setRequest($groupRequest);
        }

        return $collections;
    }
}