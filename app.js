function citationsValid(citations) {
    try {
        JSON.parse(citations);
    } catch (e) {
        return false;
    }
    return true;
}

async function handleUpload() {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
	
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const data = e.target.result;
			
            const progressBar = document.getElementById('progressBar');
    		let isValid = true;
			courseData = [];
			
            const workbook = XLSX.read(data, {type: 'binary'});
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            const json = XLSX.utils.sheet_to_json(worksheet);
			console.log(JSON.stringify(json));
            let existingKeys = [];

            // Collect all existing keys
            json.forEach(row => {

                for (let key in row) {
                    existingKeys.push(key);
                }
            });

			var totalCitations = json.length;
            json.forEach(row => {
            

            
				console.log(JSON.stringify(row));
            
			const authorFirst = row['Author First'];
            const authorLast = row['Author Last'];
			const contributorFirst = row['Contributor First'];
            const contributorLast = row['Contributor Last'];
			const title = row['Title'];
			const publisher = row['Publisher'];
			const year = row['Year'];
			const course_number = row['Course Number'];
			const course_semester = row['Course Semester'];
            const instructor = row['Instructor Last Name'];
            

			if (row['Format'] != undefined){
				format = row['Format'];
                row['Format - Input'] = row['Format']
                delete row['Format']
			}
			
			else if (row['format'] != undefined){
				format = row['format'];
                row['Format - Input'] = row['format']
                delete row['format']
			}
			
			else {
				format = ""
			}

		    console.log(format);
            row['Author First - Input'] = row['Author First']  
            delete row['Author First']

            row['Author Last - Input'] = row['Author Last']  
            delete row['Author Last']

            row['Contributor First - Input'] = row['Contributor First']  
            delete row['Contributor First']

            row['Contributor Last - Input'] = row['Contributor Last']  
            delete row['Contributor Last']

            row['Title - Input'] = row['Title']  
            delete row['Title']

            row['Publisher - Input'] = row['Publisher']  
            delete row['Publisher']

            row['Year - Input'] = row['Year']  
            delete row['Year']

            row['Course Number - Input'] = row['Course Number']  
            delete row['Course Number']

            row['Instructor Last Name - Input'] = row['Instructor Last Name']  
            delete row['Instructor Last Name']

            row['Course Semester - Input'] = row['Course Semester']  
            delete row['Course Semester']

            
            

                
            
			var c_row = {
            "Author First - Input": authorFirst,
            "Author Last - Input": authorLast,
            "Contributor First - Input": contributorFirst,
            "Contributor Last - Input": contributorLast,
            "Title - Input": title,
            "Publisher - Input": publisher,
            "Year - Input": year,
            "Course Number - Input": course_number,
            "Course Semester - Input": course_semester,
            "Instructor Last Name - Input": instructor,
            "Format - Input": format
            
        };
        	
        
                        // Add any extra columns
              for (var k in row){
				
					  
			
				if(!(c_row.hasOwnProperty(k)) || (!(c_row.hasOwnProperty()))){
				if (row.hasOwnProperty(k)) {
					
					c_row[k] = row[k];
					
				}
				}
		
			  }
			courseData.push(c_row);
        
			if (!isValid) return;

/*     		courseData['original_title']= title;
            courseData['original_author'] = author;
            courseData['original_contributor'] = contributor;
            courseData['original_publisher'] = publisher;
            courseData['original_year'] = year; */
			});
            
			let results = [];
			
    		let processedCitations = 0;
			return courseData.reduce((promise, row) => {
				return promise.then(() => {
/*             return row.parsedCitations.reduce((citationPromise, citation) => { */
					return promise.then(() => {
				
					const flattenedCitation = {
					'Course Number - Input': row['Course Number - Input'],
					'Instructor Last Name - Input': row['Instructor Last Name - Input'],
					'Course Semester - Input': row['Course Semester - Input'],
					'Title - Input': row['Title - Input'] || '',
					'Author First - Input': row['Author First - Input'] || '',
                    'Author Last - Input': row['Author Last - Input'] || '',
					'Contributor First - Input': row['Contributor First - Input'] || '',
                    'Contributor Last - Input': row['Contributor Last - Input'] || '',
					'Publisher - Input': row['Publisher - Input'] || '',
					'Year - Input': row['Year - Input'] || '',
					'Format': format || '',
				  
					
					
					};
					
					
					                        // Add any extra columns
											
			  
              for (var k in row){
				
					  
			
				if(!(flattenedCitation.hasOwnProperty(k))){
				if (row.hasOwnProperty(k)) {
					
					flattenedCitation[k] = row[k];
					
				}
				}
		
			  }
			
			
					
                
					
					return fetch('api.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(flattenedCitation)
				})
				.then(response => response.json())
				.then(result => {
					
					result.forEach(r => {results.push(r);});
					
					processedCitations++;
					progressBar.value = (processedCitations / totalCitations) * 100;
				})
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            }); }); });
/*             }, Promise.resolve()); */
        
    }, Promise.resolve()).then(() => {
        generateExcel(results);
    });
			
					
            
           
        
        
    }
		reader.readAsBinaryString(file);
        
	}
	
	else(alert("no file"));
	}


