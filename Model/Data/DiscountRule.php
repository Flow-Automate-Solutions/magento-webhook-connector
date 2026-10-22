<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model\Data;

use Magento\SalesRule\Model\Data\Rule;

class DiscountRule extends Rule
{
    /**
     * @return string|null
     */
    public function getCouponCode()
    {
        $code = $this->_get('coupon_code');
        if ($code === null || $code === '') {
            return null;
        }
        return (string)$code;
    }

    /**
     * @param string|null $couponCode
     * @return $this
     */
    public function setCouponCode($couponCode)
    {
        return $this->setData('coupon_code', $couponCode);
    }
}
