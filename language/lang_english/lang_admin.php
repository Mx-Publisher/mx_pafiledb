<?php
/** ------------------------------------------------------------------------
 *		Subject				: mxBB - a fully modular portal and CMS (for phpBB) 
 *		Author				: Jon Ohlsson and the mxBB Team
 *		Credits				: The phpBB Group & Marc Morisette, Mohd Basri & paFileDB 3.0 ©2001/2002 PHP Arena
 *		Copyright          	: (C) 2002-2005 mxBB Portal
 *		Email             	: jon@mxbb-portal.com
 *		Project site		: www.mxbb-portal.com
 * -------------------------------------------------------------------------
 * 
 *    $Id: lang_admin.php,v 1.6 2005/12/16 03:30:32 mennonitehobbit Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */
 
// Categories
$lang['Cat_manage_title'] = 'Category Management';
$lang['File_manage_title'] = 'File Management';
$lang['All_files'] = 'All Files';
$lang['Approved_files'] = 'Unapproved Files';
$lang['Broken_files'] = 'Broken Files';
$lang['File_cat'] = 'File in Category';
$lang['Maintenance'] = 'File Maintenance';
$lang['Approve'] = 'Approve';
$lang['Unapprove'] = 'Unapprove';
$lang['File_mode'] = 'View';
$lang['Approve_selected'] = 'Approve Selected';
$lang['Unapprove_selected'] = 'Unapprove Selected';
$lang['Delete_selected'] = 'Delete Selected';
$lang['No_file'] = 'There is no files';
$lang['Acat'] = 'Category: Add';
$lang['Ecat'] = 'Category: Edit';
$lang['Dcat'] = 'Category: Delete';
$lang['Rcat'] = 'Category: Reorder';
$lang['Cat_Permissions'] = 'Category Permissions';
$lang['User_Permissions'] = 'User Permissions';
$lang['Group_Permissions'] = 'Group Permissions';
$lang['User_Global_Permissions'] = 'User Global Permissions';
$lang['Group_Global_Permissions'] = 'Group Global Permissions';
$lang['Acattitle'] = 'Add Category'; 
$lang['Ecattitle'] = 'Edit Category'; 
$lang['Dcattitle'] = 'Delete Category'; 
$lang['Rcattitle'] = 'Reorder Categories'; 
$lang['Catexplain'] = 'You can use the Category Management section to add, edit, delete, and reorder categories. In order to add files to your database, you must have at least one category created. You can select a link below to manage your categories.'; 
$lang['Rcatexplain'] = 'You can reorder categories to change the position they are displayed in on the main page. To reorder the categories, change the numbers to the order you want them shown in. 1 will be showed first, 2 will be shown second, etc. This does not affect sub-categories.';
$lang['Catadded'] = 'The new category has been successfully added';
$lang['Catname'] = 'Category Name';
$lang['Catnameinfo'] = 'This will become the name of the category.';
$lang['Catdesc'] = 'Category Description';
$lang['Catdescinfo'] = 'This is a description of the files in the category';
$lang['Catparent'] = 'Parent Category';
$lang['Catparentinfo'] = 'If you want this category to be a subcategory, select the category you want it to be a sub-category of.';
$lang['Allow_file'] = 'Allow Adding file';
$lang['Allow_file_info'] = 'If you not allow adding file to this category it will be higher level category and you can add category as a sub for this category, like in the forum.';
$lang['None'] = 'None';
$lang['Catedited'] = 'The category you selected has been successfully edited';
$lang['Delfiles'] = 'What do you want to do with the files in this category?';
$lang['Do_cat'] = 'What do you want to do with the subcategory in this category?';
$lang['Move_to'] = 'Move to';
$lang['Catsdeleted'] = 'The categories you selected have been successfully deleted';
$lang['Cdelerror'] = 'You didn\'t select any categories to delete';
$lang['Rcatdone'] = 'The categories have been successfully re-ordered';