function generateExcel(results) {
	
		data = [];
        
        

        if (results != null && results != undefined && results.length > 1){
            results.forEach(result => {
                let title, author, contributor, publisher, date, mms_id, isbn, version, course_name, course_code, course_section, course_instructor, library, library_location, call_number, barcode, description, format;


				
                if (result['Title']) {
                    title = result['Title'];
                }

                if (result['Author']) {
                    author = result['Author'];
                }
                if (result['Contributor']) {
                    contributor = result['Contributor'];
                }

                if (result['Publisher']) {
                    publisher = result['Publisher'];
                }

                if (result['Year']) {
                    date = result['Year'];
                }

                if (result['MMS ID']) {
                    mms_id = result['MMS ID'];
                }

                if (result['ISBN']) {
                    isbn = result['ISBN'];
                }

                if (result['Version']) {
                    version = result['Version'];
                }
                
                if (result['Course Name']) {
                    course_name = result['Course Name'];
                  }

                if (result['course_code']){
                    course_code = result['course_code'];
                }
                if (result['course_section']){
                    course_section = result['course_section'];
                }

                
                if (result['Course Instructor']) {
                    course_instructor = result['Course Instructor'];
                }
    
				
				if (result['Library']){
					library = result['Library'];
					
				}
                
				if (result['Location']){
					library_location = result['Location'];
					
				}
                if (result['Call Number']){
                    call_number = result['Call Number'];
                }
            
                if (result['Barcode']){
                    barcode = JSON.parse(result['Barcode']);
                }
                
              
                if (result['Description']){
                    description = JSON.parse(result['Description']);
                } 
                
                if (result['Returned Format']){
                    format = result['Returned Format'];
                }

                let row = {
                    'Title': title,
                    'Author': author,
                    'Contributor': contributor,
                    'Publisher': publisher,
                    'Date': date,
                    'MMS ID': mms_id,
                    'ISBN': isbn,
                    'Version': version,
                    'Course Name': course_name,
                    'course_code': course_code,
                    'course_section': course_section,
                    'Course Instructor': course_instructor,
                    'Returned Format': format,
					'Library': library,
					'Location': library_location,
                    'Call Number': call_number,   
                    'Barcode': barcode,
                    'Description': description,
                    'Call Number': call_number,
                    'Citation Type': '',
                    'section_info': result['section_info'] || '',
                    'Item Policy': result['item_policy'] || result['Item Policy'] || '' 
                };
				
                var newValues = {};
				for (var k in result){
				if(!(row.hasOwnProperty(k))){
				if (result.hasOwnProperty(k)) {
				
					//row[k] = result[k];
                    newValues[k] = result[k];
					
				}
				}
			}

                if(typeof row == 'object'){
                    //data.push(row);
                    var newRow = Object.assign(newValues, row);
                
                    data.push(newRow);
                } else {
                    row = {
                        'Title': "ERROR",
                        'Author': "ERROR",
                        'Contributor': "ERROR",
                        'Publisher': "ERROR",
                        'Date': "ERROR",
                        'MMS ID': "ERROR",
                        'ISBN': "ERROR",
                        'Version': "ERROR",
                        'course_code': "ERROR",
                        'course_section': "ERROR",
                        'Returned Format': "ERROR",
						'Library': "ERROR",
						'Location': "ERROR",
                        'Call Number': "ERROR",    
                        'Barcode': "ERROR",
                        'Description': "ERROR",
                        'Call Number': "ERROR",
                        'Citation Type': '',
						'section_info': '',
                        'Item Policy': '' 
                    };

                    var newValues = {};
					for (var k in result){
				if(!(row.hasOwnProperty(k))){
				if (result.hasOwnProperty(k)) {
					newValues[k] = result[k];
					
				}
				}
			}

                var newRow = Object.assign(newValues, row);
                
                data.push(newRow);
                }
                
            });
        } else {
            
            let title, author, contributor, publisher, date, mms_id, isbn, version, course_name, course_code, course_section, course_instructor, library, library_location, call_number, barcode, description, format;

            if (results['Title']) {
                title = results['Title'];
            }

            if (results['Author']) {
                author = results['Author'];
            }

            if (results['Contributor']) {
                contributor = results['Contributor'];
            }

            if (results['Publisher']) {
                publisher = results['Publisher'];
            }

            if (results['Year']) {
                date = results['Year'];
            }

            if (results['MMS ID']) {
                mms_id = results['MMS ID'];
            }

            if (results['ISBN']) {
                isbn = results['ISBN'];
            }

            if (results['Version']) {
                version = results['Version'];
            }

            if (results['Course Name']) {
                course_name = results['Course Name'];
              }
            
            if (results['course_code']){
                course_code = results['course_code'];
            }

            if (results['course_section']){
                course_section = results['course_section'];
            }

            if (results['Course Instructor']) {
                course_instructor = results['Course Instructor'];
            }
            
			if (results['Library']){
					library = results['Library'];
					
			}
			
			if (results['Location']){
				library_location = results['Location'];
				
			}
            if (results['Call Number']){
                call_number = results['Call Number'];
            }
            if (results['Barcode']){
                barcode = JSON.parse(results['Barcode']);
            }

            
            if (results['Description']){
                description = JSON.parse(results['Description']);
            }
            
            if (results['Returned Format']){
                format = results['Returned Format'];
            }

            let row = {
                'Title': title,
                'Author': author,
                'Contributor': contributor,
                'Publisher': publisher,
                'Date': date,
                'MMS ID': mms_id,
                'ISBN': isbn,
                'Version': version,
                'Course Name': course_name,
                'Course Code': course_code,
                'Course Section': course_section,
                'Course Instructor': course_instructor,
                'Returned Format': format,
				'Library': library,
				'Location': library_location,
                'Call Number': call_number,
                'Barcode': barcode,
                'Description': description,
                'Citation Type': '',
                'section_info': '',
                'Item Policy': ''
            };
            var newValues = {};
			for (var k in results){
				if(!(row.hasOwnProperty(k))){
				if (results.hasOwnProperty(k)) {
					//k = k + " - Input";
				 	 //row[k] = results[k];
                    newValues[k] = results[k];
                                         
					
				}
				}
			}

            var newRow = Object.assign(newValues, row)
            alert(JSON.stringify(newRow));
            
            if(typeof row == 'object'){
                data.push(newRow);
            } else {
                row = {
                    'Title': "ERROR",
                    'Author': "ERROR",
                    'Contributor': "ERROR",
                    'Publisher': "ERROR",
                    'Date': "ERROR",
                    'MMS ID': "ERROR",
                    'ISBN': "ERROR",
                    'Version': "ERROR",
                    'Course Code': "ERROR",
                    'Course Section': "ERROR",
                    'Returned Format': "ERROR",
					'Library': "ERROR",
					'Location': "ERROR",
                    'Call Number': "ERROR", 
                    'Barcode': "ERROR",
                    'Description': "ERROR",
                    'Call Number': "ERROR",
                    'Citation Type': '',
                    'section_info': '',
                    'Item Policy': ''
                };
                var newValues = {};
			for (var k in results){
				if(!(row.hasOwnProperty(k))){
				if (results.hasOwnProperty(k)) {
					//k = k + " - Input
				 	 //row[k] = results[k];	
                    newValues[k] = results[k];				
				}
				}
			}
                var newRow = Object.assign(newValues, row);
                alert(JSON.stringify(newRow));
                data.push(newRow);
            }
        }
		
	

    console.log(data);
    const ws = XLSX.utils.json_to_sheet(data, {cellText: true});
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Results');
    const range = XLSX.utils.decode_range(ws['!ref']);
    let barcodeColIndex = -1; // Initialize to -1 to signify not found

    // Find the index of the column with the header "Barcode"
    for (let C = range.s.c; C <= range.e.c; ++C) {
        const cell = ws[XLSX.utils.encode_cell({r: range.s.r, c: C})];
        if (cell && cell.v === "Barcode") {
            barcodeColIndex = C;
            break; // Exit loop once the column is found
        }
    }
    for (let R = range.s.r + 1; R <= range.e.r; ++R) {
        const cell = ws[XLSX.utils.encode_cell({r: R, c: barcodeColIndex})];
        if (cell && cell.v) {
            cell.t = 's'; // Set cell type to string
            cell.z = '@'; // Set cell format to text
        }
    }

    XLSX.writeFile(wb, 'output.xlsx');
}
