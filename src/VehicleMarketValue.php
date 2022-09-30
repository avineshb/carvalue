<?php

declare(strict_types = 1);

namespace CarValue;

require_once __DIR__ . '/../vendor/autoload.php';

use Exception;
use PDO;
use PDOException;
use Phpml\Regression\LeastSquares;

/**
 * CarValue Trial Project 2.
 *
 * @category Class
 * @author   Avinesh Bangar <avinesh@shaw.ca>
 *
 * Predict the estimated market value of a vehicle, given the following:
 * year, make, model, and optionally mileage.
 */
class VehicleMarketValue
{
    public $config;
    public $db;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->config = [
            'dsn'       => 'mysql:host=localhost;dbname=inventory;charset=UTF8',
            'username'  => 'username',
            'password'  => 'a_secret_password',
            'table'     => 'vehicle_data',
        ];

        $this->connectToDatabase();
    }

    /**
     * Connect to database.
     *
     * @throws Exception
     */
    private function connectToDatabase()
    {
        try {
            $this->db = new PDO(
                $this->config['dsn'],
                $this->config['username'],
                $this->config['password']
            );

            // Set PDO error mode to exception.
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $error) {
            throw new Exception(
                sprintf('Database connection error: %s', $error->getMessage())
            );
        }
    }

    /**
     * Determine market value estimate of vehicle.
     *
     * @param string $vehicle
     * @param string|null $mileage
     * @return string
     */
    public function predictMarketValue($vehicle, $mileage = null)
    {
        // Split query string into parameters. Vehicle trim is optional.
        list($year, $make, $model, $trim) = array_pad(explode(' ', $vehicle), 4, null);

        // Build parameters for MySQL prepared statement.
        $params = $this->buildSQLParams($year, $make, $model, $trim, $mileage);
        // Execute SQL query and return vehicle data.
        $vehicle_data = $this->fetchVehicleData($params);

        $samples = [];
        $targets = [];
        $total_mileage = 0;
        $average_mileage = 0;

        // Iterate vehicle data rows.
        foreach ($vehicle_data as $row) {
            // Ensure 'listing_mileage' and 'listing_price' are not empty values.
            if (!empty($row['listing_mileage']) && !empty($row['listing_price'])) {
                $samples[] = [
                    (int) $row['listing_mileage']
                ];

                $targets[] = (int) $row['listing_price'];

                $total_mileage += (int) $row['listing_mileage'];
            }
        }

        // Ensure we have at least two rows.
        if (count($vehicle_data) > 1) {
            $average_mileage = $total_mileage / count($samples);

            // Use 'mileage' if it has been specified, or use the computed average mileage.
            $mileage = $mileage ?? $average_mileage;

            // Instantiate PHP-ML LeastSquares class.
            $regression = new LeastSquares();

            // Compute coefficients and intercept.
            $regression->train($samples, $targets);

            // Determine estimated market value and format value.
            $predicted_value = number_format($regression->predictSample([$mileage]));

            return json_encode([
                'market_value' => $predicted_value,
                'vehicle_data' => $vehicle_data
            ]);
        }
    }

    /**
     * Build select parameters.
     *
     * @param string $year
     * @param string $make
     * @param string $model
     * @param string|null $trim
     * @param string|null $mileage
     * @return array
     */
    private function buildSQLParams($year, $make, $model, $trim = null, $mileage = null)
    {
        return [
            'year' => $year,
            'make' => $make,
            'model' => $model,
            'trim' => $trim,
            'listing_mileage' => $mileage,
        ];
    }

    /**
     * Fetch vehicle data.
     * Handles most anomalies seen in test data.
     *
     * @param array $params
     * @return array
     * @throws Exception
     */
    private function fetchVehicleData(array $params)
    {
        try {
            $statement = $this->db->prepare(sprintf('
                SELECT
                    CONCAT_WS(" ", year, make, model, trim) AS vehicle,
                    listing_price,
                    CONCAT_WS(", ", dealer_city, dealer_state) AS location,
                    listing_mileage
                FROM %s
                WHERE (:year IS NULL OR year = :year)
                    AND (:make IS NULL OR make = :make)
                    AND (:model IS NULL OR model = :model)
                    AND (:trim IS NULL OR trim = :trim)
                    AND (:listing_mileage IS NULL OR (
                        CAST(listing_mileage AS UNSIGNED) <= :listing_mileage)
                        AND listing_mileage NOT IN ("", "0")
                    )
                    AND listing_price != ""
                    AND listing_mileage != ""
                    AND dealer_city != ""
                    AND dealer_state != ""
                ORDER BY make, model, trim, last_seen_date DESC
                LIMIT 100;
            ', $this->config['table']));

            $statement->execute($params);

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $error) {
            throw new Exception(
                sprintf('SQL exception: %s', $error->getMessage())
            );
        }
    }
}
