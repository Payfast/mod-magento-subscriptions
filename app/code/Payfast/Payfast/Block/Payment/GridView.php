<?php
/**
 * Class View
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Block\Payment;

/**
 * Class View
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */

class GridView extends \Magento\Framework\View\Element\Template
{
    /**
     * payfastRecurringPayment
     *
     * @var null
     */
    protected $_payfastRecurringPayment = null;

    /**
     * ShouldRenderInfo
     *
     * @var bool
     */
    protected $_shouldRenderInfo = false;

    /**
     * Info
     *
     * @var $info array
     */
    protected $info = [];

    /**
     * RelatedOrders
     *
     * @var relatedOrders
     */
    protected $_relatedOrders = null;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Payment
     *
     * @var \Payfast\Payfast\Model\Payment
     */
    protected $paymentModel;

    /**
     * StoreManagerInterface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * View constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context      context
     * @param \Magento\Framework\Registry                      $registry     registry
     * @param \Payfast\Payfast\Model\Payfast $paymentModel paymentModel
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager storeManager
     * @param array                                            $data         data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Payfast\Payfast\Model\Payfast $paymentModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->paymentModel = $paymentModel;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * GetRenderedInfo
     *
     * @return array
     */
    public function getRenderedInfo()
    {
        return $this->info;
    }

    /**
     * RenderRowValue
     *
     * @param \Magento\Framework\DataObject $row row
     *
     * @return string
     */
    public function renderRowValue(\Magento\Framework\DataObject $row)
    {
        $value = $row->getValue();
        if (is_array($value)) {
            $value = implode("\n", $value);
        }
        if (!$row->getSkipHtmlEscaping()) {
            $value = $this->escapeHtml($value);
        }
        return nl2br($value);
    }

    /**
     * AddInfo
     *
     * @param array $data data
     * @param null  $key  key
     *
     * @return array
     */
    protected function _addInfo(array $data, $key = null)
    {
        $object = new \Magento\Framework\DataObject($data);
        if ($key) {
            $this->info[$key] = $object;
        } else {
            $this->info[] = $object;
        }
    }

    /**
     * PrepareLayout
     *
     * @return \Magento\Framework\View\Element\Template
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        $currentpayfastRecurringPayment = $this->paymentModel->load($this->getRequest()->getParam('payment'));
        $this->_payfastRecurringPayment = $currentpayfastRecurringPayment->setStore($this->storeManager->getStore());
        return parent::_prepareLayout();
    }

    /**
     * ToHtml
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        if (!$this->_payfastRecurringPayment || $this->_shouldRenderInfo && !$this->info) {
            return '';
        }

        if ($this->hasShouldPrepareInfoTabs()) {
            $layout = $this->getLayout();
            foreach ($this->getGroupChildNames('info_tabs') as $name) {
                $block = $layout->getBlock($name);
                if (!$block) {
                    continue;
                }
                $block->setViewUrl(
                    $this->getUrl(
                        "*/*/{$block->getViewAction()}",
                        ['payment' => $this->_payfastRecurringPayment->getId()]
                    )
                );
            }
        }

        return parent::_toHtml();
    }
}
