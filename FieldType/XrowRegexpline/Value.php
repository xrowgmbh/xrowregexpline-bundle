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
     * Construct a new Value object and initialize with $values
     *
     * @param string[]|string $values
     */
    public function __construct( $value = null )
    {
        $this->value = $value;
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

