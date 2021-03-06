<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\State;
use Magento\ImportExport\Model\Import;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractImportCommand
 * @package Plenty\Item\Console\Command
 */
abstract class AbstractImportCommand extends Command
{
    /**
     * @var string
     */
    protected $behavior;

    /**
     * @var string
     */
    protected $entityCode;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Object manager factory
     *
     * @var ObjectManagerFactory
     */
    private $objectManagerFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $this->objectManagerFactory = $objectManagerFactory;
        parent::__construct();
    }

    public function arrayToAttributeString($array)
    {
        $attributes_str = NULL;
        foreach ($array as $attribute => $value) {
            $attributes_str .= "$attribute=$value,";
        }

        return $attributes_str;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $omParams = $_SERVER;
        $omParams[StoreManager::PARAM_RUN_CODE] = 'admin';
        $omParams[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $this->objectManager = $this->objectManagerFactory->create($omParams);

        $area = FrontNameResolver::AREA_CODE;

        /** @var \Magento\Framework\App\State $appState */
        $appState = $this->objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($area);
        $configLoader = $this->objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
        $this->objectManager->configure($configLoader->load($area));

        $output->writeln('Import started');

        $time = microtime(true);

        $importerModel = $this->objectManager->create('\Plenty\Core\Plugin\ImportExport\Model\Import');

        $productsArray = $this->getEntities();

        $importerModel->setBehavior($this->getBehavior());
        $importerModel->setEntityCode($this->getEntityCode());
        $adapterFactory = $this->objectManager->create('Plenty\Core\Plugin\ImportExport\Model\Import\Adapters\NestedArrayAdapterFactory');
        $importerModel->setImportAdapterFactory($adapterFactory);

        try {
            $importerModel->execute($productsArray);
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        $output->write($importerModel->getLogTrace());
        $output->write($importerModel->getErrorMessages());

        $output->writeln('Import finished. Elapsed time: ' . round(microtime(true) - $time, 2) . 's' . "\n");
        $this->afterFinishImport();

    }

    /**
     * @return array
     */
    abstract protected function getEntities();

    /**
     * @return string
     */
    public function getBehavior()
    {
        return $this->behavior;
    }

    /**
     * @param string $behavior
     */
    public function setBehavior($behavior)
    {
        $this->behavior = $behavior;
    }

    /**
     * @return string
     */
    public function getEntityCode()
    {
        return $this->entityCode;
    }

    /**
     * @param string $entityCode
     */
    public function setEntityCode($entityCode)
    {
        $this->entityCode = $entityCode;
    }


    public function afterFinishImport(){

    }

}