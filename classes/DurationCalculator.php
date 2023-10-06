<?php
class DurationCalculator
{
    /**
     * Calculates the duration between the given start date and end date in the specified duration unit.
     *
     * @param string $start_date The start date in ISO8601 format (YYYY-MM-DDTHH:mm:ssZ).
     * @param string|null $end_date The end date in ISO8601 format (YYYY-MM-DDTHH:mm:ssZ) or null for current time.
     * @param string $durationUnit The unit of duration ('HOURS', 'DAYS', 'WEEKS').
     * 
     * @return array|null An associative array containing 'value' and 'unit' keys representing the calculated duration,
     *                   or null if the input dates are invalid or end date is earlier than start date.
     * 
     * @throws Exception If the input dates are in invalid ISO8601 format.
     */
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
    /**
     * Validates if the given date is in ISO8601 format.
     *
     * @param string $date The date to validate.
     * 
     * @return bool True if the date is in valid ISO8601 format, false otherwise.
     */
    private static function isValidIso8601Date($date)
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $date);
    }
}
