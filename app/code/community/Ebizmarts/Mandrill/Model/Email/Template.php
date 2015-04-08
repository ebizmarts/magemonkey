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

        $email = array('subject' => $this->getProcessedTemplateSubject($variables), 'to' => array());

        $mail = $this->getMail();

        for ($i = 0; $i < count($emails); $i++) {
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
        $email['headers'] = $mail->getHeaders();
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
        if ($this->isPlain())
            $email['text'] = $message;
        else
            $email['html'] = $message;

        try {
            $result = $mail->messages->send($email);
        } catch (Exception $e) {
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
}