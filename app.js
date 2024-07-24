function citationsValid(citations) {
    try {
        JSON.parse(citations);
    } catch (e) {
        return false;
    }
    return true;
}

async function handleUpload(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const courseData = [];
    const progressBar = document.getElementById('progressBar');
    let isValid = true;

	var totalCitations = 0;
    const rows = document.querySelectorAll('.courseRow');
    rows.forEach(row => {
        const courseNumber = row.querySelector('input[name="courseNumber"]').value;
        const courseInstructor = row.querySelector('input[name="courseInstructor"]').value;
        const courseSemester = row.querySelector('input[name="courseSemester"]').value;
        const courseCitations = row.querySelector('textarea[name="courseCitations"]').value;

        if (!citationsValid(courseCitations)) {
            isValid = false;
            alert('Error: citations misformed. Make sure to parse them through https://anystyle.io/');
            return;
        }

        const parsedCitations = JSON.parse(courseCitations);

		parsedCitations.forEach(citation => {
			totalCitations++;
			courseData.push({
            courseNumber,
            courseInstructor,
            courseSemester,
            citation
        });
			
			
		});
        
    });

    if (!isValid) return;

    let results = [];
  
    let processedCitations = 0;

	
	return courseData.reduce((promise, row) => {
        return promise.then(() => {
/*             return row.parsedCitations.reduce((citationPromise, citation) => { */
                return promise.then(() => {
                    let creator = "";
            
			citation = row.citation;
            if (citation.author != undefined) {
                creator = citation.author;
            }

            if (citation.editor != undefined) {
                creator = citation.editor;
            }

            const flattenedCitation = {
                'Course Number': row.courseNumber,
                'Instructor Last Name': row.courseInstructor,
                'Course Semester': row.courseSemester,
                'Title': citation.title || citation['container-title'] || '',
                'Author': creator ? creator.map(a => `${a.family}, ${a.given}`).join('; ') : '',
                'Publisher': citation.publisher || '',
                'Year': citation.issued ? citation.issued['date-parts'][0][0] : ''
            };

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
            }); });
/*             }, Promise.resolve()); */
        });
    }, Promise.resolve()).then(() => {
        generateExcel(results);
    });
}



function generateExcel(results) {
	
		data = [];
        console.log(results);
        
        if (results != null && results != undefined && results.length > 1){
            results.forEach(result => {
                let title, author, contributor, publisher, date, mms_id, isbn, version, course_code, course_section, library, library_location, call_number, barcode, description, format;

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
                    'Course Code': course_code,
                    'Course Section': course_section,
                    'Returned Format': format,
					'Library': library,
					'Location': library_location,                    'Call Number': call_number,   
					'Call Number': call_number, 
                    'Barcode': barcode,
                    'Description': description,
                    'Call Number': call_number,
                    'Citation Type': '',
                    'section_info': '',
                    'Item Policy': ''
                };

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
                         'Returned Format': "ERROR",
						'Library': "ERROR",
						'Location': "ERROR",                        'Call Number': "ERROR",    
						'Call Number': "ERROR",  
                        'Barcode': "ERROR",
                        'Description': "ERROR",
                        'Call Number': "ERROR",
                        'Citation Type': '',
                        'section_info': '',
                        'Item Policy': ''
                    };
                    data.push(row);
                }
                
            });
        } else {
            console.log(JSON.stringify(results));
            let title, author, contributor, publisher, date, mms_id, isbn, version, course_code, course_section, library, library_location, call_number, barcode, description, format;

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
                'Course Code': course_code,
                'Course Section': course_section,
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
                data.push(row);
            }
        }
    const ws = XLSX.utils.json_to_sheet(data, {cellText: true});
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Results');

    const range = XLSX.utils.decode_range(ws['!ref']);
    for (let R = range.s.r + 1; R <= range.e.r; ++R) {
        const cell = ws[XLSX.utils.encode_cell({r: R, c: 14})];
        if (cell && cell.v) {
            cell.t = 's'; // Set cell type to string
            cell.z = '@'; // Set cell format to text
        }
    }

    XLSX.writeFile(wb, 'output.xlsx');
}
