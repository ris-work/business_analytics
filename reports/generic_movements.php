<?php
error_reporting(E_ALL);
require_once "./env.php";
// 1. Compute default values and parse query-string inputs
$tz = new DateTimeZone('UTC');

// Determine start date: use ?startdate or default to today minus 180 days
try {
    if (!empty($_GET['startdate'])) {
        $startDt = new DateTime($_GET['startdate'], $tz);
    } else {
        $startDt = new DateTime('now', $tz);
        $startDt->modify('-180 days');
    }
} catch (Exception $e) {
    // fallback if parsing fails
    $startDt = (new DateTime('now', $tz))->modify('-180 days');
}
$startDateIso = $startDt->format(DateTime::ATOM);  // ISO 8601 aka ATOM

// Determine end date: use ?enddate or default to today
try {
    if (!empty($_GET['enddate'])) {
        $endDt = new DateTime($_GET['enddate'], $tz);
    } else {
        $endDt = new DateTime('now', $tz);
    }
} catch (Exception $e) {
    $endDt = new DateTime('now', $tz);
}
$endDateIso = $endDt->format(DateTime::ATOM);

// Determine generic category: use ?generic or default to 13
$generic = filter_input(INPUT_GET, 'generic', FILTER_VALIDATE_INT);
if ($generic === false || $generic === null) {
    $generic = 13;
}
// 1. Configure and connect via PDO
$dsn = 'sqlite:' . $dbname;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$pdo = new PDO($dsn, null, null, $options);

// 2. Define the SQL with unnamed placeholders (?)
$sql = <<<SQL
WITH moves AS (
    SELECT
        code, generic, invoicedate,
        total_sales, total_purchases
    FROM
        generic_moves
        JOIN generic_product_info
            ON generic_moves.generic = generic_product_info.category
    WHERE
        generic_product_info.classification = 3
),

nearestpast AS (
    SELECT
        sih_history.itemcode,
        MAX(sih_history.datetime)    AS maxdate,
        generic_moves.invoicedate    AS presentedformaxdate
    FROM
        generic_moves
        JOIN sih_history
            ON generic_moves.code = sih_history.itemcode
           AND generic_moves.invoicedate > sih_history.datetime
    GROUP BY
        sih_history.itemcode,
        generic_moves.invoicedate
),

moves_with_hist AS (
    SELECT
        generic_moves.generic, description, nearestpast.presentedformaxdate,
        sih_history.sih          AS sih_past,   generic_moves.code,    sih_history.datetime AS matcheddate,
        generic_moves.total_sales, generic_moves.total_purchases, generic_moves.referenceinvoices
    FROM
        generic_moves
        JOIN nearestpast
            ON generic_moves.code = nearestpast.itemcode
           AND nearestpast.presentedformaxdate = generic_moves.invoicedate
        JOIN sih_history
            ON sih_history.itemcode = nearestpast.itemcode
           AND sih_history.datetime = nearestpast.maxdate
),

uncumulative AS (
    SELECT
        generic, code, presentedformaxdate,
        TOTAL(total_sales)          AS total_sales,           TOTAL(total_purchases)       AS total_purchases,
        GROUP_CONCAT(referenceinvoices) AS referenceinvoices,
        generic_info.description, MAX(moves_with_hist.description) AS description_1, sih_past
    FROM
        moves_with_hist
        JOIN generic_info
            ON generic_info.classification = 3
           AND moves_with_hist.generic     = generic_info.category
           AND generic_info.category       = ?
    GROUP BY
        generic, code, presentedformaxdate
)

SELECT
    *
FROM
    uncumulative
WHERE
    presentedformaxdate BETWEEN ? AND ?
ORDER BY
    generic, code, presentedformaxdate;

SQL;

// 3. Prepare the statement
$stmt = $pdo->prepare($sql);

// 4. Define your parameter values in an indexed array
$startDate = '2025-06-02';
$endDate   = '2025-06-11';
$params    = [$generic, $startDateIso, $endDateIso];

// 5. Execute with array binding and fetch all rows
$stmt->execute($params);
$rows = $stmt->fetchAll();

// 6. Render results as an HTML table
if (!empty($rows)) {
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<thead><tr>';
    // Header row
    foreach (array_keys($rows[0]) as $colName) {
        echo '<th>' . htmlspecialchars($colName) . '</th>';
    }
    echo '</tr></thead><tbody>';
    // Data rows
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<p>No records found between '
         . htmlspecialchars($startDate)
         . ' and '
         . htmlspecialchars($endDate)
         . '.</p>';
}

