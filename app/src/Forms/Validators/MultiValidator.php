<?php
namespace App\Validators;

use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Forms\Validator;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\View\Requirements;

class MultiValidator extends Validator
{

    /**
     * All applicable validators.
     *
     * @var \SilverStripe\Forms\Validator[]
     */
    private $validators;

    /**
     * Whether ajax validation should be used.
     *
     * @var bool
     */
    private $ajax;

    public function __construct(array $validators = [], $ajax = true)
    {
        Requirements::javascript('app/client/dist/MultiValidator.js');
        $this->validators = $validators;
        $this->ajax = $ajax;
    }

    public function getValidators()
    {
        return $this->validators;
    }

    public function addValidator(Validator $validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * Sends the form to each validator
     *
     * @param \SilverStripe\Forms\Form $form
     * @return \App\Validators\MultiValidator
     */
    public function setForm($form)
    {
        foreach ($this->validators as $validator) {
            $validator->setForm($form);
        }
        if ($this->ajax) {
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
    public function validate($isValidAjax = false)
    {
        // The FormRequestHandler will attempt to validate prior to passing to our validation handler.
        // If that happens, pretend the form passed validation.
        if (!$this->isValidRequest($isValidAjax)) {
            return new ValidationResult();
        }
        // Validate against all validators.
        $this->resetResult();
        if ($this->getEnabled()) {
            foreach ($this->validators as $validator) {
                $this->result->combineAnd($validator->validate());
            }
        }
        return $this->result;
    }

    protected function isValidRequest($validAjax)
    {
        $request = $this->form->getRequestHandler()->getRequest();
        return !$request->isAjax() || $validAjax || $request->allParams()['Action'] !== 'httpSubmission';
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
        // Do nothing
    }
}
