<?php

namespace Signify\ComposableValidators\Validators;

use DNADesign\Elemental\Forms\ElementalAreaField;
use DNADesign\Elemental\Models\ElementalArea;
use NumberFormatter;
use SilverStripe\Config\MergeStrategy\Priority;
use SilverStripe\Forms\FormField;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\ArrayList;

// This validator needn't exist if the elemental classes don't.
if (class_exists(ElementalAreaField::class) && class_exists(ElementalArea::class)) {

    /**
     * A validator used to define that elemental blocks of specific classes are required.
     * A minimum and/or maximum number of blocks of each class can be set, as well as the
     * positions within the elemental area in which those blocks must sit.
     *
     * This validator is best used within an AjaxCompositeValidator in conjunction with
     * a SimpleFieldsValidator.
     */
    class RequiredBlocksValidator extends BaseValidator
    {
        /**
         * List of required blocks and their requirement configuration.
         * @var array
         */
        protected $required;

        protected const TOO_FEW_ERROR = 'toofew';
        protected const TOO_MANY_ERROR = 'toomany';
        protected const POSITION_ERROR = 'outofposition';

        public function __construct(array $required = [])
        {
            $this->required = $this->normaliseRequiredConfig($required);
        }

        /**
         * Validates that the required blocks exist in the configured positions.
         *
         * @param array $data
         * @return boolean
         */
        public function php($data)
        {
            $elementalAreaFields = $this->getElementalAreaFields();
            $elementClassesToCheck = $this->required;
            $errors = [];

            // Validate elemental areas.
            foreach ($elementalAreaFields as $field) {
                $this->validateElementalArea($field, $elementalAreaFields, $elementClassesToCheck, $errors);
                // Remove errors array if empty.
                if (empty($errors[$field->Name])) {
                    unset($errors[$field->Name]);
                }
            }

            // Validate any missing required blocks.
            $this->validateMissingBlocks($elementalAreaFields, $elementClassesToCheck, $errors);

            // Add error messages to fields.
            if (!empty($errors)) {
                $this->setErrorMessages($errors);
            }
            return empty($errors);
        }

        /**
         * Add an error for each missing elemental block type if the config for the class states a minimum.
         *
         * @param ArrayList $elementalAreaFields
         * @param array $elementClassesToCheck
         * @param array $errors
         */
        protected function validateMissingBlocks(
            ArrayList $elementalAreaFields,
            array $elementClassesToCheck,
            array &$errors
        ) {
            foreach ($elementClassesToCheck as $className => $requiredConfig) {
                if (isset($requiredConfig['min'])) {
                    $relevantFields = $this->getRelevantFields($elementalAreaFields, $requiredConfig);
                    foreach ($relevantFields as $field) {
                        $errors[$field->Name][$className][] = self::TOO_FEW_ERROR;
                        if (isset($requiredConfig['pos'])) {
                            $errors[$field->Name][$className][] = self::POSITION_ERROR;
                        }
                    }
                }
            }
        }

        /**
         * Validate a specific ElementalArea.
         *
         * @param ElementalAreaField $field
         * @param ArrayList $elementalAreaFields
         * @param array $elementClassesToCheck
         * @param array $errors
         */
        protected function validateElementalArea(
            ElementalAreaField $field,
            ArrayList $elementalAreaFields,
            array &$elementClassesToCheck,
            array &$errors
        ) {
            $fieldName = $field->Name;
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
                    if (
                        !empty($requiredConfig['areafieldname'])
                        && !in_array($fieldName, $requiredConfig['areafieldname'])
                    ) {
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
        }

        /**
         * Validate that the minimum or maximum number of blocks for this class has not been exceeded.
         *
         * @param int[] $requiredConfig
         * @param integer $numberOfBlocks
         * @param ArrayList $relevantFields
         * @param string[] $errors
         */
        protected function validateMinMax(
            array $requiredConfig,
            int $numberOfBlocks,
            ArrayList $relevantFields,
            array &$errors
        ) {
            if (isset($requiredConfig['min']) || isset($requiredConfig['max'])) {
                if (isset($requiredConfig['min']) && $numberOfBlocks < $requiredConfig['min']) {
                    foreach ($relevantFields as $field) {
                        $errors[] = self::TOO_FEW_ERROR;
                    }
                }
                if (isset($requiredConfig['max']) && $numberOfBlocks > $requiredConfig['max']) {
                    foreach ($relevantFields as $field) {
                        $errors[] = self::TOO_MANY_ERROR;
                    }
                }
            }
        }

        /**
         * Validate that blocks of this class are in the positions they must be in.
         *
         * @param int[] $requiredConfig
         * @param int[] $blockPositions
         * @param string[] $errors
         */
        protected function validatePosition(array $requiredConfig, array $blockPositions, array &$errors)
        {
            if (isset($requiredConfig['pos'])) {
                if (!in_array($requiredConfig['pos'], $blockPositions)) {
                    $errors[] = self::POSITION_ERROR;
                }
            }
        }

        /**
         * Set error messages against the ElementalArea(s) which did not pass validation checks.
         *
         * @param array $errors
         */
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
                                    "Too few '{pluralBlock}', at least {count} is required.|Too few '{pluralBlock}',"
                                    . ' at least {count} are required.',
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
                                    "Too many '{pluralBlock}', only {count} is allowed.|Too many '{pluralBlock}',"
                                    . ' up to {count} are allowed.',
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
                                    // Determine the block distance from the bottom by getting the absolute value
                                    // of $pos.
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

        /**
         * Get the positions of all elemental blocks within the elemental area.
         * Both the positive 0-indexed position (from top) and negative position (from bottom) are provided.
         *
         * @param ElementalArea $area
         * @return array
         */
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

        /**
         * Get the names of ElementalAreas that need to be validated against.
         *
         * @param ArrayList $elementalAreaFields
         * @param array $requiredConfig
         * @return ArrayList
         */
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

        /**
         * Get the number of blocks of a given class that are held in the relevant ElementalAreas.
         *
         * @param string $blockClass
         * @param ArrayList $relevantFields
         * @return integer
         */
        protected function getNumberOfBlocks(string $blockClass, ArrayList $relevantFields): int
        {
            $count = 0;
            foreach ($relevantFields as $field) {
                $count += $field->getArea()->Elements()->filter(['ClassName' => $blockClass])->Count();
            }
            return $count;
        }

        /**
         * Get the localised ordinal string for the number.
         * e.g. in 'en' locales 1 becomes '1st'
         *
         * @param integer $num
         * @return string
         */
        protected function ordinal(int $num): string
        {
            $formatter = new NumberFormatter(i18n::get_locale(), NumberFormatter::ORDINAL);
            return $formatter->format($num);
        }

        /**
         * Set appropriate defaults and normalise the configuration for this validator.
         *
         * @param array $required
         * @return array
         */
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

        /**
         * Declare that this validator can be cached if there are no fields to validate.
         *
         * @return boolean
         */
        public function canBeCached(): bool
        {
            return count($this->required) === 0;
        }

        /**
         * Get all of the ElementalAreaFields available in this form.
         *
         * @return ArrayList
         */
        private function getElementalAreaFields(): ArrayList
        {
            $elementalAreaFields = ArrayList::create();
            // Get the elemental areas to be validated against.
            foreach ($this->form->Fields()->dataFields() as $fieldName => $field) {
                if (!$field instanceof ElementalAreaField) {
                    continue;
                }
                $elementalAreaFields->add($field);
            }
            return $elementalAreaFields;
        }

        public function getValidationHints(): array
        {
            $elementClassesToCheck = $this->required;
            $elementalAreaFields = $this->getElementalAreaFields();
            $hints = [];
            foreach ($elementClassesToCheck as $className => $requiredConfig) {
                $relevantFields = $this->getRelevantFields($elementalAreaFields, $requiredConfig);
                foreach ($relevantFields as $field) {
                    $singleton = $className::singleton();
                    $fieldArray = [
                        'name' => $singleton->getType(),
                        'min' => isset($requiredConfig['min']) ? $requiredConfig['min'] : -1,
                        'max' => isset($requiredConfig['max']) ? $requiredConfig['max'] : -1,
                        'pos' => isset($requiredConfig['pos']) ? $requiredConfig['pos'] : -1,
                    ];
                    if ($tab = $this->getTabForField($this->getFormField($this->form->Fields(), $field->getName()))) {
                        $fieldArray['tab'] = $tab->ID();
                    }
                    $hints[$field->ID()]['required-elements'][$className] = $fieldArray;
                }
            }
            return $hints;
        }
    }
}
