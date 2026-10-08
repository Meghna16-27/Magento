<?php
namespace Codilar\ProductRestriction\Controller\Adminhtml\Restriction;

use Codilar\ProductRestriction\Api\Data\ProductRestrictionInterfaceFactory;
use Codilar\ProductRestriction\Api\ProductRestrictionRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Psr\Log\LoggerInterface;

class Save extends Action
{
    protected $dataPersistor;
    protected $productRestrictionRepository;
    protected $productRestrictionFactory;
    protected $logger;

    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        ProductRestrictionRepositoryInterface $productRestrictionRepository,
        ProductRestrictionInterfaceFactory $productRestrictionFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->productRestrictionRepository = $productRestrictionRepository;
        $this->productRestrictionFactory = $productRestrictionFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        $this->logger->info('Product Restriction Save Data: ' . print_r($data, true));

        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            try {
                if ($id) {
                    $model = $this->productRestrictionRepository->getById($id);
                } else {
                    $model = $this->productRestrictionFactory->create();
                }

                if (isset($data['entity_id'])) {
                    unset($data['entity_id']);
                }

                $model->setData($data);
                $this->productRestrictionRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the product restriction.'));
                $this->dataPersistor->clear('codilar_product_restriction');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->logger->error('Product Restriction Save Error: ' . $e->getMessage());
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->dataPersistor->set('codilar_product_restriction', $data);
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        return $resultRedirect->setPath('*/*/index');
    }
}
