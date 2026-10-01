<?php

declare(strict_types=1);

namespace Codilar\StoreLocation\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class StoreImage extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        protected StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $path = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'store_locations/image/';

            foreach ($dataSource['data']['items'] as & $item) {
                if (!empty($item[$fieldName])) {
                    $imageName = $item[$fieldName];
                    // Format the data into the array structure the UI thumbnail component requires
                    $item[$fieldName . '_src'] = $path . $imageName;
                    $item[$fieldName . '_orig_src'] = $path . $imageName;
                    $item[$fieldName . '_alt'] = $item['name'] ?? 'Store Image';
                }
            }
        }

        return $dataSource;
    }
}
