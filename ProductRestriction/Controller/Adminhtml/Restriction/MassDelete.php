<?php
namespace Codilar\ProductRestriction\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction\CollectionFactory;

class MassDelete extends Action
{
    protected $filter;
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $deleteCount = 0;

            foreach ($collection as $item) {
                $item->delete();
                $deleteCount++;
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $deleteCount)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}
