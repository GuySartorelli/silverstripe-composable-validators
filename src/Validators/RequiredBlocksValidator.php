<?php

namespace Signify\ComposableValidators\Validators;

use DNADesign\Elemental\Forms\ElementalAreaField;
use DNADesign\Elemental\Models\ElementalArea;
use NumberFormatter;
use SilverStripe\Config\MergeStrategy\Priority;
use SilverStripe\Forms\Validator;
use SilverStripe\i18n\i18n;
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

    public function __construct(array $required = [])
    {
        $this->required = $this->normaliseRequiredConfig($required);
    }

    public function php($data)
    {
        $elementalAreaFields = ArrayList::create();
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
                    // Make sure an errors array exists
                    if (!isset($errors[$field->Name][$element->ClassName])) {
                        $errors[$field->Name][$element->ClassName] = [];
                    }
                    $relevantFields = $this->getRelevantFields($elementalAreaFields, $requiredConfig);

                    // Validate against minimum and maximum number of blocks.
                    $numberOfBlocks = $this->getNumberOfBlocks($element->ClassName, $relevantFields);
                    $this->validateMinMax(
                        $requiredConfig,
                        $numberOfBlocks,
                        $relevantFields,
                        $errors[$field->Name][$element->ClassName]
                    );

                    // Validate against position.
                    $this->validatePosition(
                        $requiredConfig,
                        $blockPositions[$element->ClassName],
                        $errors[$field->Name][$element->ClassName]
                    );

                    // Remove errors array if empty.
                    if (empty($errors[$field->Name][$element->ClassName])) {
                        unset($errors[$field->Name][$element->ClassName]);
                    }

                    // Don't check this block class again.
                    unset($elementClassesToCheck[$element->ClassName]);
                }
            }

            // Remove errors array if empty.
            if (empty($errors[$field->Name])) {
                unset($errors[$field->Name]);
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

    protected function validateMinMax(array $requiredConfig, int $numberOfBlocks, ArrayList $relevantFields, array &$errors)
    {
        if (isset($requiredConfig['min']) || isset($requiredConfig['max'])) {
            if (isset($requiredConfig['min']) && $numberOfBlocks < $requiredConfig['min']) {
                foreach ($relevantFields as $field)
                {
                    $errors[] = self::TOO_FEW_ERROR;
                }
            }
            if (isset($requiredConfig['max']) && $numberOfBlocks > $requiredConfig['max']) {
                foreach ($relevantFields as $field)
                {
                    $errors[] = self::TOO_MANY_ERROR;
                }
            }
        }
    }

    protected function validatePosition(array $requiredConfig, array $blockPositions, array &$errors)
    {
        if (isset($requiredConfig['pos'])) {
            if (!in_array($requiredConfig['pos'], $blockPositions)) {
                $errors[] = self::POSITION_ERROR;
            }
        }
    }

    protected function setErrorMessages(array $errors)
    {
        foreach ($errors as $fieldName => $blockErrors) {
            $message = _t(
                self::class . '.VALIDATION_ERRORS',
                'The following elemental block validation errors must be resolved:'
            );
            foreach ($blockErrors as $blockClass => $errorTypes) {
                foreach ($errorTypes as $errorType) {
                    $blockSingular = $blockClass::singleton()->singular_name();
                    $blockPlural = $blockClass::singleton()->plural_name();
                    $message .= PHP_EOL;
                    switch ($errorType) {
                        case self::TOO_FEW_ERROR:
                            $min = (int)$this->required[$blockClass]['min'];
                            $message .= _t(
                                self::class . '.TOO_FEW',
                                "Too few '{pluralBlock}', at least {count} is required.|Too few '{pluralBlock}', at least {count} are required.",
                                [
                                    'pluralBlock' => $blockPlural,
                                    'count' => $min,
                                ]
                            );
                            break;
                        case self::TOO_MANY_ERROR:
                            $max = (int)$this->required[$blockClass]['max'];
                            $message .= _t(
                                self::class . '.TOO_MANY',
                                "Too many '{pluralBlock}', only {count} is allowed.|Too many '{pluralBlock}', up to {count} are allowed.",
                                [
                                    'pluralBlock' => $blockPlural,
                                    'count' => $max,
                                ]
                            );
                            break;
                        case self::POSITION_ERROR:
                            $pos = (int)$this->required[$blockClass]['pos'];
                            // If $pos is less than 0, this is counting from the bottom up.
                            if ($pos < 0) {
                                // Determine the block distance from the bottom by getting the absolute value of $pos.
                                $pos *= -1;
                                $ordinal = $this->ordinal($pos);
                                $pos = $pos == 1 ? '.POSITION_ABSOLUTE' : 'POSITION_ORDINAL';
                                $message .= _t(
                                    self::class . $pos,
                                    "If a '{singularBlock}' exists, it must be {ordinal} from the {topOrBottom}",
                                    [
                                        'ordinal' => $ordinal,
                                        'singularBlock' => $blockSingular,
                                        'topOrBottom' => _t(self::class . '.BOTTOM', 'bottom'),
                                    ]
                                );
                            } else {
                                // Positions from the top are 0-indexed. Add 1 to get the true position.
                                $pos++;
                                $ordinal = $this->ordinal($pos);
                                $pos = $pos == 1 ? '.POSITION_ABSOLUTE' : 'POSITION_ORDINAL';
                                $message .= _t(
                                    self::class . $pos,
                                    "If a '{singularBlock}' exists, it must be {ordinal} from the {topOrBottom}",
                                    [
                                        'ordinal' => $ordinal,
                                        'singularBlock' => $blockSingular,
                                        'topOrBottom' => _t(self::class . '.TOP', 'top'),
                                    ]
                                );
                            }
                            break;
                        default:
                            $message .= _t(
                                self::class . '.UNKNOWN_ERROR',
                                "Unknown error for '{singularBlock}'.",
                                ['singularBlock' => $blockSingular]
                            );
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

    protected function getBlockPositions(ElementalArea $area): array
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

    protected function getRelevantFields(ArrayList $elementalAreaFields, array $requiredConfig): ArrayList
    {
        $relevantFields = $elementalAreaFields;
        if (!empty($requiredConfig['areafieldname'])) {
            $relevantFields = $relevantFields->filter([
                'Name' => $requiredConfig['areafieldname'],
            ]);
        }
        return $relevantFields;
    }

    protected function getNumberOfBlocks(string $blockClass, ArrayList $relevantFields): int
    {
        $count = 0;
        foreach ($relevantFields as $field) {
            $count += $field->getArea()->Elements()->filter(['ClassName' => $blockClass])->Count();
        }
        return $count;
    }

    protected function ordinal(int $num): string
    {
        $formatter = new NumberFormatter(i18n::get_locale(), NumberFormatter::ORDINAL);
        return $formatter->format($num);
    }

    protected function normaliseRequiredConfig(array $required): array
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
