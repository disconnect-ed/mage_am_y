<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;

class StopAjaxPlugin
{
    /**
     * @var Http
     */
    protected $request;

    public function __construct(
        Http $request
    )
    {
        $this->request = $request;
    }

    public function aroundExecute(
        $subject,
        $proceed,
        $observer
    )
    {
        if (!$this->request->isAjax()) {
            return $proceed($observer);
        }
    }
}