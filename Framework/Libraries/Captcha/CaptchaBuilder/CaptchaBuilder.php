<?php

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * This MIT library known as Gregwar/Captcha was adjusted by Iotu Nicolae, nicolae.g.iotu@link133.com
 *  in order to fit CleanDeck requirements.
 */

namespace Framework\Libraries\Captcha\CaptchaBuilder;

/**
 * Builds a new captcha image
 * Uses the fingerprint parameter, if one is passed, to generate the same image
 *
 * @author Gregwar <g.passault@gmail.com>
 * @author Jeremy Livingston <jeremy.j.livingston@gmail.com>
 */
class CaptchaBuilder implements CaptchaBuilderInterface
{
    /**
     * @var int[]
     */
    protected array $fingerprint = [];

    protected bool $useFingerprint = false;

    /**
     * @var int[]
     */
    protected array $textColor = [];

    /**
     * @var ?int[]
     */
    protected ?array $lineColor = null;

    /**
     * @var ?int[]
     */
    protected ?array $backgroundColor = null;

    protected ?\GdImage $contents = null;

    protected ?string $phrase = null;

    protected PhraseBuilder|PhraseBuilderInterface $builder;

    protected bool $distortion = true;

    /**
     * The maximum number of lines to draw in front of
     * the image. null - use default algorithm
     */
    protected ?int $maxFrontLines = null;

    /**
     * The maximum number of lines to draw behind
     * the image. null - use default algorithm
     */
    protected ?int $maxBehindLines = null;

    /**
     * The maximum angle of char
     */
    protected int $maxAngle = 8;

    /**
     * The maximum offset of char
     */
    protected int $maxOffset = 5;

    /**
     * Is the interpolation enabled ?
     */
    protected bool $interpolation = true;

    /**
     * Ignore all effects
     */
    protected bool $ignoreAllEffects = false;

    /**
     * Allowed image types for the background images
     *
     * @var string[]
     */
    protected array $allowedBackgroundImageTypes = ['image/png', 'image/jpeg', 'image/gif'];

    /**
     * The image contents
     *
     * @return \GdImage
     */
    public function getContents(): \GdImage
    {
        return $this->contents;
    }

    /**
     * Enable/Disables the interpolation
     * @param bool $interpolate True to enable, false to disable
     * @return $this
     */
    public function setInterpolation(bool $interpolate = true): static
    {
        $this->interpolation = $interpolate;

        return $this;
    }

    /**
     * Temporary dir, for OCR check
     */
    public string $tempDir = 'temp/';

    public function __construct(string $phrase = null, PhraseBuilderInterface $phraseBuilder = null)
    {
        if ($phraseBuilder instanceof PhraseBuilderInterface) {
            $this->builder = $phraseBuilder;
        } else {
            $this->builder = new PhraseBuilder();
        }

        $this->phrase = is_string($phrase) ? $phrase : $this->builder->build($phrase);
    }

    /**
     * Setting the phrase
     */
    public function setPhrase(string|int $phrase): void
    {
        $this->phrase = (string)$phrase;
    }

    /**
     * Enables/disable distortion
     * @return $this
     */
    public function setDistortion(bool $distortion): static
    {
        $this->distortion = $distortion;

        return $this;
    }

    /**
     * @param int $maxBehindLines
     * @return $this
     */
    public function setMaxBehindLines(int $maxBehindLines): static
    {
        $this->maxBehindLines = $maxBehindLines;

        return $this;
    }

    /**
     * @param int $maxFrontLines
     * @return $this
     */
    public function setMaxFrontLines(int $maxFrontLines): static
    {
        $this->maxFrontLines = $maxFrontLines;

        return $this;
    }

    /**
     * @param int $maxAngle
     * @return $this
     */
    public function setMaxAngle(int $maxAngle): static
    {
        $this->maxAngle = $maxAngle;

        return $this;
    }

    /**
     * Gets the captcha phrase
     */
    public function getPhrase(): ?string
    {
        return $this->phrase;
    }

    /**
     * Returns true if the given phrase is good
     */
    public function testPhrase(string $phrase): bool
    {
        return ($this->builder->niceize($phrase) === $this->builder->niceize($this->getPhrase()));
    }

    /**
     * Instantiation
     */
    public static function create(string $phrase = null): CaptchaBuilder
    {
        return new self($phrase);
    }

    /**
     * Sets the text color to use
     * @param int $r
     * @param int $g
     * @param int $b
     * @return $this
     */
    public function setTextColor(int $r, int $g, int $b): static
    {
        $this->textColor = [$r, $g, $b];

        return $this;
    }

