<?php

namespace Kunstmaan\TranslatorBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\TranslatorBundle\Model\Translation as TranslationModel;
use Kunstmaan\TranslatorBundle\Repository\TranslationRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Kunstmaan\TranslatorBundle\Repository\TranslationRepository")
 * @ORM\Table(
 *     name="kuma_translation",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="keyword_per_locale", columns={"keyword", "locale", "domain"}),
 *         @ORM\UniqueConstraint(name="translation_id_per_locale", columns={"translation_id", "locale"}),
 *     },
 *     indexes={@ORM\Index(name="idx_translation_locale_domain", columns={"locale", "domain"})}
 * )
 * @ORM\HasLifecycleCallbacks
 */
#[ORM\Entity(repositoryClass: TranslationRepository::class)]
#[ORM\Table(name: 'kuma_translation')]
#[ORM\UniqueConstraint(name: 'keyword_per_locale', columns: ['keyword', 'locale', 'domain'])]
#[ORM\UniqueConstraint(name: 'translation_id_per_locale', columns: ['translation_id', 'locale'])]
#[ORM\Index(name: 'idx_translation_locale_domain', columns: ['locale', 'domain'])]
#[ORM\HasLifecycleCallbacks]
class Translation
{
    const FLAG_NEW = 'new';
    const FLAG_UPDATED = 'updated';
    const STATUS_DEPRECATED = 'deprecated';
    const STATUS_DISABLED = 'disabled';
    const STATUS_ENABLED = 'enabled';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue('AUTO')]
    protected $id;

    /**
     * @ORM\Column(type="integer", name="translation_id", nullable=true)
     * @Assert\NotBlank()
     */
    #[ORM\Column(name: 'translation_id', type: 'integer', nullable: true)]
    protected $translationId;

    /**
     * The translations keyword to use in your template or call from the translator
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     */
    #[ORM\Column(name: 'keyword', type: 'string', nullable: true)]
    protected $keyword;

    /**
     * The translations keyword to use in your template or call from the translator
     *
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\NotBlank()
     */
    #[ORM\Column(name: 'locale', type: 'string', length: 5, nullable: true)]
    protected $locale;

    /**
     * @var string
     *
     * @ORM\column(type="string", length=10, options={"default" : "enabled"})
     */
    #[ORM\Column(name: 'status', type: 'string', length: 10, options: ['default' => self::STATUS_ENABLED])]
    protected $status = self::STATUS_ENABLED;

    /**
     * Location where the translation comes from
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    #[ORM\Column(name: 'file', type: 'string', length: 50, nullable: true)]
    protected $file;

    /**
     * Translation
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank()
     */
    #[ORM\Column(name: 'text', type: 'text', nullable: true)]
    protected $text;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=60)
     */
    #[ORM\Column(name: 'domain', type: 'string', length: 60, nullable: true)]
    protected $domain;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created_at", nullable=true)
     */
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    protected $updatedAt;

    /**
     * A flag which defines the status of a specific translations ('updated', 'new', ..)
     *
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    #[ORM\Column(name: 'flag', type: 'string', length: 20, nullable: true)]
    protected $flag;

    /**
     * @ORM\PrePersist
     */
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->flag = self::FLAG_NEW;

        return $this->id;
    }

    /**
     * @ORM\PreUpdate
     */
    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new DateTime();

        if ($this->flag === null) {
            $this->flag = self::FLAG_UPDATED;
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Translation
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param string $keyword
     *
     * @return Translation
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return Translation
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     *
     * @return Translation
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return Translation
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return Translation
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return Translation
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return Translation
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * @param string $flag
     *
     * @return Translation
     */
    public function setFlag($flag)
    {
        $this->flag = $flag;

        return $this;
    }

    /**
     * @param string $translationId
     *
     * @return Translation
     */
    public function setTranslationId($translationId)
    {
        $this->translationId = $translationId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTranslationId()
    {
        return $this->translationId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Translation
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->getStatus() === self::STATUS_DISABLED;
    }

    /**
     * @return bool
     */
    public function isDeprecated()
    {
        return $this->getStatus() === self::STATUS_DEPRECATED;
    }

    /**
     * @param int $id
     *
     * @return TranslationModel
     */
    public function getTranslationModel($id = null)
    {
        $translationModel = new TranslationModel();
        $translationModel->setKeyword($this->getKeyword());
        $translationModel->setDomain($this->getDomain());
        $translationModel->addText($this->getLocale(), $this->getText(), $id);

        return $translationModel;
    }
}
