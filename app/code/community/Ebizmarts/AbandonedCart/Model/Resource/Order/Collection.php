<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Model_Resource_Order_Collection extends Mage_Reports_Model_Mysql4_Order_Collection
{
    public function isLive()
    {
        return true;
    }

    /**
     * @param string $period
     * @return Ebizmarts_AbandonedCart_Model_Resource_Order_Collection|Mage_Reports_Model_Resource_Order_Collection
     */
    public function addCreateAtPeriodFilter($period)
    {
        list($from, $to) = $this->getDateRange($period, 0, 0, true);

        $this->checkIsLive($period);

        if ($this->isLive()) {
            $fieldToFilter = 'main_table.created_at';
        } else {
            $fieldToFilter = 'period';
        }

        $this->addFieldToFilter($fieldToFilter, array(
            'from' => $from->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
            'to' => $to->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
        ));

        return $this;
    }

    /**
     * @param int $isFilter
     * @return Ebizmarts_AbandonedCart_Model_Resource_Order_Collection|Mage_Reports_Model_Resource_Order_Collection
     */
    public function calculateSales($isFilter = 0)
    {
        $statuses = Mage::getSingleton('sales/config')
            ->getOrderStatusesForState(Mage_Sales_Model_Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }
        $adapter = $this->getConnection();

        if (Mage::getStoreConfig('sales/dashboard/use_aggregated_data') == 8) {
            $this->setMainTable('sales/order_aggregated_created');
            $this->removeAllFieldsFromSelect();
            $averageExpr = $adapter->getCheckSql(
                'SUM(main_table.orders_count) > 0',
                'SUM(main_table.total_revenue_amount)/SUM(main_table.orders_count)',
                0);
            $this->getSelect()->columns(array(
                'lifetime' => 'SUM(main_table.total_revenue_amount)',
                'average' => $averageExpr
            ));

            if (!$isFilter) {
                $this->addFieldToFilter('main_table.store_id',
                    array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
                );
            }
            $this->getSelect()->where('main_table.order_status NOT IN(?)', $statuses);
        } else {
            $this->setMainTable('sales/order');
            $this->removeAllFieldsFromSelect();
            if (version_compare(Mage::getVersion(), '1.6.0.0') == 1) {
                $expr = 'IFNULL(main_table.base_subtotal, 0) - IFNULL(main_table.base_subtotal_refunded, 0)'
                    . ' - IFNULL(main_table.base_subtotal_canceled, 0) - ABS(IFNULL(main_table.base_discount_amount, 0))'
                    . ' + IFNULL(main_table.base_discount_refunded, 0)';
            } else if (version_compare(Mage::getVersion(), '1.6.0.0', '<')) {
                $expr = sprintf('%s - %s - %s - (%s - %s - %s)',
                    "IFNULL('main_table.base_total_invoiced', 0)",
                    "IFNULL('main_table.base_tax_invoiced', 0)",
                    "IFNULL('main_table.base_shipping_invoiced', 0)",
                    "IFNULL('main_table.base_total_refunded', 0)",
                    "IFNULL('main_table.base_tax_refunded', 0)",
                    "IFNULL('main_table.base_shipping_refunded', 0)"
                );
            } else {
                $expr = sprintf('%s - %s - %s - (%s - %s - %s)',
                    $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_shipping_refunded', 0)
                );
            }

            if ($isFilter == 0) {
                $expr = '(' . $expr . ') * main_table.base_to_global_rate';
            }

            $this->getSelect()
                ->columns(array(
                    'lifetime' => "SUM({$expr})",
                    'average' => "AVG({$expr})"
                ))
                ->where('main_table.status NOT IN(?)', $statuses)
                ->where('main_table.state NOT IN(?)', array(
                        Mage_Sales_Model_Order::STATE_NEW,
                        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
                );
        }
        return $this;
    }

    /**
     * @param string $range
     * @param string $customStart
     * @param string $customEnd
     * @param bool $returnObjects
     * @return array
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);

        switch ($range) {
            case '24h':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subDay(1);
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;
            case '30d':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subMonth(1);
                break;
            case '60d':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subMonth(2);
                break;
            case '90d':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subMonth(3);
                break;

            case '1m':
                $dateStart->setDay(Mage::getStoreConfig('reports/dashboard/mtd_start'));
                break;
            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
            case 'lifetime':
                $startMonthDay = explode(',', Mage::getStoreConfig('reports/dashboard/ytd_start'));
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                if ($range == '2y') {
                    $dateStart->subYear(1);
                } elseif ($range == 'lifetime') {
                    $dateStart->subYear(1000);
                }
                break;
        }

        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');

        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
        }
    }

}