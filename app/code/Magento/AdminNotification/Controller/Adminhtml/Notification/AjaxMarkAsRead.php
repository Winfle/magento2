<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

class AjaxMarkAsRead extends \Magento\AdminNotification\Controller\Adminhtml\Notification
{
    /**
     * Mark notification as read (AJAX action)
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->getPostValue()) {
            return;
        }
        $notificationId = (int)$this->getRequest()->getPost('id');
        $responseData = [];
        try {
            $this->_objectManager->create(
                \Magento\AdminNotification\Model\NotificationService::class
            )->markAsRead(
                $notificationId
            );
            $responseData['success'] = true;
        } catch (\Exception $e) {
            $responseData['success'] = false;
        }
        $this->getResponse()->representJson(
            $this->_objectManager->create(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($responseData)
        );
    }
}
