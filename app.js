document.getElementById('uploadForm').addEventListener('submit', handleUpload);

function handleUpload(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    const data = [];
    formData.forEach((value, key) => {
        if (key === 'courseCitations') {
            const citations = JSON.parse(value);
            const courseNumber = formData.get('courseNumber');
            const courseInstructor = formData.get('courseInstructor');
            const courseSemester = formData.get('courseSemester');

            citations.forEach(citation => {
                const citationData = {
                    'Course Number': courseNumber,
                    'Course Instructor': courseInstructor,
                    'Course Semester': courseSemester,
                    'Citation': citation
                };
                data.push(citationData);
            });
        }
    });

    sendToServer(data);
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
                }

                else {
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
