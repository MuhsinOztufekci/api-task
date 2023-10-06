<?php

/**
 * Class ConstructionStages
 *
 * A class representing construction stages and providing methods to interact with the database.
 */
class ConstructionStages
{
	private $db;
	/**
	 * ConstructionStages constructor.
	 * Initializes the database connection.
	 */

	public function __construct()
	{
		$this->db = Api::getDb();
	}
	/**
	 * Retrieves all construction stages from the database.
	 *
	 * @return array An array of construction stages data.
	 */
	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Retrieves a single construction stage based on its ID from the database.
	 *
	 * @param int $id The ID of the construction stage.
	 *
	 * @return array An associative array containing construction stage data.
	 */

	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Inserts a new construction stage into the database based on the provided data.
	 *
	 * @param ConstructionStagesCreate $data The data for creating a new construction stage.
	 *
	 * @return array An associative array containing the newly created construction stage data.
	 *
	 * @throws Exception If validation fails or duration calculation encounters an error.
	 */

	public function post(ConstructionStagesCreate $data)
	{
		try {
			// Doğrulamalar
			Validation::validateData($data);

			// Süre ve süre birimini hesapla
			$durationInfo = DurationCalculator::calculateDuration($data->startDate, $data->endDate, $data->durationUnit);

			if ($durationInfo === null) {
				// Süre hesaplanamazsa, uygun bir hata döndür
				http_response_code(400); // Bad Request
				echo json_encode(["error" => "Invalid date or duration calculation error."]);
				return;
			}

			// SQL sorgusunu hazırla ve çalıştır
			$stmt = $this->db->prepare("
            INSERT INTO construction_stages
            (name, start_date, end_date, duration, durationUnit, color, externalId, status)
            VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
        ");
			$stmt->execute([
				'name' => $data->name,
				'start_date' => $data->startDate,
				'end_date' => $data->endDate,
				'duration' => $durationInfo['value'], // Hesaplanan süre değerini kullan
				'durationUnit' => $durationInfo['unit'], // Hesaplanan süre birimini kullan
				'color' => $data->color,
				'externalId' => $data->externalId,
				'status' => $data->status,
			]);

			// Yeni eklenen kaydı döndür
			return $this->getSingle($this->db->lastInsertId());
		} catch (Exception $e) {
			// Hataları uygun bir şekilde işle
			http_response_code(400); // Bad Request
			echo json_encode(["error" => $e->getMessage()]);
			return;
		}
	}

	/**
	 * Updates an existing construction stage in the database based on the provided ID and data.
	 *
	 * @param int $id The ID of the construction stage to update.
	 * @param ConstructionStagesCreate $data The updated data for the construction stage.
	 *
	 * @return array An associative array containing the updated construction stage data.
	 *
	 * @throws Exception If validation fails or an invalid status value is provided.
	 */

	public function patch($id, ConstructionStagesCreate $data)
	{
		// Validate status field if sent
		if (isset($data->status) && !in_array($data->status, ['NEW', 'PLANNED', 'DELETED'])) {
			throw new Exception("Invalid status value. Status should be NEW, PLANNED, or DELETED.");
		}

		// Construct the SQL query dynamically based on provided fields
		$fieldsToUpdate = [];
		$values = [];
		foreach ($data as $key => $value) {
			if ($key !== 'id') {
				$fieldsToUpdate[] = "$key = :$key";
				$values[":$key"] = $value; // Use named placeholders here
			}
		}

		// Execute the SQL query with named placeholders
		$values[':id'] = $id;
		$stmt = $this->db->prepare("
        UPDATE construction_stages
        SET " . implode(", ", $fieldsToUpdate) . "
        WHERE ID = :id
        ");
		$stmt->execute($values);

		// Return the updated construction stage
		return $this->getSingle($id);
	}
	/**
	 * Deletes a construction stage from the database based on its ID.
	 *
	 * @param int $id The ID of the construction stage to delete.
	 *
	 * @return array An associative array containing a success message.
	 */
	public function delete($id)
	{
		// Update status to DELETED
		$stmt = $this->db->prepare("
            UPDATE construction_stages
            SET status = 'DELETED'
            WHERE ID = :id
        ");
		$stmt->execute(['id' => $id]);

		// Return success message or handle as needed
		return ['message' => 'Construction stage deleted successfully.'];
	}
}
