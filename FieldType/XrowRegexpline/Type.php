<?php
/*
 * xrowregexpline
 *
 */
namespace xrow\XrowRegexplineBundle\FieldType\XrowRegexpline;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue as FieldValue;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class Type extends FieldType
{
    protected $validatorConfigurationSchema = array(
            'XrowRegexplineValidator' => array(),
    );
    
    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $validatorConfiguration
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
    */
    public function validateValidatorConfiguration($validatorConfiguration)
    {
        $validationErrors = array();
        $validator = new XrowRegexplineValidator();
        foreach ($validatorConfiguration as $validatorIdentifier => $constraints) {
            if ($validatorIdentifier !== 'XrowRegexplineValidator') {
                $validationErrors[] = new ValidationError(
                        "Validator '%validator%' is unknown",
                        null,
                        array(
                                'validator' => $validatorIdentifier,
                        )
                );
                continue;
            }
            $validationErrors += $validator->validateConstraints($constraints);
        }
        return $validationErrors;
    }
    
    /**
     * Validates a field based on the validators in the field definition.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value $fieldValue The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        $errors = array();
        if ($this->isEmptyValue($fieldValue)) {
            return $errors;
        }
        $validatorConfiguration = $fieldDefinition->getValidatorConfiguration();
        $constraints = isset($validatorConfiguration['XrowRegexplineValidator'])?$validatorConfiguration['XrowRegexplineValidator']:array();
        $validator = new XrowRegexplineValidator();
        $validator->initializeWithConstraints($constraints);
        if (!$validator->validate($fieldValue->value)) {
            return $validator->getMessage();
        }
        return array();
    }
    
    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier ()
    {
        return "hmregexpline";
    }
    
    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value $value
     *
     * @return string
     */
    public function getName ( SPIValue $value )
    {
        return (string) $value->value;
    
    }
    
    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value
     */
    public function getEmptyValue ()
    {
        return new Value();
    }
    
    /**
     * Returns if the given $value is considered empty by the field type
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public function isEmptyValue( SPIValue $value )
    {
        return $value->value === "";
    }
    
   /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param array|\xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value $inputValue
     *
     * @return \xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput( $inputValue )
    {
        if ( is_array( $inputValue ) )
        {
            $inputValue = new Value( $inputValue );
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value $value
     *
     * @return void
     */
    protected function checkValueStructure ( BaseValue $value )
    {
        

    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value $value
     *
     * @return string
     */
    protected function getSortInfo( BaseValue $value )
    {
        return $value;
    }
    
    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value $value
     */
    public function fromHash ( $hash )
    {
       if ( $hash === null )
       {
           return $this->getEmptyValue();
       }
       return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value $value
     *
     * @return mixed
     */
    public function toHash ( SPIValue $value )
    {
        if ( $this->isEmptyValue( $value ) )
        {
           return null;
        }
        return $value->value;
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return true;
    }
    
    /**
     * Converts a $value to a persistence value
     *
     * @param \xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( SPIValue $value )
    {
        if ( $value === null )
        {
            return new FieldValue(
                array(
                    "data" => null
                )
            );
        }
        return new FieldValue(
            array(
                "data" => $this->toHash( $value )
            )
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        if ( $fieldValue->data === null )
        {
            return $this->getEmptyValue();
        }
        return new Value( $fieldValue->data );
    }
}