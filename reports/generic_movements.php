<head>
<style>
body{
-webkit-print-color-adjust: exact;
print-color-adjust:exact;
}
  /* —— BASE STYLES —— */
  table {
	margin: 30px auto;
	width: 90%;
	max-width: 1200px;
	border-collapse: collapse;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	font-family: Arial, sans-serif;
	background-color: #fff;
print-color-adjust: exact;
  }

  table caption {
	caption-side: top;
	text-align: center;
	font-size: 1.1em;
	padding: 8px;
	color: #666;
  }

  th {
	background-color: #f2f2f2;
	color: #333;
	font-weight: 600;
	padding: 2px 6px;
	text-align: left;
	border-bottom: 2px solid #ddd;
	position: sticky;
  }
thead * {
position: sticky;
top: 0;
}

  td {
	padding: 2px 5px;
	color: #444;
	border-bottom: 2px dotted #aaa;
print-color-adjust: exact;
  }

  /* zebra striping */
  tr:nth-child(even) {
	background-color: #fafafa;
	border-bottom: 2px dotted #aaa;
print-color-adjust: exact;
  }

  /* hover highlight */
  tr:hover {
	background-color: #f1f1f1;
  }

  /* —— GLOBAL NUMERIC ALIGNMENT —— */
  /* presentedformaxdate (col 3), sales (6), c_sales (7), purchases (8), c_purchases (9), past_stock (11) */
  th:nth-child(2),
  td:nth-child(2),
  th:nth-child(3),
  td:nth-child(3),
  th:nth-child(4),
  td:nth-child(4),
  th:nth-child(5),
  td:nth-child(5),
  th:nth-child(6),
  td:nth-child(6),
  th:nth-child(7),
  td:nth-child(7),
  th:nth-child(8),
  td:nth-child(8),
  th:nth-child(9),
  td:nth-child(9),
  th:nth-child(11),
  td:nth-child(11) {
	text-align: right;
  }
  @media print {
thead * {
position: relative;
}
td{
overflow-wrap: anywhere;
word-wrap: anywhere;
max-width: 250px;
}

  }

  /* —— PRINT-FRIENDLY OVERRIDES —— */
</style>
<!--
  @media print {
	table {
	  margin: 0;
	  width: 100% !important;
	  box-shadow: none !important;
	  /*background-color: transparent !important;*/
	  page-break-inside: avoid;
	  border: 1px solid #000;
print-color-adjust: exact;
	}

	tr:nth-child(even) {
	  background-color: #f2f2f2 !important;
print-color-adjust: exact;
	}

	th, td {
	  -webkit-print-color-adjust: exact;
	  print-color-adjust: exact;
	  border: 2px solid #000 !important;
	  background-color: #f9f9f9 !important;
	  font-size: 10pt;
	  padding: 3px 4px;
	}
thead * {
position: relative;
}

	/* hide generic ID and generic-description (cols 1 & 4) */
/*
	th:nth-child(1),
	td:nth-child(1),
	th:nth-child(3),
	td:nth-child(3) {
	  display: none !important;
	}
*/
	/* prevent breaking rows across pages */
	tr {
	  page-break-inside: avoid;
	}

	caption {
	  font-size: 12pt;
	  color: #000;
	  padding-bottom: 4px;
	}
  }
</style>-->
</head>



<?php
error_reporting(E_ALL);
require_once "./env.php";
// 1. Compute default values and parse query-string inputs
$tz = new DateTimeZone('UTC');
$UseQuarters = false;
if(isset($_GET['quarter']) && $_GET['quarter'] != '0' && strtolower($_GET['quarter']) != "none" && strtolower($_GET['quarter']) != "nothing"){
	$UseQuarters = true;
	$Quarter = (int)$_GET['quarter'];
}

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
	code,
	generic,
	invoicedate,
	total_sales,
	total_purchases
	FROM
	generic_moves
	JOIN generic_product_info
		ON generic_moves.generic = generic_product_info.category
	WHERE
	generic_product_info.classification = 3
),

nearestpast AS (
	SELECT
	generic_moves.code           AS itemcode,
	(
	  SELECT
		MAX(sh.datetime)
	  FROM
		sih_history AS sh
	  WHERE
		sh.itemcode  = generic_moves.code
		AND sh.datetime < generic_moves.invoicedate
	)                            AS maxdate,
	generic_moves.invoicedate    AS presentedformaxdate
	FROM
	generic_moves
),

moves_with_hist AS (
	SELECT
	generic_moves.generic,
	description,
	nearestpast.presentedformaxdate,
	sih_history.sih             AS sih_past,
	generic_moves.code,
	sih_history.datetime        AS matcheddate,
	generic_moves.total_sales,
	generic_moves.total_purchases,
	generic_moves.referenceinvoices
	FROM
	generic_moves
	JOIN nearestpast
	  ON generic_moves.code              = nearestpast.itemcode
	 AND generic_moves.invoicedate       = nearestpast.presentedformaxdate
	JOIN sih_history
	  ON sih_history.itemcode           = nearestpast.itemcode
	 AND sih_history.datetime           = nearestpast.maxdate
),

uncumulative AS (
	SELECT
	generic,
	code,
	presentedformaxdate,
	TOTAL(total_sales)            AS total_sales,
	TOTAL(total_purchases)        AS total_purchases,
	GROUP_CONCAT(referenceinvoices) AS referenceinvoices,
	generic_info.description AS genericdesc,
	MAX(moves_with_hist.description) AS description,
	sih_past
	FROM
	moves_with_hist
	JOIN generic_info
	  ON generic_info.classification  = 3
	 AND moves_with_hist.generic      = generic_info.category
	 AND generic_info.category        = ?
	GROUP BY
	generic,
	code,
	presentedformaxdate
),

