<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Block\Adminhtml\System\Config;

use Magic\WebhookConnector\Model\Config;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class WebhookStatus extends Field
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        private readonly Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $isEnabled = $this->config->isEnabled();
        $status = $isEnabled ? 'Enabled' : 'Disabled';
        $statusColor = $isEnabled ? '#2e7d32' : '#b71c1c';

        $html = '<div style="line-height:1.6;">';
        $html .= '<strong>Webhook Status:</strong> ';
        $html .= '<span style="color:' . $statusColor . ';">' . $status . '</span><br/>';
        $html .= 'This module uses fixed code constants for enable/timeout/retry. Magento Admin cannot edit runtime webhook controls.';
        $html .= '<br/><br/><strong>Fixed constants:</strong>';
        $html .= '<ul style="margin:6px 0 0 18px;">';
        $html .= '<li>MAGIC_WEBHOOK_ENABLED = true</li>';
        $html .= '<li>MAGIC_WEBHOOK_URL = https://meera-dev-api.flowautomate.com/webhook/magento</li>';
        $html .= '<li>MAGIC_WEBHOOK_TIMEOUT_SECONDS = 5</li>';
        $html .= '<li>MAGIC_WEBHOOK_RETRY_COUNT = 1</li>';
        $html .= '</ul>';
        $html .= '<br/>Webhook Secret is configured below in this Admin page.';
        $html .= '</div>';

        return $html;
    }
}
