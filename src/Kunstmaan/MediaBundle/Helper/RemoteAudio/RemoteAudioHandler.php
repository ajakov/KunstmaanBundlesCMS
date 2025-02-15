<?php

namespace Kunstmaan\MediaBundle\Helper\RemoteAudio;

use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\MediaBundle\Form\RemoteAudio\RemoteAudioType;
use Kunstmaan\MediaBundle\Helper\Media\AbstractMediaHandler;

/**
 * RemoteAudioStrategy
 */
class RemoteAudioHandler extends AbstractMediaHandler
{
    /**
     * @var string
     */
    private $soundcloudApiKey;

    /**
     * @var string
     */
    const CONTENT_TYPE = 'remote/audio';

    /**
     * @var string
     */
    const TYPE = 'audio';

    public function __construct($priority, $soundcloudApiKey)
    {
        parent::__construct($priority);
        $this->soundcloudApiKey = $soundcloudApiKey;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Remote Audio Handler';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return RemoteAudioHandler::TYPE;
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return RemoteAudioType::class;
    }

    /**
     * @return mixed
     */
    public function getSoundcloudApiKey()
    {
        return $this->soundcloudApiKey;
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    public function canHandle($object)
    {
        if (
            \is_string($object) ||
            ($object instanceof Media && $object->getContentType() == RemoteAudioHandler::CONTENT_TYPE)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return RemoteAudioHelper
     */
    public function getFormHelper(Media $media)
    {
        return new RemoteAudioHelper($media);
    }

    /**
     * @throws \RuntimeException when the file does not exist
     */
    public function prepareMedia(Media $media)
    {
        if (null === $media->getUuid()) {
            $uuid = uniqid();
            $media->setUuid($uuid);
        }
        $audio = new RemoteAudioHelper($media);
        $code = $audio->getCode();
        // update thumbnail
        switch ($audio->getType()) {
            case 'soundcloud':
                $scData = json_decode(
                    file_get_contents(
                        'http://api.soundcloud.com/tracks/' . $code . '.json?client_id=' . $this->getSoundcloudApiKey()
                    )
                );
                $artworkUrl = $scData->artwork_url;
                $artworkUrl = str_replace('large.jpg', 't500x500.jpg', $artworkUrl);
                $audio->setThumbnailUrl($artworkUrl);

                break;
        }
    }

    public function saveMedia(Media $media)
    {
    }

    public function removeMedia(Media $media)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateMedia(Media $media)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createNew($data)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowTemplate(Media $media)
    {
        return '@KunstmaanMedia/Media/RemoteAudio/show.html.twig';
    }

    /**
     * @param Media  $media    The media entity
     * @param string $basepath The base path
     *
     * @return string
     */
    public function getImageUrl(Media $media, $basepath)
    {
        $helper = new RemoteAudioHelper($media);

        return $helper->getThumbnailUrl();
    }

    /**
     * @return array
     */
    public function getAddFolderActions()
    {
        return [
            RemoteAudioHandler::TYPE => [
                'type' => RemoteAudioHandler::TYPE,
                'name' => 'media.audio.add',
            ],
        ];
    }
}
