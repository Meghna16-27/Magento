<?php
namespace Codilar\ProductRestriction\Model\Restriction;

use Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $dataPersistor;
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $restrictionCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $restrictionCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $restriction) {
            $this->loadedData[$restriction->getId()] = $restriction->getData();
        }
        $data = $this->dataPersistor->get('codilar_product_restriction');
        if (!empty($data)) {
            $restriction = $this->collection->getNewEmptyItem();
            $restriction->setData($data);
            $this->loadedData[$restriction->getId()] = $restriction->getData();
            $this->dataPersistor->clear('codilar_product_restriction');
        }
        return $this->loadedData;
    }
}
