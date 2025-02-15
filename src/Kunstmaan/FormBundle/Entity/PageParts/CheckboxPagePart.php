<?php

namespace Kunstmaan\FormBundle\Entity\PageParts;

use ArrayObject;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\FormBundle\Entity\FormSubmissionFieldTypes\BooleanFormSubmissionField;
use Kunstmaan\FormBundle\Form\BooleanFormSubmissionType;
use Kunstmaan\FormBundle\Form\CheckboxPagePartAdminType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * The checkbox page part can be used to create forms with checkbox input fields
 *
 * @ORM\Entity
 * @ORM\Table(name="kuma_checkbox_page_parts")
 */
#[ORM\Entity]
#[ORM\Table(name: 'kuma_checkbox_page_parts')]
class CheckboxPagePart extends AbstractFormPagePart
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
     * Sets the required valud of this page part
     *
     * @param bool $required
     *
     * @return CheckboxPagePart
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
     * @return CheckboxPagePart
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
     * Returns the frontend view
     *
     * @return string
     */
    public function getDefaultView()
    {
        return '@KunstmaanForm/CheckboxPagePart/view.html.twig';
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
        $bfsf = new BooleanFormSubmissionField();
        $bfsf->setFieldName('field_' . $this->getUniqueId());
        $bfsf->setLabel($this->getLabel());
        $bfsf->setSequence($sequence);

        $data = $formBuilder->getData();
        $data['formwidget_' . $this->getUniqueId()] = $bfsf;
        $constraints = [];
        if ($this->getRequired()) {
            $options = [];
            if (!empty($this->errorMessageRequired)) {
                $options['message'] = $this->errorMessageRequired;
            }
            $constraints[] = new NotBlank($options);
        }
        $formBuilder->add(
            'formwidget_' . $this->getUniqueId(),
            BooleanFormSubmissionType::class,
            [
                'label' => $this->getLabel(),
                'value_constraints' => $constraints,
                'required' => $this->getRequired(),
            ]
        );
        $formBuilder->setData($data);

        $fields->append($bfsf);
    }

    /**
     * Returns the default backend form type for this page part
     *
     * @return string
     */
    public function getDefaultAdminType()
    {
        return CheckboxPagePartAdminType::class;
    }
}
