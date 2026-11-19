<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Block;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template;

class Attribution extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getFormKeyValue(): string
    {
        return $this->formKey->getFormKey();
    }

    public function getEndpoint(): string
    {
        return $this->getUrl('meera/attribution/touch');
    }
}
