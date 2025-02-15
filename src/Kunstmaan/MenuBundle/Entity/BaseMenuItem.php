<?php

namespace Kunstmaan\MenuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\MappedSuperclass()
 */
#[ORM\MappedSuperclass]
abstract class BaseMenuItem extends AbstractEntity
{
    const TYPE_PAGE_LINK = 'page_link';
    const TYPE_URL_LINK = 'url_link';

    /**
     * @var array
     */
    public static $types = [
        self::TYPE_PAGE_LINK,
        self::TYPE_URL_LINK,
    ];

    /**
     * @var Menu
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\MenuBundle\Entity\Menu", inversedBy="items")
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id")
     * @Assert\NotNull()
     * @Gedmo\TreeRoot(identifierMethod="getMenu")
     */
    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'id')]
    #[Gedmo\TreeRoot(identifierMethod: 'getMenu')]
    protected $menu;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=15, nullable=true)
     * @Assert\NotBlank()
     */
    #[ORM\Column(name: 'type', type: 'string', length: 15, nullable: true)]
    protected $type;

    /**
     * @var NodeTranslation
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\NodeBundle\Entity\NodeTranslation")
     * @ORM\JoinColumn(name="node_translation_id", referencedColumnName="id")
     */
    #[ORM\ManyToOne(targetEntity: NodeTranslation::class)]
    #[ORM\JoinColumn(name: 'node_translation_id', referencedColumnName: 'id')]
    protected $nodeTranslation;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    #[ORM\Column(name: 'title', type: 'string', nullable: true)]
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", nullable=true)
     */
    #[ORM\Column(name: 'url', type: 'string', nullable: true)]
    protected $url;

    /**
     * @var bool
     *
     * @ORM\Column(name="new_window", type="boolean", nullable=true)
     */
    #[ORM\Column(name: 'new_window', type: 'boolean', nullable: true)]
    protected $newWindow;

    /**
     * @var int
     *
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: 'integer')]
    protected $lft;

    /**
     * @var int
     *
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: 'integer')]
    protected $lvl;

    /**
     * @var int
     *
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: 'integer')]
    protected $rgt;

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param Menu $menu
     *
     * @return MenuItem
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return MenuItem
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return NodeTranslation
     */
    public function getNodeTranslation()
    {
        return $this->nodeTranslation;
    }

    /**
     * @param NodeTranslation $nodeTranslation
     *
     * @return MenuItem
     */
    public function setNodeTranslation($nodeTranslation)
    {
        $this->nodeTranslation = $nodeTranslation;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return MenuItem
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return MenuItem
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNewWindow()
    {
        return $this->newWindow;
    }

    /**
     * @param bool $newWindow
     *
     * @return MenuItem
     */
    public function setNewWindow($newWindow)
    {
        $this->newWindow = $newWindow;

        return $this;
    }

    /**
     * @return int
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param int $lft
     *
     * @return MenuItem
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * @return int
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @param int $lvl
     *
     * @return MenuItem
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * @return int
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @param int $rgt
     *
     * @return MenuItem
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayTitle()
    {
        if ($this->getType() == self::TYPE_PAGE_LINK) {
            if (!\is_null($this->getTitle())) {
                return $this->getTitle();
            }

            return $this->getNodeTranslation()->getTitle();
        }

        return $this->getTitle();
    }

    /**
     * @return string
     */
    public function getDisplayUrl()
    {
        if ($this->getType() == self::TYPE_PAGE_LINK) {
            return $this->getNodeTranslation()->getUrl();
        }

        return $this->getUrl();
    }

    /**
     * @return bool
     */
    public function isOnline()
    {
        if ($this->getType() == self::TYPE_URL_LINK) {
            return true;
        }

        if ($this->getNodeTranslation()->isOnline() && !$this->getNodeTranslation()->getNode()->isDeleted()) {
            return true;
        }

        return false;
    }

    /**
     * @Assert\Callback
     */
    public function validateEntity(ExecutionContextInterface $context)
    {
        if ($this->getType() == self::TYPE_PAGE_LINK && !$this->getNodeTranslation()) {
            $context->buildViolation('Please select a page')
              ->atPath('nodeTranslation')
              ->addViolation();
        } elseif ($this->getType() == self::TYPE_URL_LINK) {
            if ($this->getTitle() === '') {
                $context->buildViolation('Please set the link title')
                  ->atPath('title')
                  ->addViolation();
            }
            if ($this->getUrl() === '') {
                $context->buildViolation('Please set the link URL')
                  ->atPath('url')
                  ->addViolation();
            }
        }
    }
}
