<?php
/**
 * Copyright © CO-WELL Co., LTD. All rights reserved.
 * Copyright © CO-WELL ASIA Co., LTD. All rights reserved.
 * 
 * Licensed under the Open Software License version 3.0
 * See LICENSE.txt and COPYING.txt for license details.
 */

namespace Cowell\AbandonedCart\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class ObserverMailLog
 * @package Cowell\AbandonedCart\Observer
 */
class ObserverMailLog implements ObserverInterface
{
    /**
     * @var MailLogFactory
     */
    protected $mailLogFactory;

    /**
     * @var \Cowell\AbandonedCart\Model\QuoteAlertStatusFactory
     */
    protected $quoteAlertStatusFactory;

    /**
     * ObserverMailLog constructor.
     * @param \Cowell\AbandonedCart\Model\MailLogFactory $mailLogFactory
     * @param \Cowell\AbandonedCart\Model\QuoteAlertStatusFactory $quoteAlertStatusFactory
     */
    public function __construct(
        \Cowell\AbandonedCart\Model\MailLogFactory $mailLogFactory,
        \Cowell\AbandonedCart\Model\QuoteAlertStatusFactory $quoteAlertStatusFactory
    ) {
        $this->mailLogFactory          = $mailLogFactory;
        $this->quoteAlertStatusFactory = $quoteAlertStatusFactory;
    }

    /**
     * Add data to table mail log
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $emailData = $observer->getData('emailData');

        $modelStatus = $this->quoteAlertStatusFactory->create();
        $modelStatus->addData([
            "quote_id" => $emailData['quote_id'],
            "type"     => $emailData['type']
        ]);
        $modelStatus->save();

        // Converter o email_content para string se for um objeto Symfony Email
        $emailContent = $emailData['email_content'];
        if ($emailContent instanceof \Symfony\Component\Mime\Email) {
            // Tentar obter o conteúdo HTML primeiro, depois texto, ou string vazia como fallback
            $emailContent = $emailContent->getHtmlBody() ?: $emailContent->getTextBody() ?: '';
        } elseif (is_object($emailContent)) {
            // Se for outro tipo de objeto, tentar converter para string
            $emailContent = method_exists($emailContent, '__toString') ? (string) $emailContent : '';
        }

        $model = $this->mailLogFactory->create();
        $model->addData([
            "quote_id"      => $emailData['quote_id'],
            "type"          => $emailData['type'],
            "status"        => $emailData['status'],
            "email_subject" => $emailData['email_subject'],
            "email_content" => $emailContent,
            "email_to"      => $emailData['email_to'],
            "sender"        => $emailData['sender'],
            "store_id"      => $emailData['store_id']
        ]);
        $model->save();
    }
}
