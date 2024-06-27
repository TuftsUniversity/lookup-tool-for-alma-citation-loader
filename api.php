<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache'); // Ensure that the response is not cached

ini_set('max_execution_time', 1000);

//if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
	$apiKeyPrimo = 'l8xxecdeb5501901468b9cb2880ff16a3be6'; 
	$apiKeyCourses = 'l7xxde379ecb50e14de0959be6c41c1f6888';
	$apiBib = "l8xxa16e86330d384b0c9171da72a6536dc8";

	$apiResult = searchPrimoApi($record, $apiKeyPrimo, $apiKeyCourses, $apiBib);
    if (!empty($apiResult)) {
        $result = $apiResult;
    }
	
	echo json_encode($result);
    exit;
	
	
/* }

    else{

        $result[] = [
            'Title' => 'No results for ' . $record['Title'] ?? '',
            'Author' => 'No results for ' . $record['Author'] ?? '',
            'Contributor' => 'No results for ' . $record['Contributor'] ?? '',
            'Year' => 'No results for ' .  urlencode($record['Year'] ?? ''),
            'Course Code' => urlencode($record['Course Number'] ?? ''),
            'Format' => 'N/A'
        ];
    // Process the record (replace with actual processing logic)
    

    echo json_encode($result);
    exit;
} */



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
        if(strpos($author, ';') !== false){
            $pattern = '/^([^;]+).+?;.+?$/i';
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
			if($doc['context'] == "PC"){
					continue;
			}
            if(!(isset($doc['pnx']['display']['mms']))){
                
                $mms_found = false;
                continue;
           
            }

            else {

                
                $mms_found = true;
            if (isset($doc['delivery']['bestlocation']['ilsApiId'])) {
                $phys_mms_id = $doc['delivery']['bestlocation']['ilsApiId'];
				$phys_mms_id = trim($phys_mms_id);
                $itemQuery = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/$phys_mms_id/holdings/ALL/items?apikey=$apiBib&format=json";
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
                                'MMS ID' => $phys_mms_id  ?? '',
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

/*                 if(isset($doc['pnx']['display']['dedupmemberids'])  && isset($doc['delivery']['bestlocation']) && $doc['delivery']['bestlocation'] != null){
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


                            
                } */

               
               
            }
            
            if (isset($doc['delivery']['link'][0]['linkURL'])) {
				$delivery_category_array = $doc['delivery']['deliveryCategory'];
				$e_mms_id = "";
				//if (count($delivery_category_array) > 1){
					//if (in_array("Alma-E", $delivery_category_array)){
				if(isset($doc['pnx']['display']['relation'][0]) && preg_match('/^.+?\$\$Z([0-9]+).+$/', $doc['pnx']['display']['relation'][0]) == 1 && !(in_array('Alma-E', $delivery_category_array))){
					$e_mms_id = preg_replace('/^.+?\$\$Z([0-9]+).+$/', '$1', $doc['pnx']['display']['relation'][0]);
				}
				
						//else{
						//	$e_mms_id = "NEED E MMS ID";
						//}
					//}
				//}
				
				else {
					$e_mms_id = $doc['pnx']['display']['mms'][0];	
				}
				
				
                $real_results = true;
				$e_mms_id = trim($e_mms_id);
                $results[] = [
                    'Title' => $doc['pnx']['display']['title'][0] ?? '',
                    'Author' => $doc['pnx']['addata']['au'][0] ?? '',
                    'Contributor' => $doc['pnx']['addata']['addau'][0] ?? '',
                    'Publisher' => $doc['pnx']['display']['publisher'][0] ?? '',
                    'Year' => $doc['pnx']['addata']['date'][0] ?? '',
                    'MMS ID' => $e_mms_id ?? '',
                    'ISBN' => $doc['pnx']['addata']['isbn'][0] ?? '',
                    'URL' => json_encode($doc['delivery']['link'][0]['linkURL']) ?? '',
                    'Version' => $doc['pnx']['display']['version'][0] ?? '',
                    'Course Code' => $jsonCourse['course'][0]['code'] ?? '',
                    'Course Section' => $jsonCourse['course'][0]['section'] ?? '',
                    'Format' => 'Electronic'];
            }
           /*  else {
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
                ]; */
            //}
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
	
	//error_log(print_r($results, true));

    return $results;
}
}

?>