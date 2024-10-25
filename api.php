<?php

header('Content-Type: application/json');

header('Cache-Control: no-cache'); // Ensure that the response is not cached


$secrets = require 'secrets.php';
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



$apiKeyCourses = $secrets['API_KEY_COURSES'];
$apiBib = $secrets['API_BIB'];

$apiResult = searchAlmaSruApi($record, $apiKeyCourses, $apiBib);

if (!empty($apiResult)) {

    $result = $apiResult;

} else {

    $result[] = [

        'Title' => 'Wrong format for ' . ($record['Title - Input'] ?? ''),

        'Author Last' => 'Wrong format for ' . ($record['Author Last - Input'] ?? ''),

        'Contributor' => 'Wrong format for ' . ($record['Contributor - Input'] ?? ''),

        'Year' => 'Wrong format for ' . urlencode($record['Year - Input'] ?? ''),

        'course_code' => urlencode($record['Course Number - Input'] ?? ''),

        'Format' => 'Wrong format for ' . ($record['Format - Input'] ?? ''),

    ];
	
	    foreach ($row as $key => $value) {

			if (!array_key_exists($key, $results[array_key_last($results)])) {

				$results[array_key_last($results)][$key] = $value;

			}

		}

	
	

}



echo json_encode($result);

exit;