//Catgories Permission
$lang['View'] = 'View';
$lang['Read'] = 'Read';
$lang['View_file'] = 'View File';
$lang['Delete_file'] = 'Delete File';
$lang['Edit_file'] = 'Edit File';
$lang['Upload'] = 'Upload File';
$lang['Approval'] = 'Approval Level';
$lang['Download_file'] = 'Download File';
$lang['Rate'] = 'Rate';
$lang['View_comment'] = 'View Comments';
$lang['Post_comment'] = 'Post Comments';
$lang['Edit_comment'] = 'Edit Comments (n/a)';
$lang['Delete_comment'] = 'Delete Comments';
$lang['Category_auth_updated'] = 'Category permissions updated';
$lang['Click_return_catauth'] = 'Click %sHere%s to return to Category Permissions';
$lang['Auth_Control_Category'] = 'Category Permissions Control'; 
$lang['Category_auth_explain'] = 'Here you can alter the authorisation levels of each category. Remember that changing the permission level of category will affect which users can carry out the various operations within them.';
$lang['Select_a_Category'] = 'Select a Category';
$lang['Look_up_Category'] = 'Look Up Category';
$lang['Category'] = 'Category';

$lang['Category_ALL'] = 'ALL';
$lang['Category_REG'] = 'REG';
$lang['Category_PRIVATE'] = 'PRIVATE';
$lang['Category_MOD'] = 'MOD';
$lang['Category_ADMIN'] = 'ADMIN';

// Configuration
$lang['Settings'] = 'Configuration';
$lang['Settingstitle'] = 'Download Configuration';
$lang['Settingsexplain'] = 'The form below will allow you to customize all the general download options.';
$lang['Dbname'] = 'Database Name';
$lang['Dbnameinfo'] = 'This is the name of the database, such as \'Download Index\'';
$lang['Sitename'] = 'Site Name';
$lang['Sitenameinfo'] = 'This is the name of your site for the navigation menu, such as \'Home\'';
$lang['Dburl'] = 'Database URL';
$lang['Dburlinfo'] = 'This is the URL to the directory where this is installed';
$lang['Hpurl'] = 'Homepage URL';
$lang['Hpurlinfo'] = 'This is the URL to your portal or home page';
$lang['Topnum'] = 'Top Number';
$lang['Topnuminfo'] = 'This is how many files will be displayed on the Top X Downloaded files list';
$lang['Nfdays'] = 'New File Days';
$lang['Nfdaysinfo'] = 'How many days a new file is to be listed with a \'New File\' icon. If this is set to 5, then all files added within the past 5 days will have the \'New File\' icon';
$lang['Showva'] = 'Show \'View All Files\'';
$lang['Showvainfo'] = 'Choose whether or not you wish to have the \'View All Files\' category displayed with the other categories on the main page';
$lang['Php_template'] = 'PHP in template';
$lang['Php_template_info'] = 'This will allow you to use PHP directly in the template files';
$lang['Dbdl'] = 'Disable Downloads';
$lang['Dbdlinfo'] = 'This will make the download section unavailable to users. This is a good option to use when making modifications to your database. Only admins will be able to view the database';
$lang['Isdisabled'] = 'The download section is currently unavailable. Please try again later.';
$lang['Com_allowh'] = 'Allow HTML';
$lang['Com_allowb'] = 'Allow BBCode';
$lang['Com_allows'] = 'Allow Smilies';
$lang['Com_allowl'] = 'Allow Links';
$lang['Com_messagel'] = 'Default \'No Links\' Message';
$lang['Com_messagel_info'] = 'If links are not allowed this text will be displayed instead';
$lang['Com_allowi'] = 'Allow Images';
$lang['Com_messagei'] = 'Default \'No Images\' Message';
$lang['Com_messagei_info'] = 'If images are not allowed this text will be displayed instead';
$lang['Max_char'] = 'Maximum Number of characters';
$lang['Max_char_info'] = 'If some one posted a comment in which characters is more that this it will give them an error message (Limit the comment).';
$lang['Settings_changed'] = 'Your settings have been successfully updated';
$lang['File_per_page'] = 'Number of files per page';
$lang['File_per_page_info'] = 'Here you can set the number of files per page. If you leave it empty the number of per page will be 20.';
$lang['Hotlink_prevent'] = 'Hotlink Prevention';
$lang['Hotlinl_prevent_info'] = 'Set this to yes if you don\'t want to allow hotlinks to the files';
$lang['Hotlink_allowed'] = 'Allowed domains for hotlink';
$lang['Hotlink_allowed_info'] = 'Allowed domains for hotlink (separated by a comma), for example, www.phpbb.com, www.forumimages.com';
$lang['Default_sort_method'] = 'Default Sort Method';
$lang['Default_sort_order'] = 'Default Sort Order';
$lang['Max_filesize'] = 'Maximum Filesize';
$lang['Max_filesize_explain'] = 'Maximum filesize for Files. A value of 0 means \'unlimited\'. This Setting is restricted by your Server Configuration. For example, if your PHP Configuration only allows a maximum of 2 MB uploads, this cannot be overwritten by the module.';
$lang['Upload_directory'] = 'Upload Directory';
$lang['Upload_directory_explain'] = 'Enter the relative path from your root installation (where phpBB or mxBB is located) to the files upload directory. If you stick to the file structure provided in the shipped package, enter \'pafiledb/uploads/\'.';
$lang['Screenshots_directory'] = 'Screenshots Directory';
$lang['Screenshots_directory_explain'] = 'Enter the relative path from your root installation (where phpBB or mxBB is located) to the Screenshots upload directory. If you stick to the file structure provided in the shipped package, enter \'pafiledb/images/screenshots/\'.';
$lang['Forbidden_extensions'] = 'Forbidden Extensions';
$lang['Forbidden_extensions_explain'] = 'Here you can add or delete the forbidden extensions. Separate each extenstion with comma.';
$lang['Permission_settings'] = 'Permission settings';
$lang['Auth_search'] = 'Search Permission';
$lang['Auth_search_explain'] = 'Allow search for specific type of users';
$lang['Auth_stats'] = 'Stats Permission';
$lang['Auth_stats_explain'] = 'Allow stats for specific type of users';
$lang['Auth_toplist'] = 'Toplist Permission';
$lang['Auth_toplist_explain'] = 'Allow toplist for specific type of users';
$lang['Auth_viewall'] = 'Viewall Permission';
$lang['Auth_viewall_explain'] = 'Allow viewall for specific type of users';
$lang['Bytes'] = 'Bytes';
$lang['KB'] = 'KB';
$lang['MB'] = 'MB';

