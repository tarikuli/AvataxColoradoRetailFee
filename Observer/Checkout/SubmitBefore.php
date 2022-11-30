<?php
declare(strict_types=1);

namespace Studio3Marketing\AvataxColoradoRetailFee\Observer\Checkout;

class SubmitBefore implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;

    /** @var \Magento\Quote\Model\QuoteFactory */
    protected $quote;

    /** @var \Magento\Quote\Api\CartRepositoryInterface */
    protected $quoteRepository;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    /** @var \Magento\Checkout\Model\Cart */
    protected $_cart;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->productRepository = $productRepository;
        $this->quoteRepository = $quoteRepository;
        $this->quote = $quote;
        $this->messageManager = $messageManager;
        $this->_cart = $cart;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
            /** @var  \Magento\Catalog\Api\ProductRepositoryInterface $product */
            $product = $this->productRepository->get("ColoradoRetailTax");
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $observer->getEvent()->getQuote();
            $shippingAddress = $quote->getShippingAddress();
            $region = $shippingAddress->getData('region');
            if ($region == "Colorado") {
                try {
                    $params = array(
                        'qty' => 1
                    );
                    $this->_cart->addProduct($product, $params);
                    $this->_cart->save();
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            } else {
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getSku() == "ColoradoRetailTax") {
                        $itemId = $item->getItemId();
                        $this->_cart->removeItem($itemId)->save();
                    }
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}

