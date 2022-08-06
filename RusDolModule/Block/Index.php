<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Block;

use Magento\Framework\View\Element\Template;

class Index extends Template {

    public function echoText(string $text): string {
        return $text;
    }

}