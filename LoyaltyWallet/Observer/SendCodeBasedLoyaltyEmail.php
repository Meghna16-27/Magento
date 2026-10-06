<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Observer;

use Codilar\LoyaltyWallet\Api\LoyaltyLedgerRepositoryInterface;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Psr\Log\LoggerInterface;

class SendCodeBasedLoyaltyEmail implements ObserverInterface
{
    public function __construct(
        private readonly TransportBuilder $transportBuilder,
        private readonly LoyaltyLedgerRepositoryInterface $ledgerRepository,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        try {
            $order = $observer->getEvent()->getOrder();
            if (!$order || !$order->getCustomerEmail()) {
                return;
            }

            $customerId = $order->getCustomerId();
            $orderIncrementId = $order->getIncrementId();
            $pointsEarned = 0;

            if ($customerId) {
                $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDescendingDirection()->create();
                $searchCriteria = $this->searchCriteriaBuilderFactory->create()
                    ->addFilter('customer_id', $customerId)
                    ->addFilter('order_increment_id', $orderIncrementId)
                    ->addSortOrder($sortOrder)
                    ->setPageSize(1)
                    ->create();

                $transactions = $this->ledgerRepository->getList($searchCriteria)->getItems();
                $latestRecord = reset($transactions);

                if ($latestRecord) {
                    $pointsEarned = (int)$latestRecord->getQuantity();
                }
            }

            // Only send if points were actually earned for this order
            if ($pointsEarned <= 0) {
                return;
            }

            // Send the code-defined email template programmatically
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('codilar_loyalty_notification_template')
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => $order->getStoreId(),
                ])
                ->setTemplateVars([
                    'order' => $order,
                    'customer_name' => $order->getCustomerName(),
                    'earned_loyalty_points' => $pointsEarned,
                ])
                ->setFromByScope('sales', $order->getStoreId())
                ->addTo($order->getCustomerEmail(), $order->getCustomerName())
                ->getTransport();

            $transport->sendMessage();

        } catch (Exception $e) {
            $this->logger->error('Error sending code-based loyalty email: ' . $e->getMessage());
        }
    }
}
