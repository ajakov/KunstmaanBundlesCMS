<?php

namespace Kunstmaan\MediaBundle\Tests\Helper;

use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\MediaBundle\Helper\MediaManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaManagerTest extends TestCase
{
    /**
     * @var MediaManager
     */
    protected $object;

    private $defaultHandler;

    protected function setUp(): void
    {
        $this->defaultHandler = $this->getMockForAbstractClass('Kunstmaan\MediaBundle\Helper\Media\AbstractMediaHandler', [0]);
        $this->defaultHandler
            ->expects($this->any())
            ->method('canHandle')
            ->willReturn(true);
        $this->defaultHandler
            ->expects($this->any())
            ->method('getName')
            ->willReturn('DefaultHandler');
        $this->defaultHandler
            ->expects($this->any())
            ->method('getType')
            ->willReturn('any/type');
        $this->object = new MediaManager();
        $this->object->setDefaultHandler($this->defaultHandler);
    }

    public function testAddHandler()
    {
        $media = new Media();
        $handler = $this->getCustomHandler($media);
        $this->object->addHandler($handler);
        $this->assertEquals($handler, $this->object->getHandler($media));
    }

    public function testGetHandlerForType()
    {
        $handler = $this->getCustomHandler();
        $this->object->addHandler($handler);
        $this->assertEquals($handler, $this->object->getHandlerForType('custom/type'));
        $this->assertEquals($this->defaultHandler, $this->object->getHandlerForType('unknown/type'));
    }

    public function testGetHandlers()
    {
        $handler = $this->getCustomHandler();
        $this->object->addHandler($handler);
        $handlers = $this->object->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertEquals($handler, current($handlers));
    }

    public function testPrepareMediaWithDefaultHandler()
    {
        $media = new Media();
        $this->defaultHandler
            ->expects($this->once())
            ->method('prepareMedia')
            ->with($this->equalTo($media));
        $this->object->prepareMedia($media);
    }

    public function testPrepareMediaWithCustomHandler()
    {
        $media = new Media();
        $handler = $this->getCustomHandler($media);
        $handler
            ->expects($this->once())
            ->method('prepareMedia')
            ->with($this->equalTo($media));
        $this->object->addHandler($handler);
        $this->object->prepareMedia($media);
    }

    public function testSaveMediaWithDefaultHandler()
    {
        $media = new Media();
        $this->defaultHandler
            ->expects($this->once())
            ->method('saveMedia')
            ->with($this->equalTo($media));
        $this->object->saveMedia($media, true);

        $this->defaultHandler
            ->expects($this->once())
            ->method('updateMedia')
            ->with($this->equalTo($media));
        $this->object->saveMedia($media);
    }

    public function testCreateMediaWithCustomHandler()
    {
        $media = new Media();
        $handler = $this->getCustomHandler($media);
        $handler
            ->expects($this->once())
            ->method('saveMedia')
            ->with($this->equalTo($media));
        $this->object->addHandler($handler);
        $this->object->saveMedia($media, true);
    }

    public function testUpdateMediaWithCustomHandler()
    {
        $media = new Media();
        $handler = $this->getCustomHandler($media);
        $handler
            ->expects($this->once())
            ->method('updateMedia')
            ->with($this->equalTo($media));
        $this->object->addHandler($handler);
        $this->object->saveMedia($media);
    }

    public function testRemoveMediaWithDefaultHandler()
    {
        $media = new Media();
        $this->defaultHandler
            ->expects($this->once())
            ->method('removeMedia')
            ->with($this->equalTo($media));
        $this->object->removeMedia($media);
    }

    public function testRemoveMediaWithCustomHandler()
    {
        $media = new Media();
        $handler = $this->getCustomHandler($media);
        $handler
            ->expects($this->once())
            ->method('removeMedia')
            ->with($this->equalTo($media));
        $this->object->addHandler($handler);
        $this->object->removeMedia($media);
    }

    public function testGetHandlerWithDefaultHandler()
    {
        $media = new Media();
        $this->assertEquals($this->defaultHandler, $this->object->getHandler($media));
    }

    public function testGetHandlerWithCustomHandler()
    {
        $media = new Media();
        $handler = $this->getCustomHandler($media);
        $this->object->addHandler($handler);
        $this->assertEquals($handler, $this->object->getHandler($media));
    }

    public function testCreateNew()
    {
        $media = new Media();
        $data = new \stdClass();
        $this->assertNull($this->object->createNew($data));

        $handler1 = $this->getCustomHandler(null, 'CustomHandler1');
        $handler1
            ->expects($this->once())
            ->method('createNew')
            ->with($this->equalTo($data))
            ->willReturn(false);
        $this->object->addHandler($handler1);

        $handler2 = $this->getCustomHandler(null, 'CustomHandler2');
        $handler2
            ->expects($this->once())
            ->method('createNew')
            ->with($this->equalTo($data))
            ->willReturn($media);
        $this->object->addHandler($handler2);

        $this->assertEquals($media, $this->object->createNew($data));
    }

    public function testGetFolderAddActions()
    {
        $actions = [];
        $this->assertEquals($actions, $this->object->getFolderAddActions());

        $actions = ['action1', 'action2'];
        $handler = $this->getCustomHandler();
        $handler
            ->expects($this->once())
            ->method('getAddFolderActions')
            ->willReturn($actions);
        $this->object->addHandler($handler);
        $this->assertEquals($actions, $this->object->getFolderAddActions());
    }

    /**
     * @param object $media
     * @param string $name
     *
     * @return MockObject
     */
    protected function getCustomHandler($media = null, $name = null)
    {
        $handler = $this->getMockForAbstractClass('Kunstmaan\MediaBundle\Helper\Media\AbstractMediaHandler', [1]);
        if (empty($name)) {
            $name = 'CustomHandler';
        }
        $handler
            ->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        $handler
            ->expects($this->any())
            ->method('getType')
            ->willReturn('custom/type');
        if (!\is_null($media)) {
            $handler
                ->expects($this->any())
                ->method('canHandle')
                ->with($this->equalTo($media))
                ->willReturn(true);
        }

        return $handler;
    }
}
