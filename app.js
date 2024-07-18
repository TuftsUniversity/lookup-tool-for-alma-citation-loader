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
	console.log(file);
	
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const data = e.target.result;
			console.log(JSON.stringify(data));
            const progressBar = document.getElementById('progressBar');
    		let isValid = true;
			courseData = [];
			
            const workbook = XLSX.read(data, {type: 'binary'});
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            const json = XLSX.utils.sheet_to_json(worksheet);
			console.log(JSON.stringify(json));
			var totalCitations = json.length;
            json.forEach(row => {
				console.log(JSON.stringify(row));
            //Author	Contributor	Title	Publisher	Year	Course Number	Instructor Last Name	Course Semester
			const author = row['Author'];
			const contributor = row['Contributor'];
			const title = row['Title'];
			const publisher = row['Publisher'];
			const year = row['Year'];
			const course_number = row['Course Number'];
			const course_semester = row['Course Semester'];
			const instructor = row['Instructor Last Name'];
			const other_columns = [];
		    //for (var column in row){
/* 			if(column != "Author" && column != "Contributor" && column != "Title" && column != "Publisher" && column != "Year" && column != "Course Number" && column != "Course Semester" && column != "Instructor Last Name"){
				other_columns.push({column: row[columm]});
			}
			} */
		    	
		    

        	courseData.push({
            author,
            contributor,
            title,
            publisher,
            year,
            course_number,
            course_semester,
            instructor,
            
        });
			for (var k in row){
				if(!(courseData.hasOwnProperty(k))){
				if (row.hasOwnProperty(k)) {
					
					courseData[k] =row[k];
					
				}
				}
			}
        
			if (!isValid) return;

    		
			});
            row['original_title']= title;
            row['original_author'] = author;
            row['original_contributor'] = contributor;
            row['original_publisher'] = publisher;
            row['original_year'] = year;
			let results = [];
			console.log(JSON.stringify(courseData));
    		let processedCitations = 0;
			return courseData.reduce((promise, row) => {
				return promise.then(() => {
/*             return row.parsedCitations.reduce((citationPromise, citation) => { */
					return promise.then(() => {
				
					const flattenedCitation = {
					'Course Number': row.course_number,
					'Instructor Last Name': row.instructor,
					'Course Semester': row.course_semester,
					'Title': row.title || '',
					'Author': row.author || '',
					'Contributor': row.contributor || '',
					'Publisher': row.publisher || '',
					'Year': row.year || '',
				  
					
					};
					
					for (var k in row){
					if(!(flattenedCitation.hasOwnProperty(k))){
					if (row.hasOwnProperty(k)) {
						flattenedCitation[k] =row[k];
						
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
        console.log("test");
	}
	
	else(alert("no file"));
	}


function generateExcel(results) {
	
		data = [];
        console.log(results);
        

        if (results != null && results != undefined && results.length > 1){
            results.forEach(result => {
                let title, author, contributor, publisher, date, mms_id, isbn, version, course_code, course_section, call_number, barcode, description, format;


				
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
                
                if (result['Course Code']){
                    course_code = result['Course Code'];
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
                
                if (result['Format']){
                    format = result['Format'];
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
                    'Course Code': course_code,
                    'Course Section': course_section,
                    'Format': format,
                    'Call Number': call_number,   
                    'Barcode': barcode,
                    'Description': description,
                    'Call Number': call_number,
                    'Citation Type': '',
                    'section_info': '',
                    'Item Policy': ''
                };
				
				for (var k in result){
				if(!(row.hasOwnProperty(k))){
				if (result.hasOwnProperty(k)) {
				row[k] =result[k];
					
				}
				}
			}

                if(typeof row == 'object'){
                    data.push(row);
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
                        'Format': "ERROR",
                        'Call Number': "ERROR",    
                        'Barcode': "ERROR",
                        'Description': "ERROR",
                        'Call Number': "ERROR",
                        'Citation Type': '',
                        'section_info': '',
                        'Item Policy': ''
                    };
					for (var k in result){
				if(!(row.hasOwnProperty(k))){
				if (result.hasOwnProperty(k)) {
				row[k] =result[k];
					
				}
				}
			}
                    data.push(row);
                }
                
            });
        } else {
            console.log(JSON.stringify(results));
            let title, author, contributor, publisher, date, mms_id, isbn, version, course_code, course_section, call_number, barcode, description, format;

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
            
            if (results['Course Code']){
                course_code = results['Course Code'];
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
            
            if (results['Format']){
                format = results['Format'];
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
                'Course Code': course_code,
                'Course Section': course_section,
                'Format': format,
                'Call Number': call_number,
                'Barcode': barcode,
                'Description': description,
                'Citation Type': '',
                'section_info': '',
                'Item Policy': ''
            };
			
			for (var k in results){
				if(!(row.hasOwnProperty(k))){
				if (results.hasOwnProperty(k)) {
				row[k] =results[k];
					
				}
				}
			}
            
            if(typeof row == 'object'){
                data.push(row);
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
                    'Format': "ERROR",
                    'Call Number': "ERROR", 
                    'Barcode': "ERROR",
                    'Description': "ERROR",
                    'Call Number': "ERROR",
                    'Citation Type': '',
                    'section_info': '',
                    'Item Policy': ''
                };
				
				for (var k in results){
				if(!(row.hasOwnProperty(k))){
				if (results.hasOwnProperty(k)) {
				row[k] =results[k];
					
				}
				}
			}
                data.push(row);
            }
        }
    const ws = XLSX.utils.json_to_sheet(data, {cellText: true});
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Results');

    const range = XLSX.utils.decode_range(ws['!ref']);
    for (let R = range.s.r + 1; R <= range.e.r; ++R) {
        const cell = ws[XLSX.utils.encode_cell({r: R, c: 12})];
        if (cell && cell.v) {
            cell.t = 's'; // Set cell type to string
            cell.z = '@'; // Set cell format to text
        }
    }

    XLSX.writeFile(wb, 'output.xlsx');
}