    /**
     * Sets the background color to use
     * @param int $r
     * @param int $g
     * @param int $b
     * @return $this
     */
    public function setBackgroundColor(int $r, int $g, int $b): static
    {
        $this->backgroundColor = [$r, $g, $b];

        return $this;
    }

    /**
     * Sets the background color to use
     * @param int $r
     * @param int $g
     * @param int $b
     * @return $this
     */
    public function setLineColor(int $r, int $g, int $b): static
    {
        $this->lineColor = [$r, $g, $b];

        return $this;
    }

    /**
     * Sets the ignoreAllEffects value
     *
     * @param bool $ignoreAllEffects
     * @return $this
     */
    public function setIgnoreAllEffects(bool $ignoreAllEffects): static
    {
        $this->ignoreAllEffects = $ignoreAllEffects;

        return $this;
    }

    /**
     * Draw lines over the image
     * @param \GdImage $gdImage
     * @param int $width
     * @param int $height
     * @param int|null $tcol
     * @return void
     */
    protected function drawLine(\GdImage $gdImage, int $width, int $height, int $tcol = null): void
    {
        if ($this->lineColor === null) {
            $red = $this->rand(100, 255);
            $green = $this->rand(100, 255);
            $blue = $this->rand(100, 255);
        } else {
            $red = $this->lineColor[0];
            $green = $this->lineColor[1];
            $blue = $this->lineColor[2];
        }

        if ($tcol === null) {
            $tcol = \imagecolorallocate($gdImage, $red, $green, $blue);
        }

        if ($this->rand(0, 1)) { // Horizontal
            $Xa = $this->rand(0, $width / 2);
            $Ya = $this->rand(0, $height);
            $Xb = $this->rand($width / 2, $width);
            $Yb = $this->rand(0, $height);
        } else { // Vertical
            $Xa = $this->rand(0, $width);
            $Ya = $this->rand(0, $height / 2);
            $Xb = $this->rand(0, $width);
            $Yb = $this->rand($height / 2, $height);
        }
        \imagesetthickness($gdImage, $this->rand(1, 3));
        \imageline($gdImage, $Xa, $Ya, $Xb, $Yb, $tcol);
    }

    /**
     * Apply some post effects
     * @param \GdImage $gdImage
     * @return void
     */
    protected function postEffect(\GdImage $gdImage): void
    {
        if (!\function_exists('imagefilter')) {
            return;
        }

        if ($this->backgroundColor != null || $this->textColor != null) {
            return;
        }

        // Negate ?
        if ($this->rand(0, 1) == 0) {
            \imagefilter($gdImage, IMG_FILTER_NEGATE);
        }

        // Edge ?
        if ($this->rand(0, 10) == 0) {
            \imagefilter($gdImage, IMG_FILTER_EDGEDETECT);
        }

        // Contrast
        \imagefilter($gdImage, IMG_FILTER_CONTRAST, $this->rand(-50, 10));

        // Colorize
        if ($this->rand(0, 5) == 0) {
            \imagefilter($gdImage, IMG_FILTER_COLORIZE, $this->rand(-80, 50), $this->rand(-80, 50), $this->rand(-80, 50));
        }
    }

    /**
     * Writes the phrase on the image
     * @param \GdImage $gdImage
     * @param string $phrase
     * @param string $font
     * @param int $width
     * @param int $height
     * @return bool|int
     */
    protected function writePhrase(\GdImage $gdImage, string $phrase, string $font, int $width, int $height): bool|int
    {
        $length = \mb_strlen($phrase);
        if ($length === 0) {
            return \imagecolorallocate($gdImage, 0, 0, 0);
        }

        // Gets the text size and start position
        $size = (int)\round($width / $length) - $this->rand(0, 3) - 1;
        $box = \imagettfbbox($size, 0, $font, $phrase);
        $textWidth = $box[2] - $box[0];
        $textHeight = $box[1] - $box[7];
        $x = (int)\round(($width - $textWidth) / 2);
        $y = (int)\round(($height - $textHeight) / 2) + $size;

        if ($this->textColor === []) {
            $textColor = [$this->rand(0, 150), $this->rand(0, 150), $this->rand(0, 150)];
        } else {
            $textColor = $this->textColor;
        }
        $col = \imagecolorallocate($gdImage, $textColor[0], $textColor[1], $textColor[2]);

        // Write the letters one by one, with random angle
        for ($i = 0; $i < $length; $i++) {
            $symbol = \mb_substr($phrase, $i, 1);
            $box = \imagettfbbox($size, 0, $font, $symbol);
            $w = $box[2] - $box[0];
            $angle = $this->rand(-$this->maxAngle, $this->maxAngle);
            $offset = $this->rand(-$this->maxOffset, $this->maxOffset);
            \imagettftext($gdImage, $size, $angle, $x, $y + $offset, $col, $font, $symbol);
            $x += $w;
        }

        return $col;
    }

