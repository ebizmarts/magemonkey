<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/7/14
 * Time   : 4:27 PM
 * File   : Template.php
 * Module : Ebizmarts_Mandrill
 */
class Ebizmarts_Mandrill_Model_Email_Template extends Mage_Core_Model_Email_Template
{
//    protected $_bcc = array();
    protected $_mail = null;

    /**
     * @param array|string $email
     * @param null $name
     * @param array $variables
     * @return bool
     */
    public function send($email, $name = null, array $variables = array())
    {
        $storeId = Mage::app()->getStore()->getId();
        if (!Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE, $storeId)) {
            return parent::send($email, $name, $variables);
        }
        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }
        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        // Get message
        $this->setUseAbsoluteLinks(true);
        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);
        $message = $this->getProcessedTemplate($variables, true);
        $subject = $this->getProcessedTemplateSubject($variables);

        //$email = array('subject' => $this->getProcessedTemplateSubject($variables), 'to' => array());
        $email = array('subject' => $subject, 'to' => array());
        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }
        $mail = $this->getMail();
        $max = count($emails);
        for ($i = 0; $i < $max; $i++) {
            if (isset($names[$i])) {
                $email['to'][] = array(
                    'email' => $emails[$i],
                    'name' => $names[$i]
                );
            } else {
                $email['to'][] = array(
                    'email' => $emails[$i],
                    'name' => ''
                );
            }
        }
        foreach ($mail->getBcc() as $bcc) {
            $email['to'][] = array(
                'email' => $bcc,
                'type' => 'bcc'
            );
        }

        $email['from_name'] = $this->getSenderName();
        $email['from_email'] = $this->getSenderEmail();
        $emailArray = explode('@', $email['from_email']);
        if (count($emailArray) > 1) {
            $email = $this->_setEmailData($message, $mail, $email, $emailArray, $storeId);

            if ($this->hasQueue() && $this->getQueue() instanceof Mage_Core_Model_Email_Queue) {
                $emailQueue = $this->getQueue();
                $emailQueue->setMessageBody($message);
                $emailQueue->setMessageParameters(
                    array(
                        'subject' => $subject,
                        'return_path_email' => $returnPathEmail,
                        'is_plain' => $this->isPlain(),
                        'from_email' => $this->getSenderEmail(),
                        'from_name' => $this->getSenderName()
                    )
                )
                    ->addRecipients($emails, $names, Mage_Core_Model_Email_Queue::EMAIL_TYPE_TO)
                    ->addRecipients($this->_bccEmails, array(), Mage_Core_Model_Email_Queue::EMAIL_TYPE_BCC);
                $emailQueue->addMessageToQueue();
                return true;
            }
        }
        try {
            $result = $mail->messages->send($email);
            $this->_mail = null;
        } catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            return false;
        }
        return true;

    }

    /**
     * @return Mandrill_Message|Zend_Mail
     */
    public function getMail()
    {
        $storeId = Mage::app()->getStore()->getId();
        if (!Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE, $storeId)) {
            return parent::getMail();
        }
        if ($this->_mail) {
            return $this->_mail;
        } else {
            $storeId = Mage::app()->getStore()->getId();
            Mage::helper('ebizmarts_mandrill')->log("store: $storeId API: " . Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::APIKEY, $storeId));
            $this->_mail = new Mandrill_Message(Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::APIKEY, $storeId));
            return $this->_mail;
        }
    }

    protected function _setEmailData($message, $mail, $email, $emailArray, $storeId)
    {
        $mandrillSenders = $mail->senders->domains();
        $senderExists = false;
        foreach ($mandrillSenders as $sender) {
            if ($emailArray[1] == $sender['domain']) {
                $senderExists = true;
            }
        }
        if (!$senderExists) {
            $email['from_email'] = Mage::getStoreConfig('trans_email/ident_general/email', $storeId);
        }
        $headers = $mail->getHeaders();
        $headers[] = Mage::helper('ebizmarts_mandrill')->getUserAgent();
        $email['headers'] = $headers;
        if (isset($variables['tags']) && count($variables['tags'])) {
            $email ['tags'] = $variables['tags'];
        }

        if (isset($variables['tags']) && count($variables['tags'])) {
            $email ['tags'] = $variables['tags'];
        } else {
            $templateId = (string)$this->getId();
            $templates = parent::getDefaultTemplates();
            if (isset($templates[$templateId]) && isset($templates[$templateId]['label'])) {
                $email ['tags'] = array(substr($templates[$templateId]['label'], 0, 50));
            } else {
                if ($this->getTemplateCode()) {
                    $email ['tags'] = array(substr($this->getTemplateCode(), 0, 50));
                } else {
                    if ($templateId) {
                        $email ['tags'] = array(substr($templateId, 0, 50));
                    } else {
                        $email['tags'] = array('default_tag');
                    }
                }
            }
        }

        if ($att = $mail->getAttachments()) {
            $email['attachments'] = $att;
        }
        if ($this->isPlain()) {
            $email['text'] = $message;
        } else {
            $email['html'] = $message;
        }
        return $email;
    }
}
