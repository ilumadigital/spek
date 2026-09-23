<?php
/**
 * Product image normalization helpers.
 *
 * Every imported product image is rendered onto a square 1600x1600 white canvas,
 * preserving the complete source image and its aspect ratio. This makes archive
 * cards and single-product galleries consistent without relying on CSS cropping.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('SPEK_PRODUCT_IMAGE_NORMALIZATION_VERSION')) {
    define('SPEK_PRODUCT_IMAGE_NORMALIZATION_VERSION', '1');
}
if (!defined('SPEK_PRODUCT_IMAGE_CANVAS')) {
    define('SPEK_PRODUCT_IMAGE_CANVAS', 1600);
}
if (!defined('SPEK_PRODUCT_IMAGE_PADDING')) {
    define('SPEK_PRODUCT_IMAGE_PADDING', 128);
}
if (!defined('SPEK_PRODUCT_IMAGE_JPEG_QUALITY')) {
    define('SPEK_PRODUCT_IMAGE_JPEG_QUALITY', 90);
}

/**
 * Return the standard normalized filename stored in the Media Library.
 */
function spek_product_image_normalized_filename(string $original_name): string
{
    $stem = sanitize_file_name((string) pathinfo($original_name, PATHINFO_FILENAME));
    if ($stem === '') {
        $stem = 'product-image';
    }

    return $stem . '-spek-1600.jpg';
}

/**
 * Check whether an attachment was produced by the current normalizer.
 */
function spek_product_image_attachment_is_normalized(int $attachment_id): bool
{
    return (string) get_post_meta(
        $attachment_id,
        '_spek_product_image_normalized_version',
        true
    ) === (string) SPEK_PRODUCT_IMAGE_NORMALIZATION_VERSION;
}

/**
 * Mark a Media Library attachment as normalized.
 */
function spek_product_image_mark_normalized_attachment(int $attachment_id): void
{
    update_post_meta(
        $attachment_id,
        '_spek_product_image_normalized_version',
        (string) SPEK_PRODUCT_IMAGE_NORMALIZATION_VERSION
    );
    update_post_meta(
        $attachment_id,
        '_spek_product_image_canvas',
        SPEK_PRODUCT_IMAGE_CANVAS . 'x' . SPEK_PRODUCT_IMAGE_CANVAS
    );
    update_post_meta(
        $attachment_id,
        '_spek_product_image_fit',
        'contain'
    );
}

/**
 * Auto-orient a GD JPEG source when EXIF orientation is available.
 *
 * @param resource|GdImage $image
 * @return resource|GdImage
 */
function spek_product_image_gd_auto_orient($image, string $source_path)
{
    if (
        !function_exists('exif_read_data')
        || !function_exists('imagerotate')
    ) {
        return $image;
    }

    $exif = @exif_read_data($source_path);
    $orientation = isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;

    if ($orientation === 3) {
        $rotated = @imagerotate($image, 180, 0);
    } elseif ($orientation === 6) {
        $rotated = @imagerotate($image, -90, 0);
    } elseif ($orientation === 8) {
        $rotated = @imagerotate($image, 90, 0);
    } else {
        return $image;
    }

    if ($rotated) {
        imagedestroy($image);
        return $rotated;
    }

    return $image;
}

/**
 * Normalize a source image to a 1600x1600 JPEG on white.
 *
 * The complete source remains visible. Nothing is cropped. The image is scaled
 * proportionally into a safe area with 8% padding on every side.
 *
 * @return array|WP_Error {path, name, width, height}
 */
