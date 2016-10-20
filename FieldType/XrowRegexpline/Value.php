<?php
/**
 * File containing the XrowRegexpline Value class
 */

namespace xrow\XrowRegexplineBundle\FieldType\XrowRegexpline;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for XrowRegexpline field type
 */
class Value extends BaseValue
{
    /**
     * value of the Regexpline
     *
     * @var Mix
     */
    public $value;
    /**
     * Specifying Whether Validation Should Be Performed 
     * 
     * @var boolean
     */
    public $switch;

    /**
     * Construct a new Value object and initialize with $values
     *
     * @param array|string $values
     */
    public function __construct( $value = null )
    {
        if (is_array($value)) {
            foreach ( (array)$value as $key => $item )
            {
                $this->$key = $item;
            }
        } else {
            $this->value = $value;
            $this->switch = false;
        }
    }

    /**
     * Returns a string representation of the keyword value.
     *
     * @return string A comma separated list of tags, eg: "php, eZ Publish, html5"
     */
    public function __toString()
    {
        return (string)$this->value;
    }
}

