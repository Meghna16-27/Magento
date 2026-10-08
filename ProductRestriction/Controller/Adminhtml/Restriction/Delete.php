<?php
namespace Codilar\ProductRestriction\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action;
use Codilar\ProductRestriction\Api\ProductRestrictionRepositoryInterface;

class Delete extends Action
{
    protected $productRestrictionRepository;

    public function __construct(
        Action\Context $context,
        ProductRestrictionRepositoryInterface $productRestrictionRepository
    ) {
        parent::__construct($context);
        $this->productRestrictionRepository = $productRestrictionRepository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->productRestrictionRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the restriction.'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can not find a restriction to delete.'));
        return $resultRedirect->setPath('*/*/index');
    }
}
