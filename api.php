<?php
header('Content-Type: application/json');

ini_set('max_execution_time', 1000);

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON input']);
    error_log('JSON decode error: ' . json_last_error_msg());
    exit;
}

if (empty($data)) {
    echo json_encode(['error' => 'Empty input data']);
    error_log('Empty input data');
    exit;
}

$apiKeyPrimo = 'ENTER PRIMO API KEY HERE'; 
$apiKeyCourses = 'ENTER COURSE API KEY HERE';
$apiBib = "ENTER BIB API KEY HERE";

$results = [];
foreach ($data as $record) {
    $apiResult = searchPrimoApi($record, $apiKeyPrimo, $apiKeyCourses, $apiBib);
    if (!empty($apiResult)) {
        $results = array_merge($results, $apiResult);
    }

    else{

        $result[] = [
            'Title' => 'No results for ' . $record['Title'] ?? '',
            'Author' => 'No results for ' . $record['Author'] ?? '',
            'Contributor' => 'No results for ' . $record['Contributor'] ?? '',
            'Year' => 'No results for ' .  urlencode($record['Year'] ?? ''),
            'Course Code' => urlencode($record['Course Number'] ?? ''),
            'Format' => 'N/A'
        ];
        $results = array_merge($results, $result);



    }
}

echo json_encode($results);

