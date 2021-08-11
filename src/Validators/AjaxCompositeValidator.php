<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\CompositeValidator;
use SilverStripe\Forms\Validator;
use SilverStripe\View\Requirements;

/**
 * AjaxCompositeValidator can contain between 0 and many different types
 * of {@link SilverStripe\Forms\Validator}s. Each {@link SilverStripe\Forms\Validator} is
 * responsible for validating given form and generating a {@link SilverStripe\ORM\ValidationResult}.
 *
 * Can be triggered with an AJAX request.
 *
 * @package app
 */
class AjaxCompositeValidator extends CompositeValidator
{

    /**
     * Whether ajax validation should be used.
     *
     * @var bool
     */
    private $ajax = true;

    /**
     * Sends the form to each validator
     *
     * @param \SilverStripe\Forms\Form $form
     * @return \App\Validators\AjaxCompositeValidator
     */
    public function setForm($form)
    {
        if ($this->ajax) {
            Requirements::javascript('signify-nz/silverstripe-composable-validators:client/dist/AjaxCompositeValidator.js', ['defer' => true]);
            Requirements::add_i18n_javascript('signify-nz/silverstripe-composable-validators/client/lang');
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
        return parent::setForm($form);
    }

    /**
     * Validate the form data.
     *
     * This is the "front door" to validation, whereas php() is the "window".
     *
     * {@inheritDoc}
     * @see \SilverStripe\Forms\Validator::validate()
     */
    public function validate(bool $isValidAjax = false)
    {
        $this->resetResult();
        // This CompositeValidator has been disabled in full or it is not an expected request
        if (!$this->getEnabled() || !$this->isValidRequest($isValidAjax)) {
            return $this->result;
        }
        // Validate against all validators.
        foreach ($this->getValidators() as $validator) {
            $this->result->combineAnd($validator->validate($isValidAjax));

        }
        if ($isValidAjax) {
            $this->getRequest()->getSession()->clear("FormInfo.{$this->form->FormName()}.result");
        }
        return $this->result;
    }

    /**
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

    public function setAjax(bool $ajax)
    {
        $this->ajax = $ajax;
        return $this;
    }

    public function getAjax(): bool
    {
        return $this->ajax;
    }
}