truedata AS (
SELECT
	CASE WHEN ROW_NUMBER() OVER (PARTITION BY code ORDER BY presentedformaxdate) = 1 THEN generic ELSE '' END AS genericp,
	CASE WHEN ROW_NUMBER() OVER (PARTITION BY code ORDER BY presentedformaxdate) = 1 THEN code ELSE '' END AS codep,
	CASE WHEN ROW_NUMBER() OVER (PARTITION BY code ORDER BY presentedformaxdate) = 1 THEN genericdesc ELSE '' END AS genericdescp,
	CASE WHEN ROW_NUMBER() OVER (PARTITION BY code ORDER BY presentedformaxdate) = 1 THEN description ELSE '' END AS descriptionp,
        code,
        generic,
	SUBSTR(presentedformaxdate, 6, 5) AS as_at,
	total_sales AS sales,
	total(total_sales) OVER (PARTITION BY code ORDER BY presentedformaxdate ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW ) AS c_sales,
	total_purchases AS purchases,
	total(total_purchases) OVER (PARTITION BY code ORDER BY presentedformaxdate ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW ) AS c_purchases,
	referenceinvoices,
	sih_past AS past_stock,
        last_value(sih_past) OVER (PARTITION BY code ORDER BY presentedformaxdate ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) AS last_sih
FROM
	uncumulative
WHERE
	presentedformaxdate BETWEEN ? AND ?
ORDER BY
	generic,
	code,
	presentedformaxdate
),

computed AS (
SELECT genericp, codep, genericdescp, descriptionp, as_at, sales, c_sales, purchases, c_purchases, referenceinvoices, past_stock AS manual,
last_sih
+total(sales) OVER (PARTITION BY code ORDER BY as_at ROWS BETWEEN CURRENT ROW AND UNBOUNDED FOLLOWING EXCLUDE CURRENT ROW) 
-total(purchases) OVER (PARTITION BY code ORDER BY as_at ROWS BETWEEN CURRENT ROW AND UNBOUNDED FOLLOWING EXCLUDE CURRENT ROW) 
AS computed_sih

FROM truedata ORDER BY generic, code, as_at)


SELECT genericp, codep, genericdescp, descriptionp, as_at, sales, c_sales, purchases, c_purchases, referenceinvoices, manual,
computed_sih
FROM computed

;

SQL;
//total(total_sales) OVER (PARTITION BY code ORDER BY presentedformaxdate ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW ) AS c_sales
$DefinedQuarters = [];
$year = date('Y');
$DefinedQuarters = [
    [
        "{$year}-01-01",
        "{$year}-03-31",
    ],
    [
        "{$year}-04-01",
        "{$year}-06-30",
    ],
    [
        "{$year}-07-01",
        "{$year}-09-30",
    ],
    [
        "{$year}-10-01",
        "{$year}-12-31",
    ],
];
if($UseQuarters){
	$startDateIso = $DefinedQuarters[$Quarter-1][0];
	$endDateIso = $DefinedQuarters[$Quarter-1][1];
}

// 3. Prepare the statement
$stmt = $pdo->prepare($sql);

// 4. Define your parameter values in an indexed array
//$params    = [$generic, $startDateIso, $endDateIso];
$params    = [$generic, $startDateIso, $endDateIso];
//$params    = [$generic];

// 5. Execute with array binding and fetch all rows
$stmt->setFetchMode(PDO::FETCH_NUM);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// 6. Render results as an HTML table
if (!empty($rows)) {
	echo "<span>Report from $startDateIso to $endDateIso</span><br />\r\n";
	if($UseQuarters) echo "<span>Quarter requested: $Quarter</span><br />\r\n";
	echo '<table border="2" cellpadding="5" cellspacing="0">';
	echo '<thead><tr>';
	// Header row
	//foreach (array_keys($rows[0]) as $colName) {
	//	echo '<th>' . htmlspecialchars($colName) . '</th>';
	//}
	foreach (['date', 'sold', 'cumulative', 'purchase', 'cumulative', 'received invoice no.', 'verified physical', 'stock in hand'] as $colname) {
		echo '<th>' . htmlspecialchars($colname) . '</th>';
	}
	echo '</tr></thead><tbody>';
	// Data rows
	$needHeader = false;

	foreach ($rows as $row) {
		//var_dump($row[0]);
		//var_dump($row[1]);
		if (
			isset($row[0]) && is_numeric($row[0]) && trim($row[0]) != "" ||
			isset($row[1]) && is_numeric($row[1]) && trim($row[1]) != ""

		) {
			//Vypecho '<tr style="background: #9aa; position: sticky; top: 25px">';
			echo '<tr style="background: #cee !important; font-weight: 700; print-color-adjust: exact;">';
			for ($i = 0; $i < 4; $i++) {
				echo '<td style="" colspan="2">CODE: ' . htmlspecialchars($row[$i]) . '</td>';
			}
			echo '</tr>';
		}

		echo '<tr>';
		foreach (array_slice($row, 4) as $cell) {
			echo '<td>' . htmlspecialchars($cell) . '</td>';
		}
		echo '</tr>';
	}


	echo '</tbody></table>';
} else {
	echo '<p>No records found between '
		. htmlspecialchars($startDateIso)
		. ' and '
		. htmlspecialchars($endDateIso)
		. '.</p>';
}

