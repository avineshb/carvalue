const formValues = new FormData();

formValues.set('action', 'fetch_data');
formValues.set('vehicle', '');
formValues.set('mileage', '');

/**
 * Gets form data and calls 'fetchData()'.
 */
function getMarketPrice()
{
    // Should follow the convention: 2015 Toyota Camry
    // The following variation with the trim also works: 2015 Toyota Camry LE
    const validVehicle = /[0-9]+\s[a-zA-Z]+\s[a-zA-Z]+/i;

    document.getElementById('validation').style.display = 'none';
    document.getElementById('market_price').innerHTML = 'Enter a vehicle, make and model to get started.';
    document.getElementById('vehicle_data').innerHTML = 'If no vehicles match or the mileage is invalid, nothing is shown.';

    const vehicle = document.getElementById('vehicle').value;
    const mileage = document.getElementById('mileage').value;

    if (vehicle == '' || (!validVehicle.test(vehicle))) {
        document.getElementById('validation').style.display = 'block';
        return;
    }

    fetchData(vehicle, mileage);
}

/**
 * Fetch data from the server using the Fetch API.
 */
function fetchData(vehicle, mileage)
{
    formValues.set('vehicle', vehicle);
    formValues.set('mileage', mileage);

    // The JSON response body contains 'market_value' and 'vehicle_data'.
    fetch('./FetchVehicleData.php', {method: 'POST', body: formValues})
        .then(result => result.json())
        .then(result => displayData(result))
        .catch(e => console.log(e));
}

/**
 * Format a number with grouped thousands using commas.
 */
function formatNumber(number)
{
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Ensure a number with valid digits is entered for 'mileage'.
 */
function checkNumber()
{
    if (window.event && window.event.keyCode == 32 || window.event.keyCode < 48 || window.event.keyCode > 57) {
        window.event.cancelBubble = true;
        window.event.returnValue = false;

        return false;
    }
}

/**
 * Display vehicle data.
 */
function displayData(response)
{
    document.getElementById("market_price").innerHTML = `<strong>Estimated Market Price: $${response["market_value"]}</strong>`;

    var table = '<table align="center" style="margin-left: 25px" class="table table-sm table-striped table-bordered table-hover">';
    table += '<thead class="thead-light">';
    table += '<tr><th>Vehicle</th><th>Price</th><th>Mileage</th><th>Location</th></tr>';
    table += '</thead><tbody>';

    const vehicle_data = response["vehicle_data"];
    for (let i = 0; i < vehicle_data.length; i++)
    {
        table += '<tr>';
        table += '<td>' + vehicle_data[i]["vehicle"] + '</td>';
        table += '<td>$' + formatNumber(vehicle_data[i]["listing_price"]) + '</td>';
        table += '<td>' + formatNumber(vehicle_data[i]["listing_mileage"]) + ' miles</td>';
        table += '<td>' + vehicle_data[i]["location"] + '</td>';
        table += '</tr>';
    }

    table += '</tbody>';
    table += '</table>';

    document.getElementById("vehicle_data").innerHTML = table;
}
