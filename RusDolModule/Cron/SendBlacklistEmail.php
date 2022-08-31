<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Cron;

use Amasty\RusDolModule\Model\BlacklistProvider;
use Amasty\RusDolModule\Model\BlacklistRepository;
use Amasty\RusDolModule\Model\Config\ConfigProvider;
use Magento\Framework\Mail\Template\Factory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Psr\Log\LoggerInterface;

class SendBlacklistEmail
{
    /**
     * @var BlacklistProvider
     */
    private $blacklistProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var Factory
     */
    private $templateFactory;

    /**
     * @var BlacklistRepository
     */
    private $blacklistRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    private const SENDER_NAME = 'Admin';
    private const SENDER_EMAIL = 'admin@amasty.com';

    public function __construct(
        BlacklistProvider $blacklistProvider,
        LoggerInterface $logger,
        ConfigProvider $configProvider,
        TransportBuilder $transportBuilder,
        Factory $templateFactory,
        BlacklistRepository $blacklistRepository
    )
    {
        $this->blacklistProvider = $blacklistProvider;
        $this->logger = $logger;
        $this->configProvider = $configProvider;
        $this->transportBuilder = $transportBuilder;
        $this->templateFactory = $templateFactory;
        $this->blacklistRepository = $blacklistRepository;
    }

    public function execute()
    {
        $blacklistProduct = $this->blacklistProvider->getFirstBlacklistProduct();
        if (!$blacklistProduct->getSku()) {
            $this->logger->notice(get_class($this) . ' - Failed to send email! Blacklist is empty.');
            return;
        }

        $templateVars = [
            'sku' => $blacklistProduct->getSku(),
            'qty' => $blacklistProduct->getQty()
        ];
        $emailSender = [
            'name' => self::SENDER_NAME,
            'email' => self::SENDER_EMAIL
        ];
        $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => 0
        ];

        $templateId = (string)$this->configProvider->getEmailTemplate();
        $recipient = (string)$this->configProvider->getUserEmail();

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFromByScope($emailSender)
            ->addTo($recipient)
            ->getTransport();

        $emailBody = $this->getEmailBody($templateId, $templateVars, $templateOptions);

        try {
            $transport->sendMessage();
            $blacklistProduct->setEmailBody($emailBody);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $blacklistProduct->setEmailBody('Failed to send email');
        } finally {
            $this->blacklistRepository->save($blacklistProduct);
        }
    }

    protected function getEmailBody($templateId, $templateVars, $templateOptions)
    {
        $template = $this->templateFactory->get($templateId)
            ->setVars($templateVars)
            ->setOptions($templateOptions);
        return $template->processTemplate();
    }
}
