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
 * @package   mirasvit/module-report-builder
 * @version   1.0.11
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportBuilder\Plugin;

use Mirasvit\ReportBuilder\Api\Repository\ReportRepositoryInterface;
use Mirasvit\ReportBuilder\Service\BuilderService;

class ReportRepositoryPlugin
{
    private $reportRepository;

    private $builderService;

    public function __construct(
        ReportRepositoryInterface $reportRepository,
        BuilderService $builderService
    ) {
        $this->reportRepository = $reportRepository;
        $this->builderService = $builderService;
    }

    public function afterGetList($subject, $result)
    {
        foreach ($this->reportRepository->getCollection() as $report) {
            $result[] = $this->builderService->getReportInstance($report);
        }

        return $result;
    }
}