<?php

namespace Jens\ImageConverter;

class ImageConverter
{
    /** @var array */
    private $imageFormat = [
        'gif',
        'jpeg',
        'jpg',
        'png',
        'webp',
    ];

    /** @var array */
    private $constImageFormat = [
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_JPEG => 'jpeg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];

    /**
     * Do image conversion work
     *
     * @param string $from
     * @param string $to
     *
     * @return resource
     * @throws \InvalidArgumentException
     */
    public function convert($from, $to, $quality = -1)
    {
        $image = $this->loadImage($from);
        if (!$image) {
            throw new \InvalidArgumentException(sprintf('Cannot load image from %s', $from));
        }

        return $this->saveImage($to, $image, $quality);
    }

    private function loadImage($from)
    {
        $extension = $this->getRealExtension($from);

        if (!array_key_exists($extension, $this->constImageFormat)) {
            throw new \InvalidArgumentException(sprintf('The %s extension is unsupported', $extension));
        }

        $method = 'imagecreatefrom' . $this->constImageFormat[$extension];

        return $method($from);
    }

    private function saveImage($to, $image, $quality)
    {
        $extension = $this->getExtension($to);

        if ($extension === 'jpg') {
            $extension = 'jpeg';
        }

        if (!in_array($extension, $this->imageFormat)) {
            throw new \InvalidArgumentException(sprintf('The %s extension is unsupported', $extension));
        }
        if (!file_exists(dirname($to))) {
            $this->makeDirectory($to);
        }

        $method = 'image' . $extension;

        return $method($image, $to, $quality);
    }

    /**
     * Given specific $path to detect current image extension
     */
    private function getRealExtension($path)
    {
        $extension = exif_imagetype($path);

        if (!array_key_exists($extension, $this->constImageFormat)) {
            throw new \InvalidArgumentException(sprintf('Cannot detect %s extension', $path));
        }

        return $extension;
    }

    /**
     * Get image extension from specific $path
     *
     * @param string $path
     *
     * @return string
     */
    private function getExtension($path)
    {
        $pathInfo = pathinfo($path);

        if (!array_key_exists('extension', $pathInfo)) {
            throw new \InvalidArgumentException(sprintf('Cannot find extension from %s', $path));
        }

        return $pathInfo['extension'];
    }

    /**
     * Try creating the directory
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function makeDirectory($to)
    {
        $result = @mkdir(dirname($to), 0755);

        if (!$result) {
            throw new \InvalidArgumentException(\sprintf('Cannot create %s directory', $to));
        }

        return $result;
    }
}