function searchAlmaSruApi($row, $apiKeyCourses, $apiBib) {

    $title = $row['Title - Input'] ?? '';

    $authorFirst = $row['Author First - Input'] ?? '';

    $authorLast = $row['Author Last - Input'] ?? '';

    $contributorFirst = $row['Contributor First - Input'] ?? '';

    $contributorLast = $row['Contributor Last - Input'] ?? '';

    $publisher = $row['Publisher - Input'] ?? '';

    $year = urlencode($row['Year - Input'] ?? '');
	

		
	$format = $row['Format - Input'];
	
	


    $course_number = urlencode($row['Course Number - Input'] ?? '');

    $course_semester = urlencode($row['Course Semester - Input'] ?? '');

    $instructor = urlencode($row['Instructor Last Name - Input'] ?? '');

    // if ($instructor = "") {
    //     unset($instructor)
    // }



    $query = "https://tufts.alma.exlibrisgroup.com/view/sru/01TUN_INST?version=1.2&operation=searchRetrieve&recordSchema=marcxml";

    $query .= "&query=alma.mms_tagSuppressed=false";

    if (!empty($title)) {






		//Remove punctuation marks ,:;. and "\
		$title = preg_replace('/[,:;."\'\“]/', ' ', $title);

		//Replace hyphens with spaces
		//$title = preg_replace('/[-]/', ' ', $title);

		//Replace ampersands with spaces
		$title = preg_replace('/[&]/', ' ', $title);



        $query .= "%20AND%20alma.title==%22*" . urlencode($title) . "*%22";
		
		

    }
	
	else {
		
		$results[] = [

			'Title' => 'No results for ' . ($title ?? ''),

			'Author' => 'No results for ' . ($authorLast ?? ''),

			'Publisher' => 'No results for ' . ($publisher ?? ''),

			'Year' => 'No results for ' . ($year ?? ''),

			'course_code' => $jsonCourse['course'][0]['code'] ?? '',

			'Returned Format' => 'N/A'

		];
		
		foreach ($row as $key => $value) {

			if (!array_key_exists($key, $results[array_key_last($results)])) {

				$results[array_key_last($results)][$key] = $value;

			}

		}




		echo json_encode($results);

		exit;
	}
   if (!empty($authorLast)) {

    if(!empty($authorFirst)){

        $query .= "%20AND%20alma.creator=%22*" . urlencode($authorLast . "," . $authorFirst) . "*%22";

    }

    else{
        $query .= "%20AND%20alma.creator=%22*" . urlencode($authorLast) . "*%22";


    }

}

    // // Handle 'and' in the author string
    // if (strpos($author, 'and') !== false) {
    //     $pattern = '/^(.+?)(?=and).+?$/i';
    //     $replacement = '$1';
    //     $author = preg_replace($pattern, $replacement, $author);
    // }

    // // Remove titles and roles like 'trans.', 'ed.', 'eds.'
    // if (strpos($author, 'trans.') !== false || strpos($author, 'ed.') !== false || strpos($author, 'eds.') !== false) {
    //     $pattern = '/(ed\.\s*|trans\.\s*|eds\.\s*)/i';
    //     $replacement = '';
    //     $author = preg_replace($pattern, $replacement, $author);
    // }

    // // Check if the author string contains a comma
    // if (strpos($author, ',') !== false) {
    //     // Clean up the format "${last},*${first}"
    //     $pattern = '/^([^,]+),\s*([^,]+).*$/i';
    //     $replacement = '${1}*${2}';
    //     $author = preg_replace($pattern, $replacement, $author);
    // } else {
    //     // Transpose the author name if no comma is present
    //     $pattern = '/^([^ ]+)\s+([^ ]+).*$/i';
    //     $replacement = '${2},*${1}';
    //     $author = preg_replace($pattern, $replacement, $author);
    // }

    // Build the query string
   // ?$query .= "%20AND%20alma.creator=%22*" . urlencode($author) . "*%22";


   if (!empty($contributorLast)) {

    if(!empty($contributorFirst)){

        $query .= "%20AND%20alma.creator=%22*" . urlencode($contributorLast . "," . $contributorFirst) . "*%22";

    }

    else{
        $query .= "%20AND%20alma.creator=%22*" . urlencode($contributorLast) . "*%22";


    }

}

    // if (!empty($contributor)) {

    //     if (strpos($contributor, 'and') !== false) {

    //         $pattern = '/^([^,]+).+?and.+?$/i';

    //         $replacement = '$1';

    //         $contributor = preg_replace($pattern, $replacement, $contributor);

    //     }

    //     if (strpos($contributor, 'trans.') !== false || strpos($contributor, 'ed.') !== false || strpos($contributor, 'eds.') !== false) {

    //         $pattern = '/(ed.\s*|trans\.\s*|eds\.\s*)(.+)/i';

    //         $replacement = '$2';

    //         $contributor = preg_replace($pattern, $replacement, $contributor);

    //     }

    //     if (strpos($contributor, ',') !== false) {

    //         $pattern = '/^([^,]+),\s*([^,]+).*$/i';

    //         $replacement = '${1}*${2}'; 

    //         $contributor = preg_replace($pattern, $replacement, $contributor);

    //         //$author = implode(' ', array_reverse(preg_split('/,\s*/', $author$

    //     }
		

		
	// 	else{
	// 		$pattern = '/^([^ ]+)\s+([^ ]+).*$/i';
	// 		$replacement = '${2},*${1}'; 
	// 		$contributor = preg_replace($pattern, $replacement, $contributor);
	// 	}

    //     $query .= "%20AND%20alma.creator=%22*" . urlencode($contributor) . "*%22";

    // }

    if (!empty($year)) {

        $query .= "%20AND%20%28alma.main_public_date=%22" . $year . "%22%20OR%20alma.date_of_publication=%22" . $year . "%22%29";

    }
	
	
	


	


    error_log($query);
    $response = file_get_contents($query);
	
	// Gets rid of all namespace definitions 
	$xml_string = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $response);

	// Gets rid of all namespace references
	$xml_string = preg_replace('/[a-zA-Z]+:([a-zA-Z]+[=>])/', '$1', $xml_string);




    $results = [];



    $courseURL = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/courses?";



    if (!empty($course_number) && !empty($course_semester)) {
        if ($instructor != ""){
            $request_url = $courseURL . "apikey=" . $apiKeyCourses . "&q=name~" . $course_semester . "-" . $course_number . "%20AND%20instructors~" . $instructor . "&format=json";

        }

        else{
            $request_url = $courseURL . "apikey=" . $apiKeyCourses . "&q=name~" . $course_semester . "-" . $course_number . "&format=json";


        }

        error_log($request_url);
        $responseCourse = file_get_contents($request_url);

        $jsonCourse = json_decode($responseCourse, true);

        foreach($jsonCourse['course'] as $course){

            $instructors = [];
                    foreach ($course['instructor'] as $instructor1) {
                        $instructors[] = $instructor1['last_name'];
                    }
                    
                    $instructorString = implode(';', $instructors);
              

if ($xml_string === false) {
    die('Error loading XML.');
}

// Convert XML to JSON


$document = new DOMDocument();
$document->loadXml($xml_string);
$xpath = new DOMXpath($document);
//$records = $xml->records->record->recordData->record;

$records = $xpath->query("//record");

if (count($records) > 0) {
	$barcode_array = [];
	$electronic_record_array = [];
    foreach ($records as $record) {
        // Log the entire <record> element for debugging
    
			
			
			if ($format == "" || $format == "Physical" || $format == "physical"){
			
            if (!empty($xpath->query("//datafield[@tag='AVA']")->item(0)->nodeValue)) {
					   
							

                $phys_mms_id = $xpath->query("//datafield[@tag='AVA']/subfield[@code='0']")->item(0)->nodeValue;


		

                $itemQuery = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/$phys_mms_id/holdings/ALL/items?apikey=$apiBib&format=json";

				
                $responseItems = file_get_contents($itemQuery);

                $items = json_decode($responseItems, true);

			
                foreach ($items['item'] as $item) {
				

                if ((int)$items['total_record_count'] > 0) {
					
					

                    
						
						$library = "";
						$location = "";
						
						
						if (isset($item['holding_data']['in_temp_location']) && $item['holding_data']['in_temp_location'] == true){
								$library = $item['holding_data']['temp_library']['desc'];
								$location = $item['holding_data']['temp_location']['desc'];
						}
						
						else {
							$library = $item['item_data']['library']['desc'];
							$location = $item['item_data']['location']['desc'];
							
						}
						

						if (in_array($item['item_data']['barcode'], $barcode_array)){
							continue;
						}
						
						else{
								array_push($barcode_array, $item['item_data']['barcode']);
							
						}
						
						//foreach($barcode_array as $barcode){
						
						
						
					
						if (isset($xpath->query("//datafield[@tag='100']/subfield[@code='a']")->item(0)->nodeValue)){
						$author = $xpath->query("//datafield[@tag='100']/subfield[@code='a']")->item(0)->nodeValue;
						
						}
						
						else if (isset($xpath->query("//datafield[@tag='700']/subfield[@code='a']")->item(0)->nodeValue)){
							$author = $xpath->query("//datafield[@tag='700']/subfield[@code='a']")->item(0)->nodeValue;
						}
						
						else if (isset($xpath->query("//datafield[@tag='700']/subfield[@code='a']")->item(0)->nodeValue)){
							$author = $xpath->query("//datafield[@tag='700']/subfield[@code='a']")->item(0)->nodeValue;
						}
						
						else if (isset($xpath->query("//datafield[@tag='700']/subfield[@code='a']")->item(0)->nodeValue)){
							$author = $xpath->query("//datafield[@tag='710']/subfield[@code='a']")->item(0)->nodeValue;
						}

                        $results[] = [

                            'Title' => $xpath->query("//datafield[@tag='245']/subfield[@code='a']")->item(0)->nodeValue . $xpath->query("//datafield[@tag='245']/subfield[@code='b']")->item(0)->nodeValue,

                            'Author' => $author,

                            'Publisher' => $xpath->query("//datafield[@tag='264']/subfield[@code='b']")->item(0)->nodeValue,

                            'Year' => $xpath->query("//datafield[@tag='264']/subfield[@code='c']")->item(0)->nodeValue,

                            'MMS ID' => $phys_mms_id,

                            'ISBN' => $xpath->query("//datafield[@tag='020']/subfield[@code='a']")->item(0)->nodeValue,

                            'Course Name' => $course['name'] ?? '',
                            
                            'course_code' => $course['code'] ?? '',

                            'course_section' => $course['section'] ?? '',

                            'Course Instructor' => $instructorString,
							
							'Library' => $library ?? '',
							
							'Location' => $location ?? '',

                            'Call Number' => $item['holding_data']['permanent_call_number'] ?? '',

                            'Barcode' => $item['item_data']['barcode'] ?? '',

                            'Description' => json_encode($item['item_data']['description'], true) ?? '',

                            'Returned Format' => 'Physical'

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
			
			}
			
			
			

			if ($format == "" || $format == "Electronic" || $format == "electronic"){

            if (!empty($xpath->query("//datafield[@tag='AVE']")->item(0)->nodeValue)) {
				


                $e_mms_id = $xpath->query("//datafield[@tag='AVE']/subfield[@code='0']")->item(0)->nodeValue;

				if (in_array($e_mms_id . "Electronic", $electronic_record_array)){
							continue;
				}
				
				else{
						array_push($electronic_record_array, $e_mms_id . "Electronic");
					
				}
				if (isset($xpath->query("//datafield[@tag='100']/subfield[@code='a']")->item(0)->nodeValue)){
						$author = $xpath->query("//datafield[@tag='100']/subfield[@code='a']")->item(0)->nodeValue;
						
					}
					
					else if (isset($xpath->query("//datafield[@tag='110']/subfield[@code='a']")->item(0)->nodeValue)){
						$author = $xpath->query("//datafield[@tag='110']/subfield[@code='a']")->item(0)->nodeValue;
					}
					
					else if (isset($xpath->query("//datafield[@tag='700']/subfield[@code='a']")->item(0)->nodeValue)){
						$author = $xpath->query("//datafield[@tag='700']/subfield[@code='a']")->item(0)->nodeValue;
					}
					
					else if (isset($xpath->query("//datafield[@tag='710']/subfield[@code='a']")->item(0)->nodeValue)){
						$author = $xpath->query("//datafield[@tag='710']/subfield[@code='a']")->item(0)->nodeValue;
					}
                $results[] = [

                    'Title' => $xpath->query("//datafield[@tag='245']/subfield[@code='a']")->item(0)->nodeValue . $xpath->query("//datafield[@tag='245']/subfield[@code='b']")->item(0)->nodeValue,

					
                    'Author' => $author,

                    'Publisher' => $xpath->query("//datafield[@tag='264']/subfield[@code='b']")->item(0)->nodeValue,

                    'Year' => $xpath->query("//datafield[@tag='264']/subfield[@code='c']")->item(0)->nodeValue,

                    'MMS ID' => $e_mms_id,

                    'ISBN' => $xpath->query("//datafield[@tag='020']/subfield[@code='a']")->item(0)->nodeValue,

                    'Course Name' => $course['name'] ?? '',
                    
                    'course_code' =>  $course['code'] ?? '',

                    'course_section' =>  $course['section'] ?? '',

                    'Course Instructor' =>  $instructorString,

                    'Returned Format' => 'Electronic'

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

    } else {
		
		

        $results[] = [

            'Title' => 'No results for ' . ($title ?? ''),

            'Author' => 'No results for ' . ($author ?? ''),

            'Publisher' => 'No results for ' . ($publisher ?? ''),

            'Year' => 'No results for ' . ($year ?? ''),

            'course_code' => $jsonCourse['course'][0]['code'] ?? '',

            'Returned Format' => 'N/A'

        ];



        // Copy the fields that were passed into the input that aren’t used in processing for return

		foreach ($row as $key => $value) {

			if (!array_key_exists($key, $results[array_key_last($results)])) {

				$results[array_key_last($results)][$key] = $value;

			}

		}

    }
	
}
} else{
	$results[] = [

            'Title' => 'No course for ' . ($title ?? ''),

            'Author' => 'No course for ' . ($author ?? ''),

            'Publisher' => 'No course for ' . ($publisher ?? ''),

            'Year' => 'No course for ' . ($year ?? ''),

            'course_code' =>  $course_number . "-" . $course_semester . "-" . $instructor ?? '',

            'Returned Format' => 'N/A'

        ];
		
		foreach ($row as $key => $value) {

			if (!array_key_exists($key, $results[array_key_last($results)])) {

				$results[array_key_last($results)][$key] = $value;

			}

		}

		
}


	

    return $results;

}

?>