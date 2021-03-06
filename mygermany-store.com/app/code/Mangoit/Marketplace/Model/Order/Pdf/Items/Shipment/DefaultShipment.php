<?php
/**
 * Package Mangoit_Marketplace.
 */
namespace Mangoit\Marketplace\Model\Order\Pdf\Items\Shipment;

/**
 * Sales Order Shipment Pdf default items renderer
 */
class DefaultShipment extends \Magento\Sales\Model\Order\Pdf\Items\Shipment\DefaultShipment
{

    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];

        // draw Product name
        $lines[0] = [['text' => $this->string->split($item->getName(), 60, true, true), 'feed' => 100]];

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 35];

        $lines[0][] = ['text' => (($item->getFskProduct()) ? 'YES' : 'NO'), 'feed' => 245];
        // draw SKU
        $lines[0][] = [
            'text' => $this->string->split($this->getSku($item), 25),
            'feed' => 565,
            'align' => 'right',
        ];

        // Custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => 110,
                ];

                // draw options value
                if ($option['value']) {
                    $printValue = isset(
                        $option['print_value']
                    ) ? $option['print_value'] : $this->filterManager->stripTags(
                        $option['value']
                    );
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = ['text' => $this->string->split($value, 50, true, true), 'feed' => 115];
                    }
                }
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
}
