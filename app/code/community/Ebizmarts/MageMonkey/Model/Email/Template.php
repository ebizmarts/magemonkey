<?php

/**
 * Mage_Core_Model_Email_Template rewrite class
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Model_Email_Template extends Mage_Core_Model_Email_Template
{

    /**
     * Send mail to recipient
     *
     * @param   array|string $email E-mail(s)
     * @param   array|string|null $name receiver name(s)
     * @param   array $variables template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array())
    {

        //Check if should use MC Transactional Email Service
        if (FALSE === Mage::helper('monkey')->useTransactionalService()) {
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

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));


        $service = Mage::helper('monkey')->config('transactional_emails');
        $apiKey = Mage::helper('monkey')->getApiKey($this->getDesignConfig()->getStore());
        if ('mandrill' == $service) {
            $apiKey = Mage::helper('monkey')->getMandrillApiKey($this->getDesignConfig()->getStore());
        }

        $mail = Ebizmarts_MageMonkey_Model_TransactionalEmail_Adapter::factory($service);
        $mail->setApiKey($apiKey);

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        try {

            $message = array(
                'html' => $text,
                'text' => $text,
                'subject' => ('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?='),
                'from_name' => $this->getSenderName(),
                'from_email' => $this->getSenderEmail(),
                'to_email' => $emails,
                'to_name' => $names
            );

            $sent = $mail->sendEmail($message);
            if ($mail->errorCode) {
                return false;
            }

        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        return true;
    }

}
