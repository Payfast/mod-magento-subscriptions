<?php namespace Payfast\Payfast\Ui\Component\Recurring\Payment\Listing\Column;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Url;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var string
     */
    protected $_viewUrl;

    protected $backendUrl;
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Url $urlBuilder
     * @param string $viewUrl
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Url $urlBuilder,
        Context $backendContext,
        $viewUrl = '',
        array $components = [],
        array $data = []
    ) {
        $this->backendUrl = $backendContext->getBackendUrl();
        $this->_urlBuilder = $urlBuilder;
        $this->_viewUrl    = $viewUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['payment_id'])) {
                    $item[$name]['view']   = [
                        'href'  => $this->backendUrl->getUrl(
                            'sales/payfast_recurring_payment/view',
                                      ['payment' => $item['payment_id'] ]),
                        'label' => __('View')
                    ];
                }
            }
        }
        return $dataSource;
    }
}

