<?php
class DurationCalculator
{
    public static function calculateDuration($start_date, $end_date, $durationUnit)
    {
        if (!self::isValidIso8601Date($start_date)) {
            throw new Exception('Invalid start_date format.');
        }

        if (!is_null($end_date) && !self::isValidIso8601Date($end_date)) {
            throw new Exception('Invalid end_date format.');
        }

        if (!in_array($durationUnit, ['HOURS', 'DAYS', 'WEEKS'])) {
            $durationUnit = 'DAYS'; // Default fallback
        }

        $startDateTime = new DateTime($start_date);
        $endDateTime = !is_null($end_date) ? new DateTime($end_date) : null;

        if ($endDateTime && $endDateTime > $startDateTime) {
            $interval = $endDateTime->diff($startDateTime);

            if ($durationUnit === 'HOURS') {
                return ['value' => $interval->h + $interval->days * 24, 'unit' => 'HOURS'];
            } elseif ($durationUnit === 'WEEKS') {
                return ['value' => $interval->days / 7, 'unit' => 'WEEKS'];
            } elseif ($durationUnit === 'DAYS') {
                return ['value' => $interval->days, 'unit' => 'DAYS'];
            }
        }

        return null;
    }

    private static function isValidIso8601Date($date)
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $date);
    }
}
