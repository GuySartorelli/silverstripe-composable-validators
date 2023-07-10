<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\CompositeValidator;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\Validator;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\View\Requirements;

/**
 * An implementation of CompositeValidator that can contain between 0 and many different types of Validators
 * and trigger their validation via AJAX.
 *
 * @inheritDoc
 */
class AjaxCompositeValidator extends CompositeValidator
{
    /**
     * Whether the validation hint data attribute should be applied to forms.
     *
     * @var boolean
     * @config
     */
    private static $add_validation_hint = true;

    /**
     * Per-instance override for add_validation_hint
     *
     * @var boolean|null
     */
    private $addValidationHint;

    /**
     * Whether ajax validation should be used.
     *
     * @var bool
     */
    private $ajax = true;

    /**
     * Sends the form to each validator
     *
     * @param Form $form
     * @return AjaxCompositeValidator
     */
    public function setForm($form)
    {
        if ($this->ajax) {
            Requirements::javascript(
                'guysartorelli/silverstripe-composable-validators:client/dist/AjaxCompositeValidator.js',
                ['defer' => true]
            );
            Requirements::add_i18n_javascript('guysartorelli/silverstripe-composable-validators/client/lang');
            $action = 'httpSubmission';
            $request = $form->getRequestHandler()->getRequest();
            if ($form->getController() instanceof CMSMain) {
                $id = $request->param('ID') ?: $request->postVar('ID') ?: '';
                if ($id) {
                    $action = "$id/$action";
                }
            }
            $form->addExtraClass('js-multi-validator-ajax');
            $form->setAttribute('data-validation-link', $form->getRequestHandler()->Link($action));
        }
        $oldForm = $this->form;
        parent::setForm($form);
        $this->addValidationHint($oldForm);
        return $this;
    }

    public function validate(bool $isValidAjax = false)
    {
        // Skip if this is not an expected request.
        if (!$this->isValidRequest($isValidAjax)) {
            $this->resetResult();
            return $this->result;
        }
        // Let superclass handle validation of child validators.
        parent::validate();
        // Don't store the validation result in the session for AJAX validation requests.
        if ($isValidAjax) {
            $this->getRequest()->getSession()->clear("FormInfo.{$this->form->FormName()}.result");
        }
        return $this->result;
    }

    /**
     * Add multiple Validators at once.
     *
     * @param Validator[] $validator
     * @return CompositeValidator
     */
    public function addValidators(array $validators): CompositeValidator
    {
        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }
        return $this;
    }

    /**
     * Get a validator if one by that type exists - otherwise, create and add a new one.
     *
     * @param string $validatorClass The class of the validator to get or create.
     * @return Validator The existing or new validator.
     */
    public function getOrAddValidatorByType(string $validatorClass): Validator
    {
        $validators = $this->getValidatorsByType($validatorClass);
        $validator = reset($validators);
        if (!$validator) {
            $validator = $validatorClass::create();
            $this->addValidator($validator);
        }
        return $validator;
    }

    /**
     * Check whether this is a legitimate validation request.
     *
     * @param boolean $validAjax
     * @return boolean
     */
    protected function isValidRequest(bool $validAjax): bool
    {
        $request = $this->getRequest();
        // Not valid if action is validation exempt.
        if (isset($request->requestVars()['_original_action'])) {
            $clickedAction = $request->requestVars()['_original_action'];
            $clickedButton = $this->form->Actions()->dataFieldByName($clickedAction);
            if ($clickedButton && $clickedButton->getValidationExempt()) {
                return false;
            }
        }
        // Not valid if the FormRequestHandler attempts to validate prior to passing to our validation handler.
        return !$request->isAjax() || $validAjax || $request->allParams()['Action'] !== 'httpSubmission';
    }

    /**
     * Get the HTTPRequest used to submit the form or perform validation.
     *
     * @return HTTPRequest|null
     */
    protected function getRequest(): ?HTTPRequest
    {
        return $this->form->getRequestHandler()->getRequest();
    }

    /**
     * Don't validate data.
     *
     * This is not used, but is declared abstract in the parent class. It is
     * normally called from validate() which has been overridden and no longer
     * calls this.
     *
     * @see \SilverStripe\Forms\Validator::php()
     */
    public function php($data)
    {
        return true;
    }

    /**
     * Set whether this validator is configured to add a validation hint to the form.
     *
     * @param boolean $addHint
     * @return $this
     */
    public function setAddValidationHint(bool $addHint)
    {
        $this->addValidationHint = $addHint;
        return $this;
    }

    /**
     * True if this validator is configured to add a validation hint to the form.
     *
     * @return boolean
     */
    public function getAddValidationHint(): bool
    {
        if ($this->addValidationHint === null) {
            return $this->config()->get('add_validation_hint');
        }
        return $this->addValidationHint;
    }

    /**
     * Set whether this validator is configured to use AJAX validation.
     *
     * @param boolean $ajax
     * @return $this
     */
    public function setAjax(bool $ajax)
    {
        $this->ajax = $ajax;
        return $this;
    }

    /**
     * True if this validator is configured to use AJAX validation.
     *
     * @return boolean
     */
    public function getAjax(): bool
    {
        return $this->ajax;
    }

    /**
     * Add a typehint data attribute that indicates what validation is necessary.
     * This is useful to ensure automated tests know what values will be valid for which fields.
     *
     * @param Form|null $oldForm
     */
    private function addValidationHint(?Form $oldForm)
    {
        // Always make sure to remove the attribute in case it has been set.
        $dataAttribute = 'data-signify-validation-hints';
        if ($oldForm) {
            $oldForm->setAttribute($dataAttribute, null);
        }

        // Escape hatch if this validator shouldn't add hints.
        if (!$this->getAddValidationHint()) {
            return;
        }

        // Add the validation hints from all validators.
        $hints = [];
        foreach ($this->getValidators() as $validator) {
            if ($validator->hasMethod('getValidationHints')) {
                $hints = ArrayLib::array_merge_recursive($hints, $validator->getValidationHints());
            }
        }
        $this->form->setAttribute($dataAttribute, json_encode($hints));
    }
}
