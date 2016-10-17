<?php
/**
 * File containing the XrowRegexpline Value class
 */

namespace xrow\XrowRegexplineBundle\FieldType\XrowRegexpline;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for EzauthorRelation field type
 */
class Value extends BaseValue
{
    public $value;

    public function __construct( $value = null )
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return (string)$this->value;
    }
}

