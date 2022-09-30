-- Host: localhost

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_data`
--

CREATE TABLE `vehicle_data` (
  `vin_id` bigint(20) NOT NULL,
  `vin` varchar(17) NOT NULL,
  `year` varchar(4) NOT NULL,
  `make` varchar(145) DEFAULT NULL,
  `model` varchar(155) DEFAULT NULL,
  `trim` varchar(155) DEFAULT NULL,
  `dealer_name` varchar(165) NOT NULL,
  `dealer_street` varchar(120) NOT NULL,
  `dealer_city` varchar(50) DEFAULT NULL,
  `dealer_state` varchar(2) DEFAULT NULL,
  `dealer_zip` varchar(7) DEFAULT NULL,
  `listing_price` varchar(7) DEFAULT NULL,
  `listing_mileage` varchar(7) DEFAULT NULL,
  `used` varchar(5) NOT NULL,
  `certified` varchar(5) NOT NULL,
  `style` varchar(155) DEFAULT NULL,
  `driven_wheels` varchar(155) DEFAULT NULL,
  `engine` varchar(155) DEFAULT NULL,
  `fuel_type` varchar(145) DEFAULT NULL,
  `exterior_color` varchar(90) DEFAULT NULL,
  `interior_color` varchar(150) DEFAULT NULL,
  `seller_website` varchar(70) NOT NULL,
  `first_seen_date` date NOT NULL,
  `last_seen_date` date NOT NULL,
  `dealer_vdp_last_seen_date` varchar(10) DEFAULT NULL,
  `listing_status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `vehicle_data`
--
ALTER TABLE `vehicle_data`
  ADD PRIMARY KEY (`vin_id`),
  ADD KEY `year` (`year`),
  ADD KEY `make` (`make`),
  ADD KEY `model` (`model`),
  ADD KEY `listing_mileage` (`listing_mileage`),
  ADD KEY `last_seen_date` (`last_seen_date`);

--
-- AUTO_INCREMENT for table `vehicle_data`
--
ALTER TABLE `vehicle_data`
  MODIFY `vin_id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
