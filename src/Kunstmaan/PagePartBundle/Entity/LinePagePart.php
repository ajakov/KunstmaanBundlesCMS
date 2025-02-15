<?php

namespace Kunstmaan\PagePartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\PagePartBundle\Form\LinePagePartAdminType;

/**
 * @ORM\Entity
 * @ORM\Table(name="kuma_line_page_parts")
 */
#[ORM\Entity]
#[ORM\Table(name: 'kuma_line_page_parts')]
class LinePagePart extends AbstractPagePart
{
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'LinePagePart';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultView()
    {
        return '@KunstmaanPagePart/LinePagePart/view.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAdminType()
    {
        return LinePagePartAdminType::class;
    }
}
