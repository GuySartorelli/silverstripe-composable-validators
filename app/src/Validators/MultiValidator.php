<?php
namespace App\Validators;

use SilverStripe\Forms\Validator;

class MultiValidator extends Validator
{

    /**
     * All applicable validators.
     *
     * @var \SilverStripe\Forms\Validator[]
     */
    private $validators;

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
        return $this;
    }

    /**
     * Validate the form data.
     *
     * This is the "front door" to validation, whereas php() is the "window".
     *
     * {@inheritDoc}
     * @see \SilverStripe\Forms\Validator::validate()
     */
    public function validate()
    {
        $this->resetResult();
        if ($this->getEnabled()) {
            foreach ($this->validators as $validator) {
                $this->result->combineAnd($validator->validate());
            }
        }
        return $this->result;
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



    public function __construct($validators)
    {
        $this->validators = $validators;
    }
}