// Custom Field
$lang['Fieldselecttitle'] = 'Select what to do';
$lang['Afield'] = 'Custom Field: Add';
$lang['Efield'] = 'Custom Field: Edit';
$lang['Dfield'] = 'Custom Field: Delete';
$lang['Mfieldtitle'] = 'Custom Fields';
$lang['Afieldtitle'] = 'Add Field';
$lang['Efieldtitle'] = 'Edit Field';
$lang['Dfieldtitle'] = 'Delete Field';
$lang['Fieldexplain'] = 'You can use the custom fields management section to add, edit, and delete custom fields. You can use custom fields to add more information about a file. For example, if you want an information field to put the file\'s size in, you can create the custom field and then you can add the file size on the Add/Edit file page.';
$lang['Fieldname'] = 'Field Name';
$lang['Fieldnameinfo'] = 'This is the name of the field, for example \'File Size\'';
$lang['Fielddesc'] = 'Field Description';
$lang['Fielddescinfo'] = 'This is a description of the field, for example \'File Size in Megabytes\'';
$lang['Fieldadded'] = 'The custom field has been successfully added';
$lang['Fieldedited'] = 'The custom field you selected has been successfully edited';
$lang['Dfielderror'] = 'You didn\'t select any fields to delete';
$lang['Fieldsdel'] = 'The custom fields you selected have been successfully deleted';

$lang['Field_data'] = 'Options';
$lang['Field_data_info'] = 'Enter the options that the user can choose from. Separate each option with a newline (carriage return).';
$lang['Field_regex'] = 'Regular Expression';
$lang['Field_regex_info'] = 'You may require the input field to match a regular expression %s(PCRE)%s.';
$lang['Field_order'] = 'Display Order';

