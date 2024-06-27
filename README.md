**Lookup Tool for Citation Loader**

**Author:** Henry Steele, Library Technology Services, Tufts Univeristy, 2024

**Purpose:**

This tool takes a table of basic metadata such you might find in a bibliography, separated into columns such as described below, and course information described below and such as you are likely to receive in faculty emails with bibliography-style requested readings , and outputs a series of fields from Alma, from the Primo PNX Search API, the course API, and for physical items the BIB/Item API, notably the MMS ID, course code, and if applicable barcode, plus a number of fields for context. This report, aftering pulling out rows for e.g. editions you don’t want, is suitable for direct upload into the citation to create citations for reading list.

That cloud app and usage is described here: <https://github.com/TuftsUniversity/tufts-alma-cloudapp-citation-loader>

**Configure:**

Before hosting this app on your local web server, you need to go into api.php and enter your local instance’s Primo Search, Course, and BIB API keys. They can be read-only

**Input and Output:**

Note this input table can be created from an AI service such as chatgpt, directly from a bibliography-style citation, with a prompt such as the following:

“Make the following citations into a table of bibliographic metadata with columns “Title”, “Author”, “Contributor”, “Publisher”, and “Date”

Make 3 additional columns in the output table : “Course Number”, “Instructor Last Name”, and “Course Semester “ and leave them blank for user entry “

- Input data dictionary
  - - The input sheet gathered from faculty email should have the following columns with the following characteristics
      - No field is required, but keep in mind fewer pieces of data will return broader , less specific results
  - Author
    - This can either be in the format “&lt;first&gt;&lt;space&gt;&lt;last&gt;” or “&lt;last&gt;&lt;comma&gt;&lt;any_number of spaces&gt;&lt;first&gt;
    - If the comma is present then the script will reverse the names and remove the comma because this is what the Primo API seems to work best with
    - At the present it only takes the first author in a list
    - For instance in the following record <https://tufts.primo.exlibrisgroup.com/discovery/fulldisplay?docid=alma991005315909703851&context=L&vid=01TUN_INST:01TUN&lang=en&search_scope=MyInst_and_CI&adaptor=Local%20Search%20Engine&isFrbr=true&tab=Everything&query=any,contains,991005315909703851&sortby=date_d&facet=frbrgroupid,include,9076214921291068687&offset=0> which is a film production of a midsummer night’s dream, “Shakespeare” is the author
    - **Note this hasn’t been tested with various other affixes a number can have like “, Jr.”**
  - Contributor
    - This can either be in the format “&lt;first&gt;&lt;space&gt;&lt;last&gt;” or “&lt;last&gt;&lt;comma&gt;&lt;any_number of spaces&gt;&lt;first&gt;
    - If the comma is present then the script will reverse the names and remove the comma because this is what the Primo API seems to work best with
    - This in the sense meant in various metadata standards, DC/MARC etc. and can include director
    - In the example here “Beverly Bullock” is the director so you can include this in the contributor column
    - Including both shakespeare as the author and the director will help narrow down the results
    - **Note this hasn’t been tested with various other affixes a number can have like “, Jr.”**
  - Title
    - The title of the work , slightly normalized for search
  - Publisher
  - Date
  - Course Number
    - This is the number that appears within the course name
    - It can appear anywhere in the course name , but must appear exactly as entered
    - So at Tufts eg FR 22 (French ) would be expressed as FR-0022
    - You can also include section if desired and needed to uniquely identity the section
    - For instance FR-0022-01
  - Instructor Last Name
    - Just for one of the insecure ideas there are multiple
  - Course Semester
    - In the format “Sp24”
    - This is also taken from the course name
- Output
  - The output metadata is taken from the Primo PNX, Course , and if it’s a physical item the Bib API
  - The goal is to output metadata that can directly loaded into the citation loader after culling any unwanted records. ( For instance culling unwanted editions if they are returned)
  - The important fields are MMS ID, Course Code , Course Section, and Barcode if one exists
  - The other fields are informational to identify the record , including format and version
  - It also includes blank columns for the following metadata fields that can be included in the input for the citation loader by staff entering values for these in desired rows
  - Citation Type (from code table )
    - This is secondary type
    - Can be “DVD” etc
    - Primary type is Book for the kind of material uploaded in the citation loader. (This is hard coded in app and is not included in spreadsheet )
    - section_info (Reading list section)
    - Item Policy (This is the temporary item policy that will be included with the temporary library, and temporary location for physical items if a temporary move is desired )