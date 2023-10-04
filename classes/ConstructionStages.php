<?php

class ConstructionStages
{
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}

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

	public function post(ConstructionStagesCreate $data)
	{
		$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
		$stmt->execute([
			'name' => $data->name,
			'start_date' => $data->startDate,
			'end_date' => $data->endDate,
			'duration' => $data->duration,
			'durationUnit' => $data->durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $data->status,
		]);
		return $this->getSingle($this->db->lastInsertId());
	}
	public function patch($id, $data)
	{
		// Validate status field if sent
		if (isset($data['status']) && !in_array($data['status'], ['NEW', 'PLANNED', 'DELETED'])) {
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
		$stmt = $this->db->prepare("
			UPDATE construction_stages
			SET " . implode(", ", $fieldsToUpdate) . "
			WHERE ID = :id
		");
		$values[':id'] = $id;
		$stmt->execute($values);

		// Return the updated construction stage
		return $this->getSingle($id);
	}

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