// File
$lang['Afile'] = 'File: Add';
$lang['Efile'] = 'File: Edit';
$lang['Dfile'] = 'File: Delete';
$lang['Afiletitle'] = 'Add File';
$lang['Efiletitle'] = 'Edit File';
$lang['Dfiletitle'] = 'Delete File';
$lang['Fileexplain'] = 'You can use the file management section to add, edit, and delete files.';
$lang['Upload'] = 'Upload File';
$lang['Uploadinfo'] = 'Upload this file';
$lang['Uploaderror'] = 'This file already exists. Please rename the file and try again.';
$lang['Uploaddone'] = 'This file has been successfully uploaded. The URL to the file is';
$lang['Uploaddone2'] = 'Click Here to place this URL in the Download URL field.';
$lang['Upload_do_done'] = 'Uploaded Sucessfully';
$lang['Upload_do_not'] = 'Not Uploaded';
$lang['Upload_do_exist'] = 'File Exist';
$lang['Filename'] = 'File Name';
$lang['Filenameinfo'] = 'This is the name of the file you are adding, such as \'My Picture.\'';
$lang['Filesd'] = 'Short Description';
$lang['Filesdinfo'] = 'This is a short description of the file. This will go on the page that lists all the files in a category, so this description should be short';
$lang['Fileld'] = 'Long Description';
$lang['Fileldinfo'] = 'This is a longer description of the file. This will go on the file\'s information page so this description can be longer';
$lang['Filecreator'] = 'Creator/Author';
$lang['Filecreatorinfo'] = 'This is the name of whoever created the file.';
$lang['Fileversion'] = 'File Version';
$lang['Fileversioninfo'] = 'This is the version of the file, such as 3.0 or 1.3 Beta';
$lang['Filess'] = 'Screenshot URL';
$lang['Filessinfo'] = 'This is a URL to a screenshot of the file. For example, if you are adding a Winamp skin, this would be a URL to a screenshot of Winamp with this skin. You can manually enter a URL or you can leave it blank and upload a screen shot using "Browse" above.';
$lang['Filess_upload'] = 'Upload Screenshot';
$lang['Filessinfo_upload'] = 'You can upload a screenshot by clicking on "Browse"';
$lang['Filess_link'] = 'Screenshot as a Link';
$lang['Filess_link_info'] = 'If you want to show the screenshot as a link, choose "yes".';
$lang['Filedocs'] = 'Documentation/Manual URL';
$lang['Filedocsinfo'] = 'This is a URL to the documentation or a manual for the file';
$lang['Fileurl'] = 'File URL';
$lang['Fileurlinfo'] = 'This is a URL to the file that will be downloaded. You can type it in manually or you can click on "Browse" above and upload a file.';
$lang['File_upload'] = 'File Upload';
$lang['Fileinfo_upload'] = 'You can upload a file by clicking on "Browse"';
$lang['Uploaded_file'] = 'Uploaded file';
$lang['Filepi'] = 'Post Icon';
$lang['Filepiinfo'] = 'You can choose a post icon for the file. The post icon will be shown next to the file in the list of files.';
$lang['Filecat'] = 'Category';
$lang['Filecatinfo'] = 'This is the category the file belongs in.';
$lang['Filelicense'] = 'License';
$lang['Filelicenseinfo'] = 'This is the license agreement the user must agree to before downloading the file.';
$lang['Filepin'] = 'Pin File';
$lang['Filepininfo'] = 'Choose if you want the file pinned or not. Pinned files will always be shown at the top of the file list.';
$lang['Fileadded'] = 'The new file has been successfully added';
$lang['Filedeleted'] = 'The file has been successfully deleted';
$lang['Fileedited'] = 'The file you selected has been successfully edited';
$lang['Fderror'] = 'You didn\'t select any files to delete';
$lang['Filesdeleted'] = 'The files you selected have been successfully deleted';
$lang['Filetoobig'] = 'That file is too big!';
$lang['Approved'] = 'Approved';
$lang['Not_approved'] = '(Not Approved)';
$lang['Approved_info'] = 'Use this option to make the file available for users, and also to approve a file that has been uploaded by the users.';
$lang['Fchecker'] = 'File: Maintenance';
$lang['File_checker'] = 'File Maintenance';
$lang['File_checker_explain'] = 'Here you can perform a checking for all file in database and the file in the download directory.';
$lang['File_saftey'] = 'File maintenance will attempt to delete all files and screenshots that are currently not needed and will remove any file records where the file has been deleted and will clear all screenshots that are not found.<br /><br />If the files do not start with <FONT COLOR="#FF0000">{html_path}</FONT> then the files will be skipped for security reasons.<br /><br />Please make sure that <FONT COLOR="#FF0000">{html_path}</FONT> is the path that you use for your files.<br /><br />.';
// <a href="' . append_sid($phpbb_root_path . "admin/admin_db_utilities.php?perform=backup") . '" class="genmed">Backup your database</a>
$lang['File_checker_perform'] = 'Perform Checking';
$lang['Checker_saved'] = 'Total Saved Space';
$lang['Checker_sp1'] = 'Checking for records with missing files...';
$lang['Checker_sp2'] = 'Checking for records with missing screenshots...';
$lang['Checker_sp3'] = 'Deleting unused Files...'; 
$lang['Filedls'] = 'Download Total';
$lang['Addtional_field'] = 'Additional Field';
$lang['File_not_found'] = 'The file you specified cannot be found';
$lang['SS_not_found'] = 'The screenshot you specified cannot be found';
// License 
$lang['Alicense'] = 'License: Add';
$lang['Elicense'] = 'License: Edit';
$lang['Dlicense'] = 'License: Delete';
$lang['Alicensetitle'] = 'Add License';
$lang['Elicensetitle'] = 'Edit License';
$lang['Dlicensetitle'] = 'Delete License';
$lang['Licenseexplain'] = 'You can use the license management section to add, edit, and delete license agreements. You can select a license for a file on the file add or edit page. If a file has a license agreement, a user will have to agree to it before downloading the file.';
$lang['Lname'] = 'License Name';
$lang['Ltext'] = 'License Text';
$lang['Licenseadded'] = 'The new license agreement has been successfully added';
$lang['Licenseedited'] = 'The license agreement you selected has been successfully edited';
$lang['Lderror'] = 'You did not select any licenses to delete';
$lang['Ldeleted'] = 'The license agreements you selected have been successfully deleted';
$lang['License_title'] = 'License';

