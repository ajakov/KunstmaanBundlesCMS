<?php

namespace Kunstmaan\FormBundle\Entity\PageParts;

use ArrayObject;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\FormBundle\Entity\FormSubmissionFieldTypes\EmailFormSubmissionField;
use Kunstmaan\FormBundle\Form\EmailFormSubmissionType;
use Kunstmaan\FormBundle\Form\EmailPagePartAdminType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * The email page part can be used to create forms with email input fields
 *
 * @ORM\Entity
 * @ORM\Table(name="kuma_email_page_parts")
 */
#[ORM\Entity]
#[ORM\Table(name: 'kuma_email_page_parts')]
class EmailPagePart extends AbstractFormPagePart
{
    /**
     * If set to true, you are obligated to fill in this page part
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    #[ORM\Column(name: 'required', type: 'boolean', nullable: true)]
    protected $required = false;

    /**
     * Error message shows when the page part is required and nothing is filled in
     *
     * @ORM\Column(type="string", name="error_message_required", nullable=true)
     * @Length(max=255)
     */
    #[ORM\Column(name: 'error_message_required', type: 'string', nullable: true)]
    protected $errorMessageRequired;

    /**
     * Error message shows when the value is invalid
     *
     * @ORM\Column(type="string", name="error_message_invalid", nullable=true)
     * @Length(max=255)
     */
    #[ORM\Column(name: 'error_message_invalid', type: 'string', nullable: true)]
    protected $errorMessageInvalid;

    /**
     * Sets the required value of this page part
     *
     * @param bool $required
     *
     * @return EmailPagePart
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Check if the page part is required
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Sets the message shown when the page part is required and no value was entered
     *
     * @param string $errorMessageRequired
     *
     * @return EmailPagePart
     */
    public function setErrorMessageRequired($errorMessageRequired)
    {
        $this->errorMessageRequired = $errorMessageRequired;

        return $this;
    }

    /**
     * Get the error message that will be shown when the page part is required and no value was entered
     *
     * @return string
     */
    public function getErrorMessageRequired()
    {
        return $this->errorMessageRequired;
    }

    /**
     * Sets the message shown when the value is invalid
     *
     * @param string $errorMessageInvalid
     *
     * @return EmailPagePart
     */
    public function setErrorMessageInvalid($errorMessageInvalid)
    {
        $this->errorMessageInvalid = $errorMessageInvalid;

        return $this;
    }

    /**
     * Get the error message that will be shown when the value is invalid
     *
     * @return string
     */
    public function getErrorMessageInvalid()
    {
        return $this->errorMessageInvalid;
    }

    /**
     * Returns the frontend view
     *
     * @return string
     */
    public function getDefaultView()
    {
        return '@KunstmaanForm/EmailPagePart/view.html.twig';
    }

    /**
     * Modify the form with the fields of the current page part
     *
     * @param FormBuilderInterface $formBuilder The form builder
     * @param ArrayObject          $fields      The fields
     * @param int                  $sequence    The sequence of the form field
     */
    public function adaptForm(FormBuilderInterface $formBuilder, ArrayObject $fields, $sequence)
    {
        $efsf = new EmailFormSubmissionField();
        $efsf->setFieldName('field_' . $this->getUniqueId());
        $efsf->setLabel($this->getLabel());
        $efsf->setSequence($sequence);

        $data = $formBuilder->getData();
        $data['formwidget_' . $this->getUniqueId()] = $efsf;

        $constraints = [];
        if ($this->getRequired()) {
            $options = [];
            if (!empty($this->errorMessageRequired)) {
                $options['message'] = $this->errorMessageRequired;
            }
            $constraints[] = new NotBlank($options);
        }
        $options = [];
        if (!empty($this->errorMessageInvalid)) {
            $options['message'] = $this->getErrorMessageInvalid();
        }
        $constraints[] = new Email($options);

        $formBuilder->add(
            'formwidget_' . $this->getUniqueId(),
            EmailFormSubmissionType::class,
            [
                'label' => $this->getLabel(),
                'value_constraints' => $constraints,
                'required' => $this->getRequired(),
            ]
        );
        $formBuilder->setData($data);

        $fields->append($efsf);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAdminType()
    {
        return EmailPagePartAdminType::class;
    }
}
