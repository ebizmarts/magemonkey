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
        if(!Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE,$storeId)) {
           return parent::send($email, $name,$variables);
        }
        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }
        $emails = array_values( (array)$email );
        $names = is_array( $name ) ? $name : (array)$name;
        $names = array_values( $names );
        foreach ( $emails as $key => $email ) {
            if ( ! isset( $names[$key] ) ) {
                $names[ $key ] = substr( $email, 0, strpos( $email, '@' ) );
            }
        }

        // Get message
        $this->setUseAbsoluteLinks( true );
        $variables['email'] = reset( $emails );
        $variables['name'] = reset( $names );
        $message = $this->getProcessedTemplate( $variables, true );

        $email = array( 'subject' => $this->getProcessedTemplateSubject( $variables ), 'to' => array() );


        $mail = $this->getMail();

        for ( $i = 0; $i < count( $emails ); $i++ ) {
            if ( isset( $names[ $i ] ) ) {
                $email['to'][] = array(
                    'email' => $emails[ $i ],
                    'name' => $names[ $i ]
                );
            }
            else {
                $email['to'][] = array(
                    'email' => $emails[ $i ],
                    'name' => ''
                );
            }
        }
        foreach($mail->getBcc() as $bcc)
        {
            $email['to'][] = array(
                'email' => $bcc,
                'type' => 'bcc'
            );
        }
//        for ( $i = 0; $i < count( $this->_bcc ); $i++ ) {
//            $email['to'][] = array(
//                'email' => $this->_bcc[ $i ],
//                'type' => 'bcc'
//            );
//        }

        $email['from_name'] = $this->getSenderName();
        $email['from_email'] = $this->getSenderEmail();
        if($att = $mail->getAttachments()) {
            $email['attachments'] = $att;
        }
        if( $this->isPlain() )
            $email['text'] = $message;
        else
            $email['html'] = $message;

        try {
            $result = $mail->messages->send( $email );
        }
        catch( Exception $e ) {
            // Oops, some error in sending the email!
            Mage::logException( $e );
            return false;
        }

        // Woo hoo! Email sent!
        return true;

    }

    /**
     * @return Mandrill_Message|Zend_Mail
     */
    public function getMail()
    {
        if($this->_mail) {
            return $this->_mail;
        }
        else {
            $storeId = Mage::app()->getStore()->getId();
            $this->_mail = new Mandrill_Message(Mage::getStoreConfig( Ebizmarts_Mandrill_Model_System_Config::APIKEY,$storeId ));
            return $this->_mail;
        }
    }

    /**
     * @param $bcc
     * @return $this
     */
//    public function addBcc($bcc){
//        $storeId = Mage::app()->getStore()->getId();
//        if ( Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE,$storeId) && Mage::getStoreConfig( Ebizmarts_Mandrill_Model_System_Config::APIKEY,$storeId ) != '' ) {
//
//            if ( is_array( $bcc ) ) {
//                foreach ( $bcc as $email ) {
//                    $this->_bcc[] = $email;
//                }
//            }
//            elseif ( $bcc ) {
//                $this->_bcc[] = $bcc;
//            }
//            return $this;
//
//        }
//        else {
//            // Extension is not enabled, use parent's function
//            return parent::addBcc( $bcc );
//        }
//
//    }
    private function parseMessage(Message $message, DateTime $sendAt = null)
    {
        $hasTemplate = ($message instanceof MandrillMessage && null !== $message->getTemplate());
        $from = $message->getFrom();
        if (($hasTemplate && count($from) > 1) || (!$hasTemplate && count($from) !== 1)) {
            throw new Exception\RuntimeException(
                'Mandrill API requires exactly one from sender (or none if send with a template)'
            );
        }
        $from = $from->rewind();
        $parameters['message'] = array(
            'subject' => $message->getSubject(),
            'text' => $this->extractText($message),
            'html' => $this->extractHtml($message),
            'from_email' => ($from ? $from->getEmail() : ''),
            'from_name' => ($from ? $from->getName() : '')
        );
        foreach ($message->getTo() as $to) {
            $parameters['message']['to'][] = array(
                'email' => $to->getEmail(),
                'name' => $to->getName(),
                'type' => 'to'
            );
        }
        foreach ($message->getCc() as $cc) {
            $parameters['message']['to'][] = array(
                'email' => $cc->getEmail(),
                'name' => $cc->getName(),
                'type' => 'cc'
            );
        }
        foreach ($message->getBcc() as $bcc) {
            $parameters['message']['to'][] = array(
                'email' => $bcc->getEmail(),
                'name' => $bcc->getName(),
                'type' => 'bcc'
            );
        }
        $replyTo = $message->getReplyTo();
        if (count($replyTo) > 1) {
            throw new Exception\RuntimeException('Mandrill has only support for one Reply-To address');
        } elseif (count($replyTo)) {
            $parameters['message']['headers']['Reply-To'] = $replyTo->rewind()->toString();
        }
        if ($message instanceof MandrillMessage) {
            if ($hasTemplate) {
                $parameters['template_name'] = $message->getTemplate();
                foreach ($message->getTemplateContent() as $key => $value) {
                    $parameters['template_content'][] = array(
                        'name' => $key,
                        'content' => $value
                    );
                }
// Currently, Mandrill API requires a content for template_content, even if it is an
// empty array
                if (!isset($parameters['template_content'])) {
                    $parameters['template_content'] = array();
                }
            }
            foreach ($message->getGlobalVariables() as $key => $value) {
                $parameters['message']['global_merge_vars'][] = array(
                    'name' => $key,
                    'content' => $value
                );
            }
            foreach ($message->getVariables() as $recipient => $variables) {
                $recipientVariables = array();
                foreach ($variables as $key => $value) {
                    $recipientVariables[] = array(
                        'name' => $key,
                        'content' => $value
                    );
                }
                $parameters['message']['merge_vars'][] = array(
                    'rcpt' => $recipient,
                    'vars' => $recipientVariables
                );
            }
            $parameters['message']['metadata'] = $message->getGlobalMetadata();
            foreach ($message->getMetadata() as $recipient => $variables) {
                $parameters['message']['recipient_metadata'][] = array(
                    'rcpt' => $recipient,
                    'values' => $variables
                );
            }
            foreach ($message->getOptions() as $key => $value) {
                $parameters['message'][$key] = $value;
            }
            foreach ($message->getTags() as $tag) {
                $parameters['message']['tags'][] = $tag;
            }
            foreach ($message->getImages() as $image) {
                $parameters['message']['images'][] = array(
                    'type' => $image->type,
                    'name' => $image->filename,
                    'content' => base64_encode($image->getRawContent())
                );
            }
        }
        $attachments = $this->extractAttachments($message);
        foreach ($attachments as $attachment) {
            $parameters['message']['attachments'][] = array(
                'type' => $attachment->type,
                'name' => $attachment->filename,
                'content' => base64_encode($attachment->getRawContent())
            );
        }
        if (null !== $sendAt) {
// Mandrill needs to have date in UTC, using this format
            $sendAt->setTimezone(new DateTimeZone('UTC'));
            $parameters['send_at'] = $sendAt->format('Y-m-d H:i:s');
        }
        return $parameters;
    }
}