$lang['Click_return'] = 'Click %sHere%s to return to the previous page';
$lang['Click_edit_permissions'] = 'Click %sHere%s to edit the permissions for this category';

//JavaScript messages and PHP errors
$lang['Cat_name_missing'] = 'Please fill the category name field';
$lang['Cat_conflict'] = 'You can\'t have a category with no file inside a category that doesn\'t allow files';
$lang['Cat_id_missing'] = 'Please select a category';
$lang['Missing_field'] = 'Please complete all the required fields';


//Fields Types

$lang['Field_Input'] = 'Single-Line Text Box';
$lang['Field_Textarea'] = 'Multiple-Line Text Box';
$lang['Field_Radio'] = 'Single-Selection Radio Buttons';
$lang['Field_Select'] = 'Single-Selection Menu';
$lang['Field_Select_multiple'] = 'Multiple-Selection Menu';
$lang['Field_Checkbox'] = 'Multiple-Selection Checkbox';

$lang['Com_settings'] = 'Comment Settings';
$lang['Validation_settings'] = 'Approval Settings';
$lang['Ratings_settings'] = 'Ratings Settings';
$lang['PM_notify'] = 'PM Notification (to admin)';

$lang['Use_comments'] = 'Enable comments';
$lang['Allow_comments'] = 'Enable comments';
$lang['Allow_comments_info'] = 'Enable/disable comments in this category.';

$lang['Use_ratings'] = 'Enable ratings';
$lang['Allow_ratings'] = 'Enable ratings';
$lang['Allow_ratings_info'] = 'Enable/disable ratings in this category.';

$lang['MCP_title'] = 'Moderator Control Panel';
$lang['MCP_title_explain'] = 'Here moderators can approve and manage files';

$lang['Fileadded_not_validated'] = 'The new file has been successfully added, but a moderator (or administrator) needs to validate the file before approval.';

// Toplists
$lang['display_most_posts']			= "Most visited/viewed/downloaded<br />";
$lang['display_random_posts']		= "Random<br />";
$lang['display_top_ranked']			= "Top-rated<br />";
$lang['display_latest_posts']		= "Latest<br />";
$lang['num_of_cols']				= "Number of columns<br />";
$lang['num_of_rows']				= "Number of rows<br />";
$lang['target_block']				= "Associated (target) pafileDB Block";

?>