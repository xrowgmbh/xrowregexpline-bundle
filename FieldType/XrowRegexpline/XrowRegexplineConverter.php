<?php
/**
 * File containing the LegacyConverter class.
 */
namespace xrow\XrowRegexplineBundle\FieldType\XrowRegexpline;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

class XrowRegexplineConverter implements Converter
{
    const VALIDATOR_IDENTIFIER = 'XrowRegexplineValidator';
    
    /**
     * @var ConfigResolverInterface
     */
    protected $configResolver;
    
    public function __construct( ConfigResolverInterface $configResolver )
    {
        
        $this->configResolver = $configResolver;
    }
    
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @return xrow\xrowregexpline-bundle\FieldType\XrowRegexpline\Value
     */
    public static function create()
    {
        return new self;
    }

    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataText = $value->data;
    }
    
    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = $value->dataText;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        $fieldSettings = $fieldDef->fieldTypeConstraints->fieldSettings;
        $storageDef->dataText5 = serialize( $this->object_to_array($fieldSettings));
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $definition = unserialize( $storageDef->dataText5 );
        if( !is_array( $definition ) ) {
            $definition = array(
                    'regexp' => array(),
                    'error_messages' => array(),
                    'negates' => array(),
                    'preset' => array(),
                    'help_text' => '',
                    'subpattern_count' => 0,  // Used in object naming
                    'subpatterns' => array(), // Used in object naming
                    'naming_pattern' => '',   // Used in object naming
                    'display_type' => 'line',
                    'class_validation_messages' => array()
            );
        }
        if( isset( $definition[ 'pattern_selection' ] ) ) {
            $definition = $this->migratePatternSelection( $definition );
        }
        
        if( !is_array( $definition[ 'regexp' ] ) ) {
            $definition[ 'regexp' ] = array( $definition[ 'regexp' ] );
        }
        
        if( !is_array( $definition[ 'preset' ] ) ) {
            $tmpPreset = array();
            if( !empty( $definition[ 'preset' ] ) ) {
                $tmpPreset[] = $definition[ 'preset' ];
            }
            $definition[ 'preset' ] = $tmpPreset;
        }
        
        if( !isset( $definition[ 'display_type' ] ) ) {
            $definition[ 'display_type' ] = 'line';
        }
        
        if( !isset( $definition[ 'error_messages' ] ) ) {
            $definition[ 'error_messages' ] = array();
        }
        
        if( !isset( $definition[ 'negates' ] ) ) {
            $definition[ 'negates' ] = array();
        }
        
        $regexp = $definition[ 'regexp' ];
        
        $messages = array();
        foreach ( $definition [ 'error_messages' ] as $index => $message ) {
            if ( trim( $message ) != '' ) {
                $messages[ $index ] = $message;
            }
        }
        
        if ( count( $definition[ 'preset' ] ) > 0 ) {
            $presetRegexps = $this->configResolver->getParameter( 'GeneralSettings.RegularExpressions', 'xrowregexpline' );
            $presetMessages = $this->configResolver->getParameter( 'GeneralSettings.ErrorMessages', 'xrowregexpline' );
        
            $regexp = array();
            foreach( $definition[ 'preset' ] as $id ) {
                if ( isset( $presetRegexps[ $id ] ) ) {
                    $regexp[ $id ] = $presetRegexps[ $id ];
                }
                if ( isset( $presetMessages[ $id ] ) ) {
                    $messages[ $id ] = $presetMessages[ $id ];
                }
                if ( isset( $presetMessages[ $id . '_negate' ] ) ) {
                    $messages[ $id . '_negate' ] = $presetMessages[ $id . '_negate' ];
                }
            }
        }
        
        $validatorConstraints = array( self::VALIDATOR_IDENTIFIER => array(
                'regexp' => $regexp,
                'negates' => $definition[ 'negates' ],
                'messages' => $messages
        )
        );
        $fieldDef->fieldTypeConstraints->validators = $validatorConstraints;
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings( $definition );
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * If the indexing is not supported, this method must return false.
     *
     * @return string|false
     */
    public function getIndexColumn()
    {
        return false;
    }
    
    protected function migratePatternSelection( $definition )
    {
        // Migrate the old pattern_selection to the newer naming_pattern
        $definition[ 'naming_pattern' ] = '';
    
        foreach( $definition[ 'pattern_selection' ] as $pattern ) {
            $definition[ 'naming_pattern' ] .= "<$pattern>";
        }
        unset( $definition[ 'pattern_selection' ] );
        return $definition;
    }
    
    public function object_to_array($obj)
    {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val)
        {
            if (is_object($val)) {
                $val = object_to_array($val);
            }
            $arr[$key] = $val;
        }
        return $arr;
    }
}