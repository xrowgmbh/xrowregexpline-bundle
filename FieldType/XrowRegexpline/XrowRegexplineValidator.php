<?php
/**
 * File containing the XrowRegexplineValidator class.
 */
namespace xrow\XrowRegexplineBundle\FieldType\XrowRegexpline;

use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Validator for checking validity of XrowRegexpline field type.
 *
 * @property array $regexp
 * @property array $error_messages
 * @property array $negates
 * @property array $preset
 * @property string $help_text
 * @property string $naming_pattern
 * @property string $display_type
 */

class XrowRegexplineValidator extends Validator
{
    protected $constraints = array(
        'regexp' => array(),
        'negates' => array(),
        'messages' => array()
    );

    protected $constraintsSchema = array(
        'regexp' => array(
            'type' => 'array',
            'default' => array(),
        ),
        'negates' => array(
                'type' => 'array',
                'default' => array(),
        ),
        'messages' => array(
                'type' => 'array',
                'default' => array(),
        )
    );

    /**
     * @abstract
     *
     * @param mixed $constraints
     *
     * @return mixed
     */
    public function validateConstraints($constraints)
    {
        $validationErrors = array();
        foreach ($constraints as $name => $value) {
            switch ($name) {
                case 'regexp':
                case 'negates':
                case 'messages':
                    if ($value !== false && !is_array( $value )) {
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' value must be an array",
                            null,
                            array(
                                'parameter' => $name,
                            )
                        );
                    }
                    break;
                default:
                    $validationErrors[] = new ValidationError(
                        "Validator parameter '%parameter%' is unknown",
                        null,
                        array(
                            'parameter' => $name,
                        )
                    );
            }
        }
        return $validationErrors;
    }

    /**
     * Perform validation on $value.
     *
     * Will return true when all constraints are matched. If one or more
     * constraints fail, the method will return false.
     *
     * When a check against a constraint has failed, an entry will be added to the
     * $errors array.
     *
     * @abstract
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return bool
     */
    public function validate(BaseValue $value)
    {
        $valid = true;
        
        // If there is no text it is always valid
        if ( $value == '' ) {
            return $valid;
        }
        foreach ( $this->constraints[ 'regexp' ] as $index => $regexp ) {
            $doNegate = isset( $this->constraints[ 'negates' ][ $index ] );
            $result = @preg_match( $regexp, $value );
        
            if ( $doNegate === false ) {
                $failure = ( $result === 0 );
            } else {
                $failure = ( $result === 1 );
            }
        
            if ( $failure ) {
                $index = $index . ( $doNegate ? '_negate' : '' );
                if ( isset( $this->constraints[ 'messages' ][ $index ] ) ) {
                    $this->errors[] = $this->constraints[ 'messages' ][ $index ];
                }
                $valid = false;
            }
        }
        if ( !$valid && count( $this->errors ) == 0 ) {
            $this->errors[] = 'Your input did not meet the requirements.';
        }
        return $valid;
    }
}
