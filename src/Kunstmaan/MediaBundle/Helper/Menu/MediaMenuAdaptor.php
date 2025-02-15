<?php

namespace Kunstmaan\MediaBundle\Helper\Menu;

use Kunstmaan\AdminBundle\Helper\Menu\MenuAdaptorInterface;
use Kunstmaan\AdminBundle\Helper\Menu\MenuBuilder;
use Kunstmaan\AdminBundle\Helper\Menu\MenuItem;
use Kunstmaan\AdminBundle\Helper\Menu\TopMenuItem;
use Kunstmaan\MediaBundle\Entity\Folder;
use Kunstmaan\MediaBundle\Repository\FolderRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * The Media Menu Adaptor
 */
class MediaMenuAdaptor implements MenuAdaptorInterface
{
    /**
     * @var FolderRepository
     */
    private $repo;

    /**
     * @param FolderRepository $repo
     */
    public function __construct($repo)
    {
        $this->repo = $repo;
    }

    public function adaptChildren(MenuBuilder $menu, array &$children, MenuItem $parent = null, Request $request = null)
    {
        if (\is_null($parent)) {
            // Add menu item for root gallery
            $rootFolders = $this->repo->getRootNodes();
            $currentId = $request->attributes->get('folderId');
            $currentFolder = null;
            if (isset($currentId)) {
                /* @var Folder $currentFolder */
                $currentFolder = $this->repo->find($currentId);
            }

            /** @var Folder $rootFolder */
            foreach ($rootFolders as $rootFolder) {
                $menuItem = new TopMenuItem($menu);
                $menuItem
                    ->setRoute('KunstmaanMediaBundle_folder_show')
                    ->setRouteparams(['folderId' => $rootFolder->getId()])
                    ->setUniqueId('folder-' . $rootFolder->getId())
                    ->setLabel($rootFolder->getName())
                    ->setParent(null)
                    ->setRole($rootFolder->getRel());

                if (!\is_null($currentFolder)) {
                    $parentIds = $this->repo->getParentIds($currentFolder);
                    if (\in_array($rootFolder->getId(), $parentIds)) {
                        $menuItem->setActive(true);
                    }
                }
                $children[] = $menuItem;
            }
        }
    }
}
