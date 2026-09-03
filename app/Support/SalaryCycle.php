<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * دورة الراتب: الراتب ينزل يوم 27، فالدورة المالية تبدأ من 27 وتنتهي 26 من
 * الشهر اللي بعده. كل الحسابات "الشهرية" (الدخل، المصروف، حد الميزانية)
 * تمشي على هالنافذة مو على الشهر الميلادي، عشان معاملة يوم 28 تدخل في
 * الدورة الحالية لا في دورة الشهر الجاي.
 */
class SalaryCycle
{
    /**
     * أول يوم في الدورة (اليوم اللي ينزل فيه الراتب).
     */
    public const START_DAY = 27;

    /**
     * بداية الدورة اللي يقع فيها التاريخ المعطى، الساعة 00:00:00.
     */
    public static function startFor(Carbon $date): Carbon
    {
        $day = $date->copy()->startOfDay();

        $anchor = $day->day >= self::START_DAY
            ? $day->copy()->startOfMonth()
            : $day->copy()->startOfMonth()->subMonth();

        return $anchor->day(self::START_DAY)->startOfDay();
    }

    /**
     * نهاية الدورة اللي يقع فيها التاريخ المعطى: 26 من الشهر التالي 23:59:59.
     */
    public static function endFor(Carbon $date): Carbon
    {
        return self::startFor($date)
            ->addMonth()
            ->day(self::START_DAY - 1)
            ->endOfDay();
    }

    /**
     * بداية الدورة السابقة، عشان نقارن الدورة الحالية بالـ trend حقها.
     */
    public static function previousStartFor(Carbon $date): Carbon
    {
        return self::startFor(self::startFor($date)->subDay());
    }

    /**
     * نهاية الدورة السابقة.
     */
    public static function previousEndFor(Carbon $date): Carbon
    {
        return self::endFor(self::startFor($date)->subDay());
    }

    /**
     * نطاق الدورة الحالية كـ [من, إلى].
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function currentRange(?Carbon $now = null): array
    {
        $now ??= Carbon::now();

        return [self::startFor($now), self::endFor($now)];
    }

    /**
     * نطاق الدورة السابقة كـ [من, إلى].
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function previousRange(?Carbon $now = null): array
    {
        $now ??= Carbon::now();

        return [self::previousStartFor($now), self::previousEndFor($now)];
    }

    /**
     * الأيام المتبقية لين نهاية الدورة (يشمل اليوم الحالي، وأقلها يوم واحد).
     */
    public static function remainingDays(?Carbon $now = null): int
    {
        $now ??= Carbon::now();

        $today = $now->copy()->startOfDay();

        return max(1, (int) $today->diffInDays(self::endFor($now)) + 1);
    }
}
