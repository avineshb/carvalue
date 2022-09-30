<?php

declare(strict_types = 1);

namespace CarValue;

use Exception;
use PDO;
use PDOException;

/**
 * CarValue Trial Project 2.
 *
 * @category Class
 * @author   Avinesh Bangar <avinesh@shaw.ca>
 *
 * Imports CSV data into a MySQL database.
 */
class DataImporter
{
    private $insert;

    public $config;
    public $csvPath = './data/inventory-listing.csv';
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
     * Import data from CSV file.
     *
     * This could be improved by using array chunks.
     * We can also use LOAD DATA INFILE, which is much faster.
     *
     * @throws Exception
     */
    public function importData()
    {
        // Open data file.
        $fp = fopen($this->csvPath, 'r');
        if ($fp === FALSE) {
            throw new Exception('CSV file not present.');
        }

        // Delimiter is pipe character; ASCII code is 124.
        $delimiter = chr(124);
        // Fetch headers from first row of CSV file.
        $header = fgetcsv($fp, 1024, $delimiter);

        // Import data into `vehicle_data` table.
        try {
            $this->db->beginTransaction();
            $i = 0;
            // Fetch remaining data from CSV file.
            while ($row = fgetcsv($fp, 1024, $delimiter)) {
                // Ensure the number of header columns matches the row columns.
                if (!$validatedData = $this->validateRow($header, $row)) {
                    continue;
                }

                $data = $this->buildRow($validatedData);
                $this->insertRow($data);

                $i++;
            }

            $this->db->commit();

            fclose($fp);

            echo $i . ' rows inserted'. PHP_EOL;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            fclose($fp);

            throw $e;
        }
    }

    /**
     * Validate row data.
     *
     * @param array $header
     * @param array $row
     * @return false
     */
    private function validateRow(array $header, array $row)
    {
        if (count($header) !== count($row)) {
            return false;
        }

        return array_combine($header, $row);
    }

    /**
     * Build an insert row.
     *
     * @param array $data
     * @return array
     */
    private function buildRow(array $data)
    {
        $params = [
            'vin' => $data['vin'],
            'year' => $data['year'],
            'make' => $data['make'] ?? null,
            'model' => $data['model'] ?? null,
            'trim' => $data['trim'] ?? null,
            'dealer_name' => $data['dealer_name'],
            'dealer_street' => $data['dealer_street'],
            'dealer_city' => $data['dealer_city'] ?? null,
            'dealer_state' => $data['dealer_state'] ?? null,
            'dealer_zip' => $data['dealer_zip'] ?? null,
            'listing_price' => $data['listing_price'] ?? null,
            'listing_mileage' => $data['listing_mileage'] ?? null,
            'used' => $data['used'],
            'certified' => $data['certified'],
            'style' => $data['style'] ?? null,
            'driven_wheels' => $data['driven_wheels'] ?? null,
            'engine' => $data['engine'] ?? null,
            'fuel_type' => $data['fuel_type'] ?? null,
            'exterior_color' => $data['exterior_color'] ?? null,
            'interior_color' => $data['interior_color'] ?? null,
            'seller_website' => $data['seller_website'],
            'first_seen_date' => $data['first_seen_date'],
            'last_seen_date' => $data['last_seen_date'],
            'dealer_vdp_last_seen_date' => $data['dealer_vdp_last_seen_date'] ?? '0000-00-00',
            'listing_status' => $data['listing_status'] ?? null,
        ];

        return $params;
    }

    /**
     * Insert new row with vehicle data.
     *
     * @param array $params
     */
    private function insertRow(array $params)
    {
        $this->insert = $this->db->prepare(sprintf('
            INSERT INTO %s (
                vin,
                year,
                make,
                model,
                trim,
                dealer_name,
                dealer_street,
                dealer_city,
                dealer_state,
                dealer_zip,
                listing_price,
                listing_mileage,
                used,
                certified,
                style,
                driven_wheels,
                engine,
                fuel_type,
                exterior_color,
                interior_color,
                seller_website,
                first_seen_date,
                last_seen_date,
                dealer_vdp_last_seen_date,
                listing_status
            ) VALUES (
                :vin,
                :year,
                :make,
                :model,
                :trim,
                :dealer_name,
                :dealer_street,
                :dealer_city,
                :dealer_state,
                :dealer_zip,
                :listing_price,
                :listing_mileage,
                :used,
                :certified,
                :style,
                :driven_wheels,
                :engine,
                :fuel_type,
                :exterior_color,
                :interior_color,
                :seller_website,
                :first_seen_date,
                :last_seen_date,
                :dealer_vdp_last_seen_date,
                :listing_status
            );
        ', $this->config['table']));

        $this->insert->execute($params);
    }
}

$var = new DataImporter();
$var->importData();
