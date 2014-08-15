<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/14/14
 * Time   : 6:48 PM
 * File   : Message.php
 * Module : Ebizmarts_Mandrill
 */
class Mandrill_Message extends Mandrill_Mandrill
{
    protected $_attachments = array();
    protected $_bcc = array();
    protected $_bodyText = false;
    protected $_bodyHtml = false;

    public function createAttachment($body,
                                     $mimeType    = Zend_Mime::TYPE_OCTETSTREAM,
                                     $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
                                     $encoding    = Zend_Mime::ENCODING_BASE64,
                                     $filename    = null)
    {
        $att = array('type' => $mimeType,'name' => $filename,'content'=> base64_encode($body));
        array_push($this->_attachments,$att);
    }
    public function log($m)
    {
        $storeId = Mage::app()->getStore()->getId();
        if(Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE_LOG,$storeId))
        {
            Mage::log($m,Zend_Log::INFO,'Mandrill.log');
        }
    }
    public function getAttachments()
    {
        return $this->_attachments;
    }
    public function addBcc($bcc)
    {
        $storeId = Mage::app()->getStore()->getId();
        if(is_array($bcc))
        {
            foreach($bcc as $email)
            {
                array_push($this->_bcc,$email);
            }
        }
        else
        {
            array_push($this->_bcc,$bcc);
        }
    }
    public function getBcc()
    {
        return $this->_bcc;
    }
    public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        $this->_bodyHtml = $html;
    }
    public function getBodyHtml()
    {
        return $this->_bodyHtml;
    }
    public function setBodyText($txt, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        $this->_bodyText = $txt;
    }
    public function getBodyText()
    {
        return $this->_bodyText;
    }

}