    /**
     * Generate the image
     * @param int $width
     * @param int $height
     * @param string|null $font
     * @param int[]|null $fingerprint
     * @return $this
     */
    public function build(int $width = 150, int $height = 40, string $font = null, array $fingerprint = null): static
    {
        if (null !== $fingerprint) {
            $this->fingerprint = $fingerprint;
            $this->useFingerprint = true;
        } else {
            $this->fingerprint = [];
            $this->useFingerprint = false;
        }

        if ($font === null) {
            $font = __DIR__ . '/Font/font' . $this->rand(0, 5) . '.ttf';
        }

        // if background images list is not set, use a color fill as a background
        $image = \imagecreatetruecolor($width, $height);
        if ($this->backgroundColor == null) {
            $bg = \imagecolorallocate($image, $this->rand(200, 255), $this->rand(200, 255), $this->rand(200, 255));
        } else {
            $color = $this->backgroundColor;
            $bg = \imagecolorallocate($image, $color[0], $color[1], $color[2]);
        }
        \imagefill($image, 0, 0, $bg);

        // Apply effects
        if (!$this->ignoreAllEffects) {
            $square = $width * $height;
            $effects = $this->rand($square / 3000, $square / 2000);

            // set the maximum number of lines to draw in front of the text
            if ($this->maxBehindLines != null && $this->maxBehindLines > 0) {
                $effects = \min($this->maxBehindLines, $effects);
            }

            if ($this->maxBehindLines !== 0) {
                for ($e = 0; $e < $effects; $e++) {
                    $this->drawLine($image, $width, $height);
                }
            }
        }

        // Write CAPTCHA text
        $color = $this->writePhrase($image, $this->phrase, $font, $width, $height);

        // Apply effects
        if (!$this->ignoreAllEffects) {
            $square = $width * $height;
            $effects = $this->rand($square / 3000, $square / 2000);

            // set the maximum number of lines to draw in front of the text
            if ($this->maxFrontLines != null && $this->maxFrontLines > 0) {
                $effects = \min($this->maxFrontLines, $effects);
            }

            if ($this->maxFrontLines !== 0) {
                for ($e = 0; $e < $effects; $e++) {
                    $this->drawLine($image, $width, $height, $color);
                }
            }
        }

        // Distort the image
        if ($this->distortion && !$this->ignoreAllEffects) {
            $image = $this->distort($image, $width, $height, $bg);
        }

        // Post effects
        if (!$this->ignoreAllEffects) {
            $this->postEffect($image);
        }

        $this->contents = $image;

        return $this;
    }

