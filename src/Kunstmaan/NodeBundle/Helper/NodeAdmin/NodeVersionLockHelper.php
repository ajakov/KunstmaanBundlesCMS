<?php

namespace Kunstmaan\NodeBundle\Helper\NodeAdmin;

use Doctrine\Persistence\ObjectManager;
use Kunstmaan\AdminBundle\Entity\BaseUser;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Entity\NodeVersionLock;
use Kunstmaan\NodeBundle\Repository\NodeVersionLockRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NodeVersionLockHelper implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(ContainerInterface $container, ObjectManager $em)
    {
        $this->setContainer($container);
        $this->setObjectManager($em);
    }

    /**
     * @param ObjectManager $objectManager
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param bool $isPublicNodeVersion
     *
     * @return bool
     */
    public function isNodeVersionLocked(BaseUser $user, NodeTranslation $nodeTranslation, $isPublicNodeVersion)
    {
        if ($this->container->getParameter('kunstmaan_node.lock_enabled')) {
            $this->removeExpiredLocks($nodeTranslation);
            $this->createNodeVersionLock($user, $nodeTranslation, $isPublicNodeVersion); // refresh lock
            $locks = $this->getNodeVersionLocksByNodeTranslation($nodeTranslation, $isPublicNodeVersion, $user);

            return \count($locks) ? true : false;
        }

        return false;
    }

    /**
     * @param BaseUser $userToExclude
     * @param bool     $isPublicNodeVersion
     *
     * @return array
     */
    public function getUsersWithNodeVersionLock(NodeTranslation $nodeTranslation, $isPublicNodeVersion, BaseUser $userToExclude = null)
    {
        return array_reduce(
            $this->getNodeVersionLocksByNodeTranslation($nodeTranslation, $isPublicNodeVersion, $userToExclude),
            function ($return, NodeVersionLock $item) {
                $return[] = $item->getOwner();

                return $return;
            },
            []
        );
    }

    protected function removeExpiredLocks(NodeTranslation $nodeTranslation)
    {
        $threshold = $this->container->getParameter('kunstmaan_node.lock_threshold');
        $locks = $this->objectManager->getRepository(NodeVersionLock::class)->getExpiredLocks($nodeTranslation, $threshold);
        foreach ($locks as $lock) {
            $this->objectManager->remove($lock);
        }
    }

    /**
     * When editing the node, create a new node translation lock.
     *
     * @param bool $isPublicVersion
     */
    protected function createNodeVersionLock(BaseUser $user, NodeTranslation $nodeTranslation, $isPublicVersion)
    {
        $lock = $this->objectManager->getRepository(NodeVersionLock::class)->findOneBy([
            'owner' => method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->getUsername(),
            'nodeTranslation' => $nodeTranslation,
            'publicVersion' => $isPublicVersion,
        ]);
        if (!$lock) {
            $lock = new NodeVersionLock();
        }
        $lock->setOwner(method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->getUsername());
        $lock->setNodeTranslation($nodeTranslation);
        $lock->setPublicVersion($isPublicVersion);
        $lock->setCreatedAt(new \DateTime());

        $this->objectManager->persist($lock);
        $this->objectManager->flush();
    }

    /**
     * When editing a node, check if there is a lock for this node translation.
     *
     * @param bool     $isPublicVersion
     * @param BaseUser $userToExclude
     *
     * @return NodeVersionLock[]
     */
    protected function getNodeVersionLocksByNodeTranslation(NodeTranslation $nodeTranslation, $isPublicVersion, BaseUser $userToExclude = null)
    {
        $threshold = $this->container->getParameter('kunstmaan_node.lock_threshold');
        /** @var NodeVersionLockRepository $objectRepository */
        $objectRepository = $this->objectManager->getRepository(NodeVersionLock::class);

        return $objectRepository->getLocksForNodeTranslation($nodeTranslation, $isPublicVersion, $threshold, $userToExclude);
    }
}
