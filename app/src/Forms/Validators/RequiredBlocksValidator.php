<?php

namespace App\Forms\Validators;

use DNADesign\Elemental\Controllers\ElementalAreaController;
use DNADesign\Elemental\Forms\ElementalAreaField;
use SilverStripe\Config\MergeStrategy\Priority;
use SilverStripe\Forms\Validator;
use SilverStripe\ORM\ArrayList;

class RequiredBlocksValidator extends Validator
{

    /**
     * List of required blocks and their requirement configuration.
     * @var array
     */
    protected $required;

    const TOO_FEW_ERROR = 'toofew';
    const TOO_MANY_ERROR = 'toomany';

    public function __construct($required = [])
    {
        $this->required = $this->normaliseRequiredConfig($required);
    }

    public function php($data)
    {
        $idPrefixLength = strlen(sprintf(ElementalAreaController::FORM_NAME_TEMPLATE, ''));
        $elementalAreaFields = new ArrayList();
        $elementClassesToCheck = $this->required;
        $errors = [];

        // Validate against all elemental areas, unless configuration indicates a specific area.
        foreach ($this->form->Fields()->dataFields() as $fieldName => $field) {
            if (!$field instanceof ElementalAreaField) {
                continue;
            }
            $elementalAreaFields->add($field);
        }

        foreach ($elementalAreaFields as $field) {
            $fieldName = $field->Name;
            /** @var ElementalArea $area */
            $area = $field->getArea();
            if ($area) {
                // Check submitted form data for each element in the area.
                foreach ($area->Elements() as $element) {
                    if (!array_key_exists($element->ClassName, $elementClassesToCheck)) {
                        continue;
                    }
                    $requiredConfig = $elementClassesToCheck[$element->ClassName];
                    // If we need to check for this block in specific fields but not this one, skip for now.
                    if (!empty($requiredConfig['areafieldname']) && !in_array($fieldName, $requiredConfig['areafieldname'])) {
                        continue;
                    }
                    $relevantFields = $this->getRelevantFields($elementalAreaFields, $requiredConfig);

                    // Validate against minimum and maximum number of blocks.
                    if (isset($requiredConfig['min']) || isset($requiredConfig['max'])) {
                        $numberOfBlocks = $this->getNumberOfBlocks($element->ClassName, $relevantFields);
                        if (isset($requiredConfig['min']) && $numberOfBlocks < $requiredConfig['min']) {
                            foreach ($relevantFields as $field)
                            {
                                $errors[$field->Name][$element->ClassName][] = self::TOO_FEW_ERROR;
                            }
                        }
                        if (isset($requiredConfig['max']) && $numberOfBlocks > $requiredConfig['max']) {
                            foreach ($relevantFields as $field)
                            {
                                $errors[$field->Name][$element->ClassName][] = self::TOO_MANY_ERROR;
                            }
                        }
                    }
                    // Don't check this block class again.
                    unset($elementClassesToCheck[$element->ClassName]);
                }
            }
        }

        // Add an error for each missing elemental block type if the config for the class states a minimum.
        if (!empty($elementClassesToCheck)) {
            foreach ($elementClassesToCheck as $className => $requiredConfig) {
                if (isset($requiredConfig['min'])) {
                    $relevantFields = $this->getRelevantFields($elementalAreaFields, $requiredConfig);
                    foreach ($relevantFields as $field)
                    {
                        $errors[$field->Name][$element->ClassName][] = self::TOO_FEW_ERROR;
                    }
                }
            }
        }

        // Add error messages to fields.
        if (!empty($errors)) {
            foreach ($errors as $fieldName => $blockErrors) {
                $message = 'The following elemental block validation errors must be resolved:';
                foreach ($blockErrors as $blockClass => $errorTypes) {
                    foreach ($errorTypes as $errorType) {
                        $blockSingular = $blockClass::singleton()->singular_name();
                        $blockPlural = $blockClass::singleton()->plural_name();
                        $message .= PHP_EOL;
                        switch ($errorType) {
                            case self::TOO_FEW_ERROR:
                                $min = $this->required[$blockClass]['min'];
                                $isAre = $this->isAre($min);
                                $message .= "Too few '$blockPlural'. At least $min $isAre required.";
                                break;
                            case self::TOO_MANY_ERROR:
                                $max = $this->required[$blockClass]['max'];
                                $isAre = $this->isAre($max);
                                $message .= "Too many '$blockPlural'. Up to $max $isAre allowed.";
                                break;
                            default:
                                $message .= "Unknown error for '$blockSingular'.";
                                break;
                        }
                    }
                }
                $this->validationError(
                    $fieldName,
                    $message,
                    'required'
                );
            }
        }

        return empty($errors);
    }

    protected function getRelevantFields($elementalAreaFields, $requiredConfig)
    {
        $relevantFields = $elementalAreaFields;
        if (!empty($requiredConfig['areafieldname'])) {
            $relevantFields = $relevantFields->filter([
                'Name' => $requiredConfig['areafieldname'],
            ]);
        }
        return $relevantFields;
    }

    protected function getNumberOfBlocks($blockClass, $relevantFields)
    {
        $count = 0;
        foreach ($relevantFields as $field) {
            $count += $field->getArea()->Elements()->filter(['ClassName' => $blockClass])->Count();
        }
        return $count;
    }

    protected function isAre($count)
    {
        return $count == 1 ? 'is' : 'are';
    }

    protected function normaliseRequiredConfig($required)
    {
        $defaultConfig = [
            'min' => 1,
        ];
        $newConfig = [];
        foreach ($required as $blockClass => $config) {
            if (is_numeric($blockClass) && is_string($config)) {
                // If only a block class name is provided, set the default config.
                $newConfig[$config] = $defaultConfig;
                unset($required[$blockClass]);
                continue;
            } elseif (!is_string($blockClass) || !is_array($config)) {
                throw new \InvalidArgumentException('Invalid required blocks array.');
            }
            $config = array_change_key_case($config, CASE_LOWER);
            // Set the default config if none is supplied or only an AreaFieldName is supplied.
            $configKeys = array_keys($config);
            if (empty($config) || (count($configKeys) === 1 && $configKeys[0] === 'areafieldname')) {
                $required[$blockClass] = Priority::mergeArray($config, $defaultConfig);
            }
            // Ensure 'AreaFieldName' config is an array.
            if (isset($config['areafieldname']) && !is_array($config['areafieldname'])) {
                $required[$blockClass]['areafieldname'] = [$config['areafieldname']];
            }
        }

        $allConfig = Priority::mergeArray($required, $newConfig);
        return $allConfig;
    }

}
