<?php
declare(strict_types=1);

namespace Amasty\SecondRusDolModule\Plugin;

use Amasty\RusDolModule\Block\Index;

class ChangeFormActionPlugin
{
    const NEW_ACTION = 'checkout/cart/add';

    public function afterGetFormAction(
        Index $subject,
        $result
    ): string
    {
        return $result = $subject->getUrl(self::NEW_ACTION);
    }
}