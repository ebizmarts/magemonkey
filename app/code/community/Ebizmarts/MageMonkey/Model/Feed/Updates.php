<?php

/**
 * Feed updates model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_Feed_Updates {

    private $_key = 'monkey';
    private $_resources = array('Ebizmarts_MageMonkey', 'Ebizmarts_Global');
    
    /**
     * Retrieve feed data as XML element
     *
     * @return SimpleXMLElement
     */
    public function getFeedData($uri) {
        $curl = new Varien_Http_Adapter_Curl;
        $curl->setConfig(array(
            'timeout' => 30
        ));
        $curl->write(Zend_Http_Client::GET, $uri, '1.0');
        $data = $curl->read();
        if ($data === false) {
            return false;
        }
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        $curl->close();

        try {
            $xml = new SimpleXMLElement($data);
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $xml;
    }

    public function getConfig($key) {
        return Mage::getStoreConfig($this->_key . '/notifications/' . $key);
    }

    /**
     * Retrieve DB date from RSS date
     *
     * @param string $rssDate
     * @return string YYYY-MM-DD YY:HH:SS
     */
    public function getDate($rssDate) {
        return gmdate('Y-m-d H:i:s', strtotime($rssDate));
    }    
    
    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl() {
        return $this->getConfig('updates_url');
    }

    public function getLastUpdate($resource) {
        return Mage::app()->loadCache($this->_key . $resource . '_updates_feed_lastcheck');
    }


    /**
     * Checks feed
     * @return
     */
    public function check() {

        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            return $this;
        }        
        
        foreach($this->_resources as $resource) {
            
            if(((int)$this->getConfig('check_frequency')) + $this->getLastUpdate($resource) < time()) {
                $this->_getUpdates($resource);
            }
            
        }
    }

    protected function _getUpdates($resource) {
        $feedData = array();

        try {

            $node = $this->getFeedData($this->getFeedUrl() . "{$resource}.xml");

            if (!$node) {
                Mage::app()->saveCache(time(), $this->_key . $resource . '_updates_feed_lastcheck');
                return false;
            }

            foreach ($node->xpath('items/item') as $item) {        
                
                $feedData[] = array(
                    'severity' => (string) $item->severity,
                    'date_added' => (string) $item->created_at,
                    'title' => (string) $item->title,
                    'description' => (string) $item->description,
                    'url' => (string) $item->url,
                );
            }

            if (count($feedData)) {
                Mage::getModel('adminnotification/inbox')->parse($feedData);
            }

            Mage::app()->saveCache(time(), $this->_key . $resource . '_updates_feed_lastcheck');

            return true;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

}