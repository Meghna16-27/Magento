<?php
namespace Codilar\ProductRestriction\Block\Adminhtml\Restriction\Edit;

use Magento\Backend\Block\Widget\Context;
use Codilar\ProductRestriction\Api\ProductRestrictionRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class Generic
{
    protected $context;
    protected $productRestrictionRepository;

    public function __construct(
        Context $context,
        ProductRestrictionRepositoryInterface $productRestrictionRepository
    ) {
        $this->context = $context;
        $this->productRestrictionRepository = $productRestrictionRepository;
    }

    public function getId()
    {
        try {
            $id = $this->context->getRequest()->getParam('entity_id');
            if ($id) {
                return $this->productRestrictionRepository->getById($id)->getId();
            }
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return null;
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
