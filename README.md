moodle-block_editablecontenthtml
================================

A simple HTML block variant that allows non editing roles to edit content based on a dedicated capability.

This block provides a mean to let some non editing roles (i.e., manipulating activities) to edit some 
content in a bloc in a course addressed to students. 

This serves when using a course template policy that provides to non editing trainers academic 
pre-written contents where some parts should be contextualized. 

A single capability (block/editablecontenthtml:editcontent) drives which role an edit or not. Editing
roles will anyway have the capability of editing passing through the usual block paramters edition
form. 

Those super-editors might lock the ocntent, thus avoiding sub-editors to change it any more.  