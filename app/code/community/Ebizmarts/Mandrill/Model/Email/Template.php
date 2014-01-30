<?php

/**
 * Mage_Core_Model_Email_Template rewrite class
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Mandrill
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_Mandrill_Model_Email_Template extends Mage_Core_Model_Email_Template {

	protected $_mandrill = null;
    protected $replyTo = null;
	protected $_bcc = array();

	public function getMail() {

		//Check if should use Mandrill Transactional Email Service
        if(FALSE === Mage::helper('mandrill')->useTransactionalService()){
            return parent::getMail();
        }

		if(is_null($this->_mandrill)){
			$this->_mandrill = Mage::helper('mandrill')->api();
			$this->_mandrill->setApiKey(Mage::helper('mandrill')->getApiKey());
		}
		return $this->_mandrill;
	}

	/**
	 * Add BCC emails to list to send.
	 *
	 * @return Ebizmarts_Mandrill_Model_Email_Template
	 */
    public function addBcc($bcc) {
		$helper = Mage::helper('mandrill');
    	if(FALSE === $helper->useTransactionalService()){
            return parent::addBcc($bcc);
        }
        if (is_array($bcc)) {
            foreach ($bcc as $email) {
                $this->_bcc[] = $email;
            }
        }
        elseif ($bcc) {
            $this->_bcc[] = $bcc;
        }
        return $this;

    }

    /**
     * Send mail to recipient
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array()) {

        $helper = Mage::helper('mandrill');

		//Check if should use Mandrill Transactional Email Service
        if(FALSE === $helper->useTransactionalService()){
            return parent::send($email, $name, $variables);
        }

        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }

        $emails = array_values((array)$email);

		if(count($this->_bcc) > 0){
//			$bccEmail = $this->_bcc[0];
            $bccEmail = $this->_bcc;
		}else{
			$bccEmail = '';
		}

        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        $mail = $this->getMail();

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        try {

            $message = array (
					        'subject'     => $this->getProcessedTemplateSubject($variables),
					        'from_name'   => $this->getSenderName(),
					        'from_email'  => $this->getSenderEmail(),
					        'to_email'    => $emails,
					        'to_name'     => $names,
					        'bcc_address' => $bccEmail,
					        'headers'	  => array('Reply-To' => $this->replyTo)
				        );

			if($this->isPlain()) {
		 		$message['text'] = $text;
			} else {
				$message['html'] = $text;
			}
            if(isset($variables['tags']) && count($variables['tags'])) {
                $message ['tags'] = $variables['tags'];
            }
            else {
                $templateId = (string)$this->getId();
                $templates = parent::getDefaultTemplates();
                if (isset($templates[$templateId])) {
                	$message ['tags'] =  array(substr($templates[$templateId]['label'], 0, 50));
				} else {
				        if($this->getTemplateCode()){
				        	$message ['tags'] = array(substr($this->getTemplateCode(), 0, 50));
				        } else {
				        	$message ['tags'] = array(substr($templateId, 0, 50));
				        }
				}
            }

            $sent = $mail->sendEmail($message);
            if($mail->errorCode){
				return false;
			}

        }catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        return true;
    }

    public function setReplyTo($email) {
        if(FALSE === Mage::helper('mandrill')->useTransactionalService()) {
            return parent::setReplyTo($email);
        }

		$this->replyTo = $email;
        return $this;
    }

	public function createAttachment($body,
                                     $mimeType    = Zend_Mime::TYPE_OCTETSTREAM,
                                     $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
                                     $encoding    = Zend_Mime::ENCODING_BASE64,
                                     $filename    = null)
    {

    }
    public function addAttachment(Zend_Mime_Part $att)
    {

    }

    public function addTo($email, $name = null)
    {
    	if(FALSE === Mage::helper('mandrill')->useTransactionalService()) {
	        array_push($this->_bcc, $email);
	        return $this;
    	}
    }

}
