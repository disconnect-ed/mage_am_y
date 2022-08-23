<?php
declare(strict_types=1);

namespace Amasty\SecondRusDolModule\Plugin;

use Amasty\RusDolModule\Block\Index;

class ChangeFormActionPlugin
{
    public function afterGetFormAction(
        Index $subject,
        $result
    ) {
        return $result = $subject->getUrl('checkout/cart/add');
    }
}