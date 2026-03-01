<?php

namespace App\Helpers;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;

class DateUtils
{
    /**
     * Convert AD (English) date to BS (Nepali) date
     * 
     * @param string $adDate YYYY-MM-DD
     * @param string|null $format Optional format
     * @param string $locale 'np' or 'en'
     * @return string
     */
    public static function adToBs($adDate, $format = 'Y-m-d', $locale = 'en')
    {
        if (empty($adDate)) return '';
        
        try {
            return LaravelNepaliDate::from($adDate)->toNepaliDate($format, $locale);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Convert BS (Nepali) date to AD (English) date
     * 
     * @param string $bsDate YYYY-MM-DD
     * @return string
     */
    public static function bsToAd($bsDate)
    {
        if (empty($bsDate)) return '';
        
        try {
            return LaravelNepaliDate::from($bsDate)->toEnglishDate();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Format a Nepali date elegantly
     * 
     * @param string $date (AD or BS)
     * @param string $locale 'np' or 'en'
     * @return string
     */
    public static function formatNepali($date, $locale = 'np')
    {
        if (empty($date)) return '';
        
        try {
            return LaravelNepaliDate::from($date)->toNepaliDate('D, j F Y', $locale);
        } catch (\Throwable $e) {
            return $date;
        }
    }
}
