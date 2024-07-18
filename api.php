<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache'); // Ensure that the response is not cached

ini_set('max_execution_time', 1000);

$input = file_get_contents('php://input');
$record = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON input']);
    error_log('JSON decode error: ' . json_last_error_msg());
    exit;
}

if (empty($record)) {
    echo json_encode(['error' => 'Empty input data']);
    error_log('Empty input data');
    exit;
}

$apiKeyCourses = '***REMOVED***';
$apiBib = "***REMOVED***";

$apiResult = searchAlmaSruApi($record, $apiKeyCourses, $apiBib);
if (!empty($apiResult)) {
    $result = $apiResult;
} else {
    $result[] = [
        'Title' => 'No results for ' . ($record['Title'] ?? ''),
        'Author' => 'No results for ' . ($record['Author'] ?? ''),
        'Contributor' => 'No results for ' . ($record['Contributor'] ?? ''),
        'Year' => 'No results for ' . urlencode($record['Year'] ?? ''),
        'Course Code' => urlencode($record['Course Number'] ?? ''),
        'Format' => 'N/A'
    ];
}

echo json_encode($result);
exit;

function searchAlmaSruApi($row, $apiKeyCourses, $apiBib) {
    $title = $row['Title'] ?? '';
    $author = $row['Author'] ?? '';
    $contributor = $row['Contributor'] ?? '';
    $publisher = $row['Publisher'] ?? '';
    $year = urlencode($row['Year'] ?? '');
    $course_number = urlencode($row['Course Number'] ?? '');
    $course_semester = urlencode($row['Course Semester'] ?? '');
    $instructor = urlencode($row['Instructor Last Name'] ?? '');

    $query = "https://tufts.alma.exlibrisgroup.com/view/sru/01TUN_INST?version=1.2&operation=searchRetrieve&recordSchema=marcxml";

    if (!empty($title)) {
        $title = preg_replace('/([^\:]+).*/', '$1', $title);
        $title = preg_replace('/\s{2,}/', ' ', $title);
        $query .= "&query=alma.title=" . urlencode($title);
    }
    if (!empty($author)) {
        if (strpos($author, 'and') !== false) {
            $pattern = '/^([^,]+).+?and.+?$/i';
            $replacement = '$1';
            $author = preg_replace($pattern, $replacement, $author);
        }
        if (strpos($contributor, 'trans.') !== false || strpos($contributor, 'ed.') !== false || strpos($contributor, 'eds.') !== false) {
            $pattern = '/(ed.\s*|trans\.\s*)(.+)/i';
            $replacement = '$2';
            $author = preg_replace($pattern, $replacement, $author);
        }
        if (strpos($author, ',') !== false) {
            $pattern = '/^([^,]+,\s*[^,]+$).*/i';
            $replacement = '${1}';
            $author = preg_replace($pattern, $replacement, $author);
            $author = implode(' ', array_reverse(preg_split('/,\s*/', $author)));
        }
        $query .= "%20AND%20alma.creator=" . urlencode($author);
    }
    if (!empty($contributor)) {
        if (strpos($contributor, 'and') !== false) {
            $pattern = '/^([^,]+).+?and.+?$/i';
            $replacement = '$1';
            $contributor = preg_replace($pattern, $replacement, $contributor);
        }
        if (strpos($contributor, 'trans.') !== false || strpos($contributor, 'ed.') !== false || strpos($contributor, 'eds.') !== false) {
            $pattern = '/(ed.\s*|trans\.\s*)(.+)/i';
            $replacement = '$2';
            $contributor = preg_replace($pattern, $replacement, $contributor);
        }
        if (strpos($contributor, ',') !== false) {
            $pattern = '/([^,]+,\s*[^,]+)(.*)/i';
            $replacement = '${1}';
            $contributor = preg_replace($pattern, $replacement, $contributor);
            $contributor = implode(' ', array_reverse(preg_split('/,\s*/', $contributor)));
        }
        $query .= "%20AND%20alma.contributor=" . urlencode($contributor);
    }
    if (!empty($year)) {
        $query .= "%20AND%20alma.date=" . $year;
    }

    $response = file_get_contents($query);
    $xml = simplexml_load_string($response);

    $results = [];

    $courseURL = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/courses?";

    if (!empty($course_number) && !empty($instructor) && !empty($course_semester)) {
        $request_url = $courseURL . "apikey=" . $apiKeyCourses . "&q=name~" . $course_semester . "-" . $course_number . "%20AND%20instructors~" . $instructor . "&format=json";
        $responseCourse = file_get_contents($request_url);
        $jsonCourse = json_decode($responseCourse, true);
    }

    if ($xml->records->record->count() > 0) {
        foreach ($xml->records->record as $record) {
            if ($record->xpath("//datafield[@tag='AVA']")->count() > 0) {
                $phys_mms_id = (string)$record->xpath("//controlfield[@tag='001']")[0];

                $itemQuery = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/$phys_mms_id/holdings/ALL/items?apikey=$apiBib&format=json";
                $responseItems = file_get_contents($itemQuery);
                $items = json_decode($responseItems, true);

                if ((int)$items['total_record_count'] > 0) {
                    foreach ($items['item'] as $item) {
                        $results[] = [
                            'Title' => (string)$record->xpath("//datafield[@tag='245']/subfield[@code='a']")[0],
                            'Author' => (string)$record->xpath("//datafield[@tag='100']/subfield[@code='a']")[0],
                            'Publisher' => (string)$record->xpath("//datafield[@tag='264']/subfield[@code='b']")[0],
                            'Year' => (string)$record->xpath("//datafield[@tag='264']/subfield[@code='c']")[0],
                            'MMS ID' => $phys_mms_id,
                            'ISBN' => (string)$record->xpath("//datafield[@tag='020']/subfield[@code='a']")[0],
                            'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
                            'Course Section' => $jsonCourse['course'][0]['section'] ?? '',
                            'Call Number' => $item['holding_data']['permanent_call_number'] ?? '',
                            'Barcode' => $item['item_data']['barcode'] ?? '',
                            'Description' => json_encode($item['item_data']['description'], true) ?? '',
                            'Format' => 'Physical'
                        ];

                        // Copy the fields that were passed into the input that aren’t used in processing for return
                        foreach ($row as $key => $value) {
                            if (!array_key_exists($key, $results[array_key_last($results)])) {
                                $results[array_key_last($results)][$key] = $value;
                            }
                        }
                    }
                }
            }

            if ($record->xpath("//datafield[@tag='AVE']")->count() > 0) {
                $e_mms_id = (string)$record->xpath("//controlfield[@tag='001']")[0];

                $results[] = [
                    'Title' => (string)$record->xpath("//datafield[@tag='245']/subfield[@code='a']")[0],
                    'Author' => (string)$record->xpath("//datafield[@tag='100']/subfield[@code='a']")[0],
                    'Publisher' => (string)$record->xpath("//datafield[@tag='264']/subfield[@code='b']")[0],
                    'Year' => (string)$record->xpath("//datafield[@tag='264']/subfield[@code='c']")[0],
                    'MMS ID' => $e_mms_id,
                    'ISBN' => (string)$record->xpath("//datafield[@tag='020']/subfield[@code='a']")[0],
                    'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
                    'Course Section' => $jsonCourse['course'][0]['section'] ?? '',
                    'Format' => 'Electronic'
                ];

                // Copy the fields that were passed into the input that aren’t used in processing for return
                foreach ($row as $key => $value) {
                    if (!array_key_exists($key, $results[array_key_last($results)])) {
                        $results[array_key_last($results)][$key] = $value;
                    }
                }
            }
        }
    } else {
        $results[] = [
            'Title' => 'No results for ' . ($title ?? ''),
            'Author' => 'No results for ' . ($author ?? ''),
            'Publisher' => 'No results for ' . ($publisher ?? ''),
            'Year' => 'No results for ' . ($year ?? ''),
            'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
            'Format' => 'N/A'
        ];

        // Copy the fields that were passed into the input that aren’t used in processing for return
        foreach ($row as $key => $value) {
            if (!array_key_exists($key, $results[array_key_last($results)])) {
                $results[array_key_last($results)][$key] = $value;
            }
        }
    }

    return $results;
}
?>
