document.getElementById('uploadForm').addEventListener('submit', handleUpload);

function citationsValid(citations) {
    try {
        JSON.parse(citations);
    } catch (e) {
        return false;
    }
    return true;
}

function handleUpload(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    var data = [];
    let isValid = true;

    // Prepare arrays to hold values for each row
    const courseNumbers = [];
    const courseInstructors = [];
    const courseSemesters = [];
    const courseCitations = [];

    // Collect the data into arrays
    formData.forEach((value, key) => {
        if (key === 'courseNumber') courseNumbers.push(value);
        if (key === 'courseInstructor') courseInstructors.push(value);
        if (key === 'courseSemester') courseSemesters.push(value);
        if (key === 'courseCitations') courseCitations.push(value);
    });

    // Validate each citation JSON
    for (let i = 0; i < courseCitations.length; i++) {
        if (!citationsValid(courseCitations[i])) {
            isValid = false;
            alert('Error: citations misformed. Make sure to parse them through https://anystyle.io/');
            return;
        }
    }

    if (!isValid) return;
	
    // Process each row of data
    for (let i = 0; i < courseNumbers.length; i++) {
        const citations = JSON.parse(courseCitations[i]);

        citations.forEach(citation => {
            let creator = "";

            if (citation.author != undefined) {
                creator = citation.author;
            }

            if (citation.editor != undefined) {
                creator = citation.editor;
            }

            const flattenedCitation = {
                'Course Number': courseNumbers[i],
                'Instructor Last Name': courseInstructors[i],
                'Course Semester': courseSemesters[i],
                'Title': citation.title || citation['container-title'] || '',
                'Author': creator ? creator.map(a => `${a.family}, ${a.given}`).join('; ') : '',
                'Publisher': citation.publisher || '',
                'Year': citation.issued ? citation.issued['date-parts'][0][0] : ''
            };
            data.push(flattenedCitation);
        });
    }

    console.log(JSON.stringify(data));
    sendToServer(data);
    //updateProgressBar();
}


function sendToServer(data) {
    document.getElementById('hourglass').style.display = 'block'; // Show hourglass

    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(results => {
        let data = [];

        console.log(results);
        
        if (results != null && results != undefined && results.length > 1){
            results.forEach(result => {
                let title, author, contributor, publisher, date, mms_id, isbn, version, course_code, course_section, call_number, barcode, url, description, format;

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
                
                if(result['URL']){
                    url = JSON.parse(result['URL']);
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
                    'URL': url,     
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
                        'Format': "ERROR",
                        'Call Number': "ERROR",
                        'URL': "ERROR",     
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
            let title, author, contributor, publisher, date, mms_id, isbn, version, course_code, call_number, barcode, url, description, format;

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

            if(results['URL']){
                url = JSON.parse(results['URL']);
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
                'URL': url,
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
                    'Format': "ERROR",
                    'Call Number': "ERROR",
                    'URL': "ERROR",     
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
        
        // Create a new workbook and a worksheet
        const ws = XLSX.utils.json_to_sheet(data, {cellText: true});
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Results');

        // Set the format of the barcode column to text explicitly
        const range = XLSX.utils.decode_range(ws['!ref']);
        for (let R = range.s.r + 1; R <= range.e.r; ++R) {
            const cell = ws[XLSX.utils.encode_cell({r: R, c: 13})]; // Column J is index 9
            if (cell && cell.v) {
                cell.t = 's'; // Set cell type to string
                cell.z = '@'; // Set cell format to text
            }
        }

        XLSX.writeFile(wb, 'output.xlsx');

        //document.getElementById('downloadBtn').style.display = 'block';
        document.getElementById('hourglass').style.display = 'none'; // Hide hourglass
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error:' + error.message + data);
        document.getElementById('hourglass').style.display = 'none'; // Hide hourglass
    });
}