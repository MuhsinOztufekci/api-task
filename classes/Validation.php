<?php

/**
 * Class Validation
 *
 * A utility class for validating construction stage data.
 */
class Validation
{
    /**
     * Validates the provided data for construction stages.
     *
     * @param mixed $data The data to be validated.
     *
     * @throws Exception If validation fails. The exception message contains JSON-encoded error details.
     *
     * @return bool True if validation succeeds.
     */
    public static function validateData($data)
    {
        $errors = [];

        // Check name length
        if (isset($data->name) && strlen($data->name) > 255) {
            $errors['name'] = 'Name must be a maximum of 255 characters.';
        }

        // Check start_date format
        if (isset($data->start_date) && !self::isValidIso8601Date($data->start_date)) {
            $errors['start_date'] = 'Invalid start_date format. It should be in ISO8601 format (e.g., 2022-12-31T14:59:00Z).';
        }

        // Check end_date format
        if (isset($data->end_date) && !is_null($data->end_date) && !self::isValidIso8601Date($data->end_date)) {
            $errors['end_date'] = 'Invalid end_date format. It should be in ISO8601 format (e.g., 2022-12-31T14:59:00Z) or null.';
        }

        // Check end_date is later than start_date
        if (isset($data->start_date, $data->end_date) && !is_null($data->end_date) && strtotime($data->end_date) <= strtotime($data->start_date)) {
            $errors['end_date'] = 'End date must be later than start date.';
        }

        // Check durationUnit value
        $allowedDurationUnits = ['HOURS', 'DAYS', 'WEEKS'];
        if (isset($data->durationUnit) && !in_array($data->durationUnit, $allowedDurationUnits) && !is_null($data->durationUnit)) {
            $errors['durationUnit'] = 'Invalid durationUnit. It should be one of HOURS, DAYS, WEEKS.';
        }

        // Check color format
        if (isset($data->color) && !is_null($data->color) && !preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $data->color)) {
            $errors['color'] = 'Invalid color format. It should be a valid HEX color (e.g., #FF0000).';
        }

        // Check externalId length
        if (isset($data->externalId) && strlen($data->externalId) > 255) {
            $errors['externalId'] = 'External ID must be a maximum of 255 characters.';
        }

        // Check status value
        $allowedStatusValues = ['NEW', 'PLANNED', 'DELETED'];
        if (isset($data->status) && !in_array($data->status, $allowedStatusValues) && !is_null($data->status)) {
            $errors['status'] = 'Invalid status value. It should be one of NEW, PLANNED, DELETED.';
        }

        // Throw errors if any
        if (!empty($errors)) {
            throw new Exception(json_encode($errors));
        }

        return true;
    }
    /**
     * Checks if the given date is in ISO8601 format.
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

try {
    $data = json_decode(file_get_contents('php://input'));
    Validation::validateData($data);
} catch (Exception $e) {
    echo $e->getMessage();
}
