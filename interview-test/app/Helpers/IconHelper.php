<?php

namespace App\Helpers;

class IconHelper
{
    /**
     * Get SVG icon from file
     */
    private static function getSvgFile($name, $size = 16)
    {
        $path = public_path('images/icons/' . $name . '.svg');
        if (file_exists($path)) {
            $svg = file_get_contents($path);
            $svg = str_replace('width="16"', 'width="' . $size . '"', $svg);
            $svg = str_replace('height="16"', 'height="' . $size . '"', $svg);
            return $svg;
        }
        return '';
    }

    public static function edit($size = 16)
    {
        return self::getSvgFile('edit', $size);
    }

    public static function delete($size = 16)
    {
        return self::getSvgFile('delete', $size);
    }

    public static function view($size = 16)
    {
        return self::getSvgFile('view', $size);
    }

     /**
     * Get Invoice SVG Icon
     */
    public static function invoice($size = 16, $color = 'currentColor')
    {
        return self::getSvgFile('invoice', $size, $color);
    }
    
    public static function add($size = 16)
    {
        return self::getSvgFile('add', $size);
    }
       /**
     * Get Rent/Book SVG Icon
     */
    public static function rent($size = 16)
    {
        return self::getSvgFile('rent', $size);
    }
}