function spek_product_image_normalize_file(
    string $source_path,
    string $original_name = 'product-image.jpg'
) {
    if (!is_file($source_path) || !is_readable($source_path)) {
        return new WP_Error(
            'spek_image_source_missing',
            __('Το αρχείο εικόνας δεν είναι διαθέσιμο για επεξεργασία.', 'spek-theme')
        );
    }

    $info = @getimagesize($source_path);
    if (!$info || empty($info[0]) || empty($info[1])) {
        return new WP_Error(
            'spek_image_invalid',
            __('Το αρχείο δεν αναγνωρίστηκε ως έγκυρη εικόνα.', 'spek-theme')
        );
    }

    $source_width = (int) $info[0];
    $source_height = (int) $info[1];

    // Avoid pathological decompression memory use from extremely large sources.
    if (($source_width * $source_height) > 80000000) {
        return new WP_Error(
            'spek_image_too_large',
            __('Η εικόνα έχει υπερβολικά μεγάλες διαστάσεις για ασφαλή επεξεργασία.', 'spek-theme')
        );
    }

    if (!function_exists('wp_tempnam')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    $tmp = wp_tempnam('spek-product-1600.jpg');
    if (!$tmp) {
        return new WP_Error(
            'spek_image_temp_failed',
            __('Δεν ήταν δυνατή η δημιουργία προσωρινού αρχείου εικόνας.', 'spek-theme')
        );
    }

    $canvas_size = (int) SPEK_PRODUCT_IMAGE_CANVAS;
    $padding = (int) SPEK_PRODUCT_IMAGE_PADDING;
    $inner_size = max(1, $canvas_size - ($padding * 2));

    /*
     * Prefer Imagick where available: it handles large files efficiently and
     * preserves alpha correctly while compositing onto the white canvas.
     */
    if (class_exists('Imagick')) {
        try {
            $source = new Imagick();
            $source->readImage($source_path);

            if ($source->getNumberImages() > 1) {
                $source->setIteratorIndex(0);
            }

            if (method_exists($source, 'autoOrient')) {
                $source->autoOrient();
            } elseif (method_exists($source, 'autoOrientImage')) {
                $source->autoOrientImage();
            }

            $source->setImagePage(0, 0, 0, 0);

            $width = (int) $source->getImageWidth();
            $height = (int) $source->getImageHeight();

            if ($width < 1 || $height < 1) {
                throw new RuntimeException('Invalid image dimensions.');
            }

            $scale = min($inner_size / $width, $inner_size / $height);
            $target_width = max(1, (int) round($width * $scale));
            $target_height = max(1, (int) round($height * $scale));

            $source->resizeImage(
                $target_width,
                $target_height,
                Imagick::FILTER_LANCZOS,
                1,
                true
            );

            $canvas = new Imagick();
            $canvas->newImage(
                $canvas_size,
                $canvas_size,
                new ImagickPixel('white')
            );
            $canvas->setImageFormat('jpeg');

            $x = (int) floor(($canvas_size - $target_width) / 2);
            $y = (int) floor(($canvas_size - $target_height) / 2);

            $canvas->compositeImage(
                $source,
                Imagick::COMPOSITE_OVER,
                $x,
                $y
            );

            $canvas->setImageCompression(Imagick::COMPRESSION_JPEG);
            $canvas->setImageCompressionQuality((int) SPEK_PRODUCT_IMAGE_JPEG_QUALITY);
            $canvas->stripImage();

            if (!$canvas->writeImage($tmp)) {
                throw new RuntimeException('Could not write normalized image.');
            }

            $source->clear();
            $source->destroy();
            $canvas->clear();
            $canvas->destroy();

            return [
                'path' => $tmp,
                'name' => spek_product_image_normalized_filename($original_name),
                'width' => $canvas_size,
                'height' => $canvas_size,
            ];
        } catch (Throwable $e) {
            @unlink($tmp);
            return new WP_Error(
                'spek_image_normalize_failed',
                sprintf(
                    __('Αποτυχία κανονικοποίησης εικόνας: %s', 'spek-theme'),
                    $e->getMessage()
                )
            );
        }
    }

    /*
     * GD fallback for servers without Imagick.
     */
    $mime = strtolower((string) ($info['mime'] ?? ''));
    $source = null;

    if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
        $source = @imagecreatefromjpeg($source_path);
        if ($source) {
            $source = spek_product_image_gd_auto_orient($source, $source_path);
        }
    } elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
        $source = @imagecreatefrompng($source_path);
    } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
        $source = @imagecreatefromwebp($source_path);
    }

    if (!$source || !function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
        @unlink($tmp);
        return new WP_Error(
            'spek_image_editor_unavailable',
            __('Ο server δεν διαθέτει συμβατό image editor (Imagick/GD).', 'spek-theme')
        );
    }

    $width = imagesx($source);
    $height = imagesy($source);

    if ($width < 1 || $height < 1) {
        imagedestroy($source);
        @unlink($tmp);
        return new WP_Error(
            'spek_image_invalid_dimensions',
            __('Η εικόνα έχει μη έγκυρες διαστάσεις.', 'spek-theme')
        );
    }

    $scale = min($inner_size / $width, $inner_size / $height);
    $target_width = max(1, (int) round($width * $scale));
    $target_height = max(1, (int) round($height * $scale));

    $canvas = imagecreatetruecolor($canvas_size, $canvas_size);
    if (!$canvas) {
        imagedestroy($source);
        @unlink($tmp);
        return new WP_Error(
            'spek_image_canvas_failed',
            __('Δεν ήταν δυνατή η δημιουργία καμβά εικόνας.', 'spek-theme')
        );
    }

    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefilledrectangle($canvas, 0, 0, $canvas_size, $canvas_size, $white);
    imagealphablending($canvas, true);

    $x = (int) floor(($canvas_size - $target_width) / 2);
    $y = (int) floor(($canvas_size - $target_height) / 2);

    $copied = imagecopyresampled(
        $canvas,
        $source,
        $x,
        $y,
        0,
        0,
        $target_width,
        $target_height,
        $width,
        $height
    );

    if (!$copied || !imagejpeg($canvas, $tmp, (int) SPEK_PRODUCT_IMAGE_JPEG_QUALITY)) {
        imagedestroy($source);
        imagedestroy($canvas);
        @unlink($tmp);
        return new WP_Error(
            'spek_image_save_failed',
            __('Αποτυχία αποθήκευσης κανονικοποιημένης εικόνας.', 'spek-theme')
        );
    }

    imagedestroy($source);
    imagedestroy($canvas);

    return [
        'path' => $tmp,
        'name' => spek_product_image_normalized_filename($original_name),
        'width' => $canvas_size,
        'height' => $canvas_size,
    ];
}
