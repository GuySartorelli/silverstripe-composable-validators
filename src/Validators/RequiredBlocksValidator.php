<?php

namespace Signify\ComposableValidators\Validators;

use DNADesign\Elemental\Forms\ElementalAreaField;
use DNADesign\Elemental\Models\ElementalArea;
use SilverStripe\Config\MergeStrategy\Priority;
use SilverStripe\Forms\Validator;
use SilverStripe\ORM\ArrayList;

// This validator needn't exist if the elemental classes don't.
if (!class_exists(ElementalAreaField::class) || !class_exists(ElementalArea::class)) {
    return;
}

class RequiredBlocksValidator extends Validator
{

    /**
     * List of required blocks and their requirement configuration.
     * @var array
     */
    protected $required;

    const TOO_FEW_ERROR = 'toofew';
    const TOO_MANY_ERROR = 'toomany';
    const POSITION_ERROR = 'outofposition';

    public function __construct($required = [])
    {
        $this->required = $this->normaliseRequiredConfig($required);
    }

    public function php($data)
    {
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
                // Get block positioning
                $blockPositions = $this->getBlockPositions($area);

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

                    // Validate against position.
                    if (isset($requiredConfig['pos'])) {
                        if (!in_array($requiredConfig['pos'], $blockPositions[$element->ClassName])) {
                            $errors[$field->Name][$element->ClassName][] = self::POSITION_ERROR;
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
                        $errors[$field->Name][$className][] = self::TOO_FEW_ERROR;
                        if (isset($requiredConfig['pos'])) {
                            $errors[$field->Name][$className][] = self::POSITION_ERROR;
                        }
                    }
                }
            }
        }

        // Add error messages to fields.
        if (!empty($errors)) {
            $this->setErrorMessages($errors);
        }

        return empty($errors);
    }

    protected function setErrorMessages($errors)
    {
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
                        case self::POSITION_ERROR:
                            $pos = $this->required[$blockClass]['pos'];
                            if ($pos < 0) {
                                $pos *= -1;
                                $ordinal = $this->ordinal($pos);
                                $pos = $pos == 1 ? 'at the bottom' : "$ordinal from the bottom";
                            } else {
                                $pos++;
                                $ordinal = $this->ordinal($pos);
                                $pos = $pos == 1 ? 'at the top' : "$ordinal from the top";
                            }
                            $message .= "If a '$blockSingular' exists, it must be $pos.";
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

    protected function getBlockPositions(ElementalArea $area)
    {
        $total = $area->Elements()->Count();
        $positions = [];
        $pos = 0;
        foreach ($area->Elements() as $element) {
            if (!isset($positions[$element->ClassName])) {
                $positions[$element->ClassName] = [];
            }
            // Get the positive (from top) and negative (from bottom) positions.
            $positions[$element->ClassName][] = $pos;
            $positions[$element->ClassName][] = $pos - $total;
            $pos++;
        }
        return $positions;
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

    protected function ordinal($num)
    {
        $ones = $num % 10;
        $tens = floor($num / 10) % 10;
        if ($tens == 1) {
            $suff = "th";
        } else {
            switch ($ones) {
                case 1 : $suff = "st"; break;
                case 2 : $suff = "nd"; break;
                case 3 : $suff = "rd"; break;
                default : $suff = "th";
            }
        }
        return $num . $suff;
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

    public function canBeCached(): bool
    {
        return count($this->required) === 0;
    }
}
