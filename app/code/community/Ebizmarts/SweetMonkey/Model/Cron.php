<?php

/**
 * Model to handle cron tasks logic
 *
 * @author Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_SweetMonkey_Model_Cron {

    const XML_PATH_EXPIRY_EMAIL_TEMPLATE = 'sweetmonkey/general/warning/expiry_email_template';
    const XML_PATH_EXPIRY_EMAIL_IDENTITY = 'sweetmonkey/general/warning/expiry_email_identity';
    const XML_PATH_POINTS_EMAIL_TEMPLATE = 'sweetmonkey/general/warning/points_email_template';
    const XML_PATH_POINTS_EMAIL_IDENTITY = 'sweetmonkey/general/warning/points_email_identity';

    protected $_daysUntilExpiry = 3;
    protected $_pointsToNotify = 500;

    /**
     * Send automated emails to the customers
     *
     * @return void
     */
    public function automatedEmails() {
        $this->checkAllCustomers();
    }

    public function checkAllCustomers() {
        $customers = Mage::getModel('rewards/customer')->getCollection();
        foreach ($customers as $c) {
            if (!Mage::helper('rewards/expiry')->isEnabled($c->getStoreId()))
                continue;
            $c = Mage::getModel('rewards/customer')->load($c->getId());

            /* send an automated email 3 days before a points expiration */
            $days = Mage::getModel('rewards/expiry')->getDaysUntilExpiry($c);
            if ($days == $this->_daysUntilExpiry) {
                $points = $c->getPoints();
                $vars = array(
                    'customer_name' => $c->getName(),
                    'customer_email' => $c->getEmail(),
                    'store_name' => $c->getStore()->getName(),
                    'points_transferred' => (string) $points[1],
                    'points_balance' => (string) $c->getPointsSummary(),
                    'days_left' => $days,
                    'days_until_expiry' => $this->_daysUntilExpiry
                );
                $email = Mage::getModel('core/email_template');
                $email->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EXPIRY_EMAIL_TEMPLATE), Mage::getStoreConfig(self::XML_PATH_EXPIRY_EMAIL_IDENTITY), $c->getEmail(), $c->getName(), $vars
                );
            }

            /* send an automated email if a customer has a 500 points or more */
            $points = $c->getPoints();
            if ($points[1] >= $this->_pointsToNotify) {
                $vars = array(
                    'customer_name' => $c->getName(),
                    'customer_email' => $c->getEmail(),
                    'store_name' => $c->getStore()->getName(),
                    'points_transferred' => (string) $points[1],
                    'points_balance' => (string) $c->getPointsSummary(),
                    'days_left' => $days,
                    'points_to_notify' => $this->_pointsToNotify
                );
                $email = Mage::getModel('core/email_template');
                $email->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_POINTS_EMAIL_TEMPLATE), Mage::getStoreConfig(self::XML_PATH_POINTS_EMAIL_IDENTITY), $c->getEmail(), $c->getName(), $vars
                );
            }
        }
    }

    /**
     * Push customers vars to MailChimp
     *
     * @return void
     */
    public function pushMergeVarsForCustomers() {

        $customers = Mage::getModel('rewards/customer')->getCollection();

        foreach ($customers as $c) {
            if (!Mage::helper('rewards/expiry')->isEnabled($c->getStoreId())) {
                continue;
            }

            $customer = Mage::getModel('rewards/customer')->load($c->getId());
            Mage::helper('sweetmonkey')->pushVars($customer);
        }
    }

}