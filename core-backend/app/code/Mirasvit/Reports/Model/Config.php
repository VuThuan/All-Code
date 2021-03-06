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
 * @package   mirasvit/module-reports
 * @version   1.3.20
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Reports\Model;

use Magento\Backend\Model\Locale\Resolver as LocaleResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\Bundle\DataBundle;

class Config
{
    const GEO_FILE_URL = 'http://files.mirasvit.com/report/postcode/list';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        LocaleResolver $localeResolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeResolver = $localeResolver;
        $this->filesystem = $filesystem;
    }

    /**
     * @return array
     */
    public function getLocaleData()
    {
        $localeData = (new DataBundle())->get($this->localeResolver->getLocale());

        $daysData = $localeData['calendar']['gregorian']['dayNames'];
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];

        $data = [
            'days'     => [
                'wide'        => array_values(iterator_to_array($daysData['format']['wide'])),
                'abbreviated' => array_values(iterator_to_array($daysData['format']['abbreviated'])),

            ],
            'months'   => [
                'wide'        => array_values(iterator_to_array($monthsData['format']['wide'])),
                'abbreviated' => array_values(iterator_to_array($monthsData['format']['abbreviated'])),
            ],
            'firstDay' => $this->scopeConfig->getValue('general/locale/firstday'),
        ];

        return $data;
    }
}
