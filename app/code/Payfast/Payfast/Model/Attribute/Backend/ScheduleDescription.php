<?php
/**
 * Class ScheduleDescription
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Model\Attribute\Backend;

/**
 * Class ScheduleDescription
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class ScheduleDescription extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Method validate
     *
     * @param \Magento\Framework\DataObject $object object
     *
     * @return boolean
     */
    public function validate($object)
    {
        $attribute_code = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attribute_code);

        $parent_attribute_value = $object->getData('is_payfast_recurring');
        if ($parent_attribute_value && trim($value) == '') {
            throw new \Magento\Framework\Exception\LocalizedException(__("Enter a schedule description of the recurring payment."));
        }
        return true;
    }
}