    /**
     * Distorts the image
     */
    public function distort(\GdImage $gdImage, int $width, int $height, int $bg): \GdImage|bool
    {
        $contents = \imagecreatetruecolor($width, $height);
        $X = $this->rand(0, $width);
        $Y = $this->rand(0, $height);
        $phase = $this->rand(0, 10);
        $scale = 1.1 + $this->rand(0, 10000) / 30000;
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $Vx = $x - $X;
                $Vy = $y - $Y;
                $Vn = \sqrt($Vx * $Vx + $Vy * $Vy);

                if ($Vn != 0) {
                    $Vn2 = $Vn + 4 * \sin($Vn / 30);
                    $nX = $X + ($Vx * $Vn2 / $Vn);
                    $nY = $Y + ($Vy * $Vn2 / $Vn);
                } else {
                    $nX = $X;
                    $nY = $Y;
                }
                $nY += $scale * \sin($phase + $nX * 0.2);

                if ($this->interpolation) {
                    $p = $this->interpolate(
                        $nX - \floor($nX),
                        $nY - \floor($nY),
                        $this->getCol($gdImage, \floor($nX), \floor($nY), $bg),
                        $this->getCol($gdImage, \ceil($nX), \floor($nY), $bg),
                        $this->getCol($gdImage, \floor($nX), \ceil($nY), $bg),
                        $this->getCol($gdImage, \ceil($nX), \ceil($nY), $bg)
                    );
                } else {
                    $p = $this->getCol($gdImage, \round($nX), \round($nY), $bg);
                }

                if ($p == 0) {
                    $p = $bg;
                }

                \imagesetpixel($contents, $x, $y, $p);
            }
        }

        return $contents;
    }

    /**
     * Saves the Captcha to a jpeg file
     */
    public function save(string $filename, int $quality = 90): bool
    {
        return \imagejpeg($this->contents, $filename, $quality);
    }

    /**
     * Gets the image contents
     */
    public function get(int $quality = 90): bool|string
    {
        \ob_start();
        $this->output($quality);

        return \ob_get_clean();
    }

    /**
     * Gets the HTML inline base64
     * @throws \Exception
     */
    public function inline(int $quality = 90): string
    {
        $inline_data = $this->get($quality);
        if ($inline_data === false) {
            throw new \Exception('Invalid captcha image');
        }
        return 'data:image/jpeg;base64,' . \base64_encode($inline_data);
    }

    /**
     * Outputs the image
     */
    public function output(int $quality = 90): bool
    {
        return \imagejpeg($this->contents, null, $quality);
    }

    /**
     * @return int[]
     */
    public function getFingerprint(): array
    {
        return $this->fingerprint;
    }

    /**
     * Returns a random number or the next number in the
     * fingerprint
     */
    protected function rand(int|float $min, int|float $max): bool|int
    {
        if ($this->useFingerprint) {
            $value = \current($this->fingerprint);
            \next($this->fingerprint);
        } else {
            $value = \mt_rand((int)$min, (int)$max);
            $this->fingerprint[] = $value;
        }

        return $value;
    }

    /**
     * @param float|int $x
     * @param float|int $y
     * @param float|int $nw
     * @param float|int $ne
     * @param float|int $sw
     * @param float|int $se
     * @return int
     */
    protected function interpolate(float|int $x, float|int $y,
                                   float|int $nw, float|int $ne, float|int $sw, float|int $se): int
    {
        [$r0, $g0, $b0] = $this->getRGB($nw);
        [$r1, $g1, $b1] = $this->getRGB($ne);
        [$r2, $g2, $b2] = $this->getRGB($sw);
        [$r3, $g3, $b3] = $this->getRGB($se);

        $cx = 1.0 - $x;
        $cy = 1.0 - $y;

        $m0 = $cx * $r0 + $x * $r1;
        $m1 = $cx * $r2 + $x * $r3;
        $r = (int)($cy * $m0 + $y * $m1);

        $m0 = $cx * $g0 + $x * $g1;
        $m1 = $cx * $g2 + $x * $g3;
        $g = (int)($cy * $m0 + $y * $m1);

        $m0 = $cx * $b0 + $x * $b1;
        $m1 = $cx * $b2 + $x * $b3;
        $b = (int)($cy * $m0 + $y * $m1);

        return ($r << 16) | ($g << 8) | $b;
    }

    /**
     * @param \GdImage $gdImage
     * @param float|int $x
     * @param float|int $y
     * @param int $background
     * @return int
     */
    protected function getCol(\GdImage $gdImage, float|int $x, float|int $y, int $background): int
    {
        $L = \imagesx($gdImage);
        $H = \imagesy($gdImage);
        if ($x < 0 || $x >= $L || $y < 0 || $y >= $H) {
            return $background;
        }

        return \imagecolorat($gdImage, $x, $y);
    }

    /**
     * @param float|int $col
     * @return int[]
     */
    protected function getRGB(float|int $col): array
    {
        return [
            $col >> 16 & 0xff,
            $col >> 8 & 0xff,
            (int)($col) & 0xff,
        ];
    }

    /**
     * Validate the background image path. Return the image type if valid
     *
     * @param string $backgroundImage
     * @return string
     * @throws \Exception
     */
    protected function validateBackgroundImage(string $backgroundImage): string
    {
        // check if file exists
        if (!\file_exists($backgroundImage)) {
            $backgroundImageExploded = \explode('/', $backgroundImage);
            $imageFileName = \count($backgroundImageExploded) > 1 ? $backgroundImageExploded[\count($backgroundImageExploded) - 1] : $backgroundImage;

            throw new \Exception('Invalid background image: ' . $imageFileName);
        }

        // check image type
        $finfo = \finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        $imageType = \finfo_file($finfo, $backgroundImage);
        \finfo_close($finfo);

        if (!\in_array($imageType, $this->allowedBackgroundImageTypes)) {
            throw new \Exception('Invalid background image type! Allowed types are: ' . implode(', ', $this->allowedBackgroundImageTypes));
        }

        return $imageType;
    }

    /**
     * Create background image from type
     *
     * @param string $backgroundImage
     * @param string $imageType
     * @return \GdImage|false
     * @throws \Exception
     */
    protected function createBackgroundImageFromType(string $backgroundImage, string $imageType): \GdImage|bool
    {
        switch ($imageType) {
            case 'image/jpeg':
                $image = \imagecreatefromjpeg($backgroundImage);
                break;
            case 'image/png':
                $image = \imagecreatefrompng($backgroundImage);
                break;
            case 'image/gif':
                $image = \imagecreatefromgif($backgroundImage);
                break;

            default:
                throw new \Exception('Not supported file type for background image!');
        }

        return $image;
    }
}
