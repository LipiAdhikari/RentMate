<?php

namespace App\Services;

use Carbon\Carbon;
use DateTime;

class NepaliCalendar
{
    /**
     * Nepali Months dictionary
     */
    public const MONTHS = [
        1 => ['en' => 'Baisakh', 'np' => 'बैशाख'],
        2 => ['en' => 'Jestha', 'np' => 'जेठ'],
        3 => ['en' => 'Ashadh', 'np' => 'असार'],
        4 => ['en' => 'Shrawan', 'np' => 'साउन'],
        5 => ['en' => 'Bhadra', 'np' => 'भदौ'],
        6 => ['en' => 'Ashwin', 'np' => 'असोज'],
        7 => ['en' => 'Kartik', 'np' => 'कार्तिक'],
        8 => ['en' => 'Mangsir', 'np' => 'मंसिर'],
        9 => ['en' => 'Poush', 'np' => 'पुष'],
        10 => ['en' => 'Magh', 'np' => 'माघ'],
        11 => ['en' => 'Falgun', 'np' => 'फागुन'],
        12 => ['en' => 'Chaitra', 'np' => 'चैत'],
    ];

    /**
     * Number of days in each month for BS years 2075 to 2090
     */
    protected static array $bsDays = [
        2075 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2076 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        2077 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2078 => [31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        2079 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2080 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        2081 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2082 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2083 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2084 => [31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30],
        2085 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2086 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2087 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2088 => [31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30],
        2089 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2090 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
    ];

    /**
     * Calibration Anchor:
     * 2083-01-01 BS = 2026-04-14 AD
     */
    protected const ANCHOR_BS_YEAR = 2083;
    protected const ANCHOR_AD_DATE = '2026-04-14';

    /**
     * Get days in specific BS month
     */
    public static function getDaysInBsMonth(int $bsYear, int $bsMonth): int
    {
        return self::$bsDays[$bsYear][$bsMonth - 1] ?? 30;
    }

    /**
     * Convert BS Date (Year, Month, Day) to AD Date string (Y-m-d)
     */
    public static function bsToAd(int $bsYear, int $bsMonth, int $bsDay): string
    {
        $anchorYear = self::ANCHOR_BS_YEAR;
        $anchorDate = Carbon::parse(self::ANCHOR_AD_DATE);

        $maxDays = self::getDaysInBsMonth($bsYear, $bsMonth);
        $bsDay = max(1, min($bsDay, $maxDays));

        $daysDiff = 0;

        if ($bsYear >= $anchorYear) {
            for ($y = $anchorYear; $y < $bsYear; $y++) {
                $daysDiff += array_sum(self::$bsDays[$y] ?? [31,31,32,31,31,31,30,29,30,29,30,30]);
            }
            for ($m = 1; $m < $bsMonth; $m++) {
                $daysDiff += self::$bsDays[$bsYear][$m - 1] ?? 30;
            }
            $daysDiff += ($bsDay - 1);

            return $anchorDate->copy()->addDays($daysDiff)->format('Y-m-d');
        } else {
            for ($y = $anchorYear - 1; $y > $bsYear; $y--) {
                $daysDiff += array_sum(self::$bsDays[$y] ?? [31,31,32,31,31,31,30,29,30,29,30,30]);
            }
            for ($m = $bsMonth + 1; $m <= 12; $m++) {
                $daysDiff += self::$bsDays[$bsYear][$m - 1] ?? 30;
            }
            $daysDiff += ($maxDays - $bsDay + 1);

            return $anchorDate->copy()->subDays($daysDiff)->format('Y-m-d');
        }
    }

    /**
     * Convert an AD Date (Y-m-d) to BS Date array
     */
    public static function adToBs(string|DateTime|Carbon $adDateInput): array
    {
        $adDate = $adDateInput instanceof DateTime || $adDateInput instanceof Carbon
            ? clone $adDateInput
            : new DateTime($adDateInput);

        $anchor = new DateTime(self::ANCHOR_AD_DATE);
        $diffDays = (int) $anchor->diff($adDate)->format('%r%a');

        $bsYear = self::ANCHOR_BS_YEAR;
        $bsMonth = 1;
        $bsDay = 1 + $diffDays;

        if ($bsDay > 0) {
            while ($bsDay > (self::$bsDays[$bsYear][$bsMonth - 1] ?? 30)) {
                $bsDay -= self::$bsDays[$bsYear][$bsMonth - 1] ?? 30;
                $bsMonth++;
                if ($bsMonth > 12) {
                    $bsMonth = 1;
                    $bsYear++;
                }
            }
        } else {
            while ($bsDay <= 0) {
                $bsMonth--;
                if ($bsMonth < 1) {
                    $bsMonth = 12;
                    $bsYear--;
                }
                $bsDay += self::$bsDays[$bsYear][$bsMonth - 1] ?? 30;
            }
        }

        $monthInfo = self::MONTHS[$bsMonth] ?? ['en' => 'Month ' . $bsMonth, 'np' => 'महिना ' . $bsMonth];

        return [
            'year' => $bsYear,
            'month' => $bsMonth,
            'day' => $bsDay,
            'month_en' => $monthInfo['en'],
            'month_np' => $monthInfo['np'],
            'formatted' => sprintf('%s %02d, %d', $monthInfo['en'], $bsDay, $bsYear),
            'formatted_np' => sprintf('%s %s, %s', $monthInfo['np'], self::toNepaliNumerals($bsDay), self::toNepaliNumerals($bsYear)),
            'short' => sprintf('%s %d', $monthInfo['en'], $bsYear),
            'short_np' => sprintf('%s %s', $monthInfo['np'], self::toNepaliNumerals($bsYear)),
        ];
    }

    /**
     * Approximate English Month to dominant Nepali Month and vice-versa
     */
    public static function guessBsMonthFromAd(int $adYear, int $adMonth): array
    {
        // 18th day of AD month reliably maps to the dominant Nepali month (e.g. mid Nov -> Mangsir)
        $dateStr = sprintf('%04d-%02d-18', $adYear, $adMonth);
        $bsInfo = self::adToBs($dateStr);

        return [
            'bs_year' => $bsInfo['year'],
            'bs_month' => $bsInfo['month'],
            'month_name' => $bsInfo['month_en'] . ' (' . $bsInfo['month_np'] . ')',
        ];
    }

    /**
     * Map Nepali Month and Year to approximate AD starting month/year
     */
    public static function bsMonthToAd(int $bsYear, int $bsMonth): array
    {
        // Baisakh (Month 1) corresponds to approx April
        // Month offset from Baisakh
        $adYear = $bsYear - 57;
        $adMonth = $bsMonth + 3;
        if ($adMonth > 12) {
            $adMonth -= 12;
            $adYear++;
        }

        return [
            'ad_year' => $adYear,
            'ad_month' => $adMonth,
            'ad_date' => sprintf('%04d-%02d-01', $adYear, $adMonth),
        ];
    }

    /**
     * Get Devanagari numerals
     */
    public static function toNepaliNumerals(string|int $input): string
    {
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $np = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

        return str_replace($en, $np, (string) $input);
    }

    /**
     * Get dual formatted label for a monthly record
     * e.g. "Mangsir 2083 (मंसिर २०८३) • Nov 2026"
     */
    public static function getDualMonthLabel(int $adYear, int $adMonth, ?int $bsYear = null, ?int $bsMonth = null): string
    {
        if (!$bsYear || !$bsMonth) {
            $guessed = self::guessBsMonthFromAd($adYear, $adMonth);
            $bsYear = $guessed['bs_year'];
            $bsMonth = $guessed['bs_month'];
        }

        $monthInfo = self::MONTHS[$bsMonth] ?? ['en' => 'Month ' . $bsMonth, 'np' => 'महिना ' . $bsMonth];
        $adDate = Carbon::createFromDate($adYear, $adMonth, 1);

        return sprintf(
            '%s %d (%s %s) • %s',
            $monthInfo['en'],
            $bsYear,
            $monthInfo['np'],
            self::toNepaliNumerals($bsYear),
            $adDate->format('M Y')
        );
    }

    /**
     * Returns today's date in Bikram Sambat
     */
    public static function today(): array
    {
        return self::adToBs(Carbon::now());
    }
}