function searchPrimoApi($row, $apiKey, $apiKeyCourses, $apiBib) {
    $title = $row['Title'] ?? '';
    $author = $row['Author'] ?? '';
    $contributor = $row['Contributor'] ?? '';
    $publisher = $row['Publisher'] ?? '';
    $year = urlencode($row['Year'] ?? '');
    $course_number = urlencode($row['Course Number'] ?? '');
    $course_semester = urlencode($row['Course Semester'] ?? '');
    $instructor = urlencode($row['Instructor Last Name'] ?? '');
    
    $query = "https://api-na.hosted.exlibrisgroup.com/primo/v1/search?q=";
    
    if (!empty($title)) {
        $title =  preg_replace('/([^\:]+).*/', '$1', $title);
        //$title = preg_replace('/\!\@\#\$\%\^\&\*\(\)\_\+\-\=\{\}\\\<\>\?\~\'/', ' ', $title);
        $title =  preg_replace('/\s{2,}/', ' ', $title);

        $query .= "title,contains," . urlencode($title);
    }
    if (!empty($author)) {
        if(strpos($author, 'and') !== false){
            $pattern = '/^([^,]+).+?and.+?$/i';
            $replacement = '$1';
            $author = preg_replace($pattern, $replacement, $author);

        }
        if(strpos($contributor, 'trans.') !== false || strpos($contributor, 'ed.') !== false || strpos($contributor, 'eds.') !== false ){
            $pattern = '/(ed.\s*|trans\.\s*)(.+)/i';
            $replacement = '$2';
            $author = preg_replace($pattern, $replacement, $author);

        }
        if (strpos($author, ',') !== false){

        
            
            $pattern = '/^([^,]+,\s*[^,]+$).*/i';
            $replacement = '${1}';
            $author =  preg_replace($pattern, $replacement, $author);
            $author = implode(' ', array_reverse(preg_split('/,\s*/', $author)));
        }
        $query .= ",AND;creator,contains," . urlencode($author);
    }
    if (!empty($contributor)) {
        if(strpos($contributor, 'and') !== false){
            $pattern = '/^([^,]+).+?and.+?$/i';
            $replacement = '$1';
            $contributor = preg_replace($pattern, $replacement, $contributor);

        }
        if(strpos($contributor, 'trans.') !== false || strpos($contributor, 'ed.') !== false || strpos($contributor, 'eds.') !== false ){
            $pattern = '/(ed.\s*|trans\.\s*)(.+)/i';
            $replacement = '$2';
            $contributor = preg_replace($pattern, $replacement, $contributor);

        }
        if (strpos($contributor, ',') !== false) {
            $pattern = '/([^,]+,\s*[^,]+)(.*)/i';
            $replacement = '${1}';
            $contributor =  preg_replace($pattern, $replacement, $contributor);
            $contributor = implode(' ', array_reverse(preg_split('/,\s*/', $contributor)));
        }
        $query .= ",AND;contributor,contains," . urlencode($contributor);
    }
    if (!empty($year)) {
        $query .= ",AND;cdate,contains," . $year;
    }
    $query .= "&apikey=$apiKey&vid=01TUN_INST:01TUN&lang=en&tab=Everything&scope=MyInst_and_CI&format=json";

    $response = file_get_contents($query);
    $json = json_decode($response, true);

    $results = [];
    
    $courseURL = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/courses?";
    
    if (!empty($course_number) && !empty($instructor) && !empty($course_semester)) {
        $request_url = $courseURL . "apikey=" . $apiKeyCourses . "&q=name~" . $course_semester . "-" . $course_number . "%20AND%20instructors~" . $instructor . "&format=json";
        $responseCourse = file_get_contents($request_url);
        $jsonCourse = json_decode($responseCourse, true);
    
    
    if (isset($json['docs']) && count($json['docs']) > 0) {
        $real_results = false;
        $mms_found = false;
        foreach ($json['docs'] as $doc) {
            if(!(isset($doc['pnx']['display']['mms']))){
                
                $mms_found = false;
                
           
            }

            else {

                
                $mms_found = true;
            if (isset($doc['pnx']['display']['mms']) && isset($doc['delivery']['bestlocation']) && $doc['delivery']['bestlocation'] != null) {
                $mms_id = $doc['pnx']['display']['mms'][0];
                $itemQuery = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/$mms_id/holdings/ALL/items?apikey=$apiBib&format=json";
                $responseItems = file_get_contents($itemQuery);
                $items = json_decode($responseItems, true);
                
                if ((int)$items['total_record_count'] > 0) {
                    foreach ($items as $item1) {
                        if (is_array($item1)){
                        foreach ($item1 as $item) {
                            $real_results = true;
                            $results[] = [
                                'Title' => $doc['pnx']['display']['title'][0] ?? '',
                                'Author' => $doc['pnx']['addata']['au'][0] ?? '',
                                'Contributor' => $doc['pnx']['addata']['addau'][0] ?? '',
                                'Publisher' => $doc['pnx']['display']['publisher'][0] ?? '',
                                'Year' => $doc['pnx']['addata']['date'][0] ?? '',
                                'MMS ID' => $doc['pnx']['display']['mms'][0] ?? '',
                                'ISBN' => $doc['pnx']['addata']['isbn'][0] ?? '',
                                'Version' => $doc['pnx']['display']['version'][0] ?? '',
                                'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
                                'Course Section' => $jsonCourse['course'][0]['section'] ?? '',
                                'Call Number' => $item['holding_data']['permanent_call_number'] ?? '',
                                'Barcode' => $item['item_data']['barcode'] ?? '',
                                'Description' => json_encode($item['item_data']['description'], true) ?? '',
                                'Format' => 'Physical'
                            ];
                        }

                        
                    }

                    
                    

  /*                    else{
                         $results[] = [
                             'Title' => $doc['pnx']['display']['title'][0] ?? '',
                             'Author' => $doc['pnx']['addata']['au'][0] ?? '',
                             'Contributor' => $doc['pnx']['addata']['addau'][0] ?? '',
                             'Publisher' => $doc['pnx']['display']['publisher'][0] ?? '',
                             'Year' => $doc['pnx']['addata']['date'][0] ?? '',
                             'MMS ID' => $doc['pnx']['display']['mms'][0] ?? '',
                             'ISBN' => $doc['pnx']['addata']['isbn'][0] ?? '',
                             'Version' => $doc['pnx']['display']['version'][0] ?? '',
                             'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
                             'Call Number' => $item1['holding_data']['permanent_call_number'] ?? '',
                             'Barcode' => $item1['item_data']['barcode'] ?? '',
                             'Description' => json_encode($item1['item_data']['description'], true) ?? '',
                             'Format' => 'Physical'
                         ];

                     }
                    */                    } 
                }

                if(isset($doc['pnx']['display']['dedupmemberids'])  && isset($doc['delivery']['bestlocation']) && $doc['delivery']['bestlocation'] != null){
                    $mms_id = $doc['pnx']['display']['dedupmemberids'][0];
                    $itemQuery = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/$mms_id/holdings/ALL/items?apikey=$apiBib&format=json";
                    $responseItems = file_get_contents($itemQuery);

                    $itemQuery = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/$mms_id/holdings/ALL/items?apikey=$apiBib&format=json";
                    $responseItems = file_get_contents($itemQuery);
                    $items = json_decode($responseItems, true);
                    
                    if ((int)$items['total_record_count'] > 0) {
                        foreach ($items as $item1) {
                            if (is_array($item1)){
                            foreach ($item1 as $item) {
                                $real_results = true;
                                $results[] = [
                                    'Title' => $doc['pnx']['display']['title'][0] ?? '',
                                    'Author' => $doc['pnx']['addata']['au'][0] ?? '',
                                    'Contributor' => $doc['pnx']['addata']['addau'][0] ?? '',
                                    'Publisher' => $doc['pnx']['display']['publisher'][0] ?? '',
                                    'Year' => $doc['pnx']['addata']['date'][0] ?? '',
                                    'MMS ID' => $doc['pnx']['display']['dedupmemberids'][0] ?? '',
                                    'ISBN' => $doc['pnx']['addata']['isbn'][0] ?? '',
                                    'Version' => $doc['pnx']['display']['version'][0] ?? '',
                                    'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
                                    'Course Section' => $jsonCourse['course'][0]['section'] ?? '',
                                    'Call Number' => $item['holding_data']['permanent_call_number'] ?? '',
                                    'Barcode' => $item['item_data']['barcode'] ?? '',
                                    'Description' => json_encode($item['item_data']['description'], true) ?? '',
                                    'Format' => 'Physical'
                                ];
                            }
    
                            
                        }
    
                        
                        
    
                        // else{
                        //     $results[] = [
                        //         'Title' => $doc['pnx']['display']['title'][0] ?? '',
                        //         'Author' => $doc['pnx']['addata']['au'][0] ?? '',
                        //         'Contributor' => $doc['pnx']['addata']['addau'][0] ?? '',
                        //         'Publisher' => $doc['pnx']['display']['publisher'][0] ?? '',
                        //         'Year' => $doc['pnx']['addata']['date'][0] ?? '',
                        //         'MMS ID' => $doc['pnx']['display']['mms'][0] ?? '',
                        //         'ISBN' => $doc['pnx']['addata']['isbn'][0] ?? '',
                        //         'Version' => $doc['pnx']['display']['version'][0] ?? '',
                        //         'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
                        //         'Call Number' => $item1['holding_data']['permanent_call_number'] ?? '',
                        //         'Barcode' => $item1['item_data']['barcode'] ?? '',
                        //         'Description' => json_encode($item1['item_data']['description'], true) ?? '',
                        //         'Format' => 'Physical'
                        //     ];
    
                        // }
                        }
                    }


                            
                }

               
               
            }
            
            if (isset($doc['pnx']['display']['mms_id']) && isset($doc['delivery']['link'][0]['linkURL'])) {
                $real_results = true;
                $results[] = [
                    'Title' => $doc['pnx']['display']['title'][0] ?? '',
                    'Author' => $doc['pnx']['addata']['au'][0] ?? '',
                    'Contributor' => $doc['pnx']['addata']['addau'][0] ?? '',
                    'Publisher' => $doc['pnx']['display']['publisher'][0] ?? '',
                    'Year' => $doc['pnx']['addata']['date'][0] ?? '',
                    'MMS ID' => $doc['pnx']['display']['mms'][0] ?? '',
                    'ISBN' => $doc['pnx']['addata']['isbn'][0] ?? '',
                    'URL' => json_encode($doc['delivery']['link'][0]['linkURL']) ?? '',
                    'Version' => $doc['pnx']['display']['version'][0] ?? '',
                    'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
                    'Course Section' => $jsonCourse['course'][0]['section'] ?? '',
                    'Format' => 'Electronic'];
            }
            else {
                $real_results = true;
                $results[] = [
                    'Title' => $doc['pnx']['display']['title'][0] ?? '',
                    'Author' => $doc['pnx']['addata']['au'][0] ?? '',
                    'Contributor' => $doc['pnx']['addata']['addau'][0] ?? '',
                    'Publisher' => $doc['pnx']['display']['publisher'][0] ?? '',
                    'Year' => $doc['pnx']['addata']['date'][0] ?? '',
                    'MMS ID' => $doc['pnx']['display']['mms'][0] ?? '',
                    'ISBN' => $doc['pnx']['addata']['isbn'][0] ?? '',
                    'URL' => json_encode($doc['delivery']['link'][0]['linkURL']) ?? '',
                    'Version' => $doc['pnx']['display']['version'][0] ?? '',
                    'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
                    'Course Section' => $jsonCourse['course'][0]['section'] ?? '',
                    'Format' => 'Electronic'
                ];
            }
        }
            

            
        }

    if($mms_found == false && $real_results = false){
            
        $results[] = [
            'Title' => "No MMS ID for " . $doc['pnx']['display']['title'][0] ?? '',
            'Author' => "No MMS ID for " . $doc['pnx']['addata']['au'][0] ?? '',
            'Contributor' => $doc['pnx']['addata']['addau'][0] ?? '',
            'Publisher' => "No MMS ID for " . $doc['pnx']['display']['publisher'][0] ?? '',
            'Year' => "No MMS ID for " . $doc['pnx']['addata']['date'][0] ?? '',
            'MMS ID' => "No MMS ID for " . $doc['pnx']['display']['mms'][0] ?? '',
            'ISBN' => "No MMS ID for " . $doc['pnx']['addata']['isbn'][0] ?? '',
            'Version' => "No MMS ID for " . $doc['pnx']['display']['version'][0] ?? '',
            'Course Code' => "No MMS ID for " . $jsonCourse['course'][0]['code'] ?? '',
            'Course Section' => $jsonCourse['course'][0]['section'] ?? '',
            'Format' => "No MMS ID for " . 'Unknown'
        ];
        
        
    
    }
     }

    else {
        $results[] = [
            'Title' => 'No results for ' . $title ?? '',
            'Author' => 'No results for ' . $author ?? '',
            'Contributor' => 'No results for ' . $contributor ?? '',
            'Publisher' => 'No results for ' . $publisher ?? '',
            'Year' => 'No results for ' . $year ?? '',
            'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
            'Format' => 'N/A'
        ];
    }

    return $results;
}
}

?>
