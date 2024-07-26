**Lookup Tool for Citation Loader**

**Author:** Henry Steele, Library Technology Services, Tufts Univeristy, 2024

**Purpose:**

This tool takes a table of basic metadata such you might find in a bibliography, separated into columns such as described below, and course information described below and such as you are likely to receive in faculty emails with bibliography-style requested readings , uses the the Alma SRU (which you will need to enable in integration profiles) the course API, and for physical items the BIB/Item API, and outputs several pieces of metadata, notably the MMS ID, course code, and if applicable barcode, plus a number of fields for context. you can use thus report after culling rows for e.g. editions you don’t want, is suitable for direct upload into the citation to create reading lists and citations.

That cloud app and usage is described here: <https://github.com/TuftsUniversity/tufts-alma-cloudapp-citation-loader>

**Configure:**

Before hosting this app on your local web server, you need to go into secrets.php and enter your local instance’s Course and bin API keys. They can be read-only

**Input and Output:**

There are two branches of this lookup tool, available in branches v9 and v10.  

v9 takes a spreadsheet youve pulled this bibliographic metadata into and works on thar.  v10 allows you to create rowa for each course and jusr paste in the citations that youve turbed inti JSON by going to anystyle.io referenced in the HTML.  both can handke multiple courses

For v9,  this input table can be created from an AI service such as chatgpt, directly from a bibliography-style citation, with a prompt such as the following:

“Make the following citations into a table of bibliographic metadata with columns “Title”, “Author”, “Contributor”, “Publisher”, and “Date”

Make 3 additional columns in the output table : “Course Number”, “Instructor Last Name”, and “Course Semester “ and leave them blank for user entry “

Note that for v9, the program will carry over any additional fields you include as well.  
- Input data dictionary
  - The input sheet gathered from faculty email should have the following columns with the following characteristics
      - No field is required, but keep in mind fewer pieces of data will return broader , less specific results
  - Author
    - in citation format.  It will be normalized for inclusion in an SRU search parameter 
  - Contributor
    - in citation format.  It will be normalized for inclusion in an SRU parameter.   note this could be a directir included for a film by the author of the original work. 
  - Title
    - The title in citation formar
  - Publisher
    - you can include this but it's not used in search.   It's just that most citations have them.  It was deemed to problematic to include
  - Date
    - year
  - Course Number
    - This is the number that appears within the course name
    - It can appear anywhere in the course name , but must appear exactly as entered
    - So at Tufts eg FR 22 (French ) would be expressed as FR-0022
    - You can also include section if desired and needed to uniquely identity the section
    - For instance FR-0022-01
  - Instructor Last Name
    - Just for one of the instructors if there are multiple
  - Course Semester
    - In the format “Sp24” or whatever appears at the beginning of your course name
    
- Output
  - The output metadata is taken from the SRU, Course , and if it’s a physical item the Bib API
  - The goal is to output metadata that can directly loaded into the citation loader after you cull any unwanted records. ( For instance culling unwanted editions if they are returned)
  - The important fields are MMS ID, Course Code , Course Section, and Barcode if one exists
  - The other fields are informational to identify the record , including format and version
  - It also includes blank columns for the following metadata fields that can be included in the input for the citation loader by staff entering values for these in desired rows
    - Citation Type (from code table )
      - This is secondary type
      - Can be “DVD” etc
      - Primary type is Book for the kind of material uploaded in the citation loader. (This is hard coded in app and is not included in spreadsheet )
    - section_info (Reading list section)
    - Item Policy (This is the temporary item policy that will be included with the temporary library, and temporary location for physical items if a temporary move is desired )