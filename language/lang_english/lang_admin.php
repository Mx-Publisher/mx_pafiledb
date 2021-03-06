<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: lang_admin.php,v 1.16 2008/06/03 20:15:58 jonohlsson Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

//
// adminCP index
//
$lang['pafileDB_Download'] = 'PafileDB Admin';
$lang['0_Configuration'] = 'General Settings';
$lang['1_Cat_manage'] = 'Category Management';
$lang['2_File_manage'] = 'File Management';
$lang['3_Permissions'] = 'Permissions';
$lang['4_License'] = 'License';
$lang['5_Custom_manage'] = 'Custom fields';
$lang['6_Fchecker'] = 'File checker';

//
// Parameter Types
//
$lang['ParType_pa_mapping'] = 'pafileDB category mapping';
$lang['ParType_pa_mapping_info'] = '';

$lang['ParType_pa_quick_cat'] = 'pafileDB default category';
$lang['ParType_pa_quick_cat_info'] = '';

//
// Parameter Names
//
$lang['pa_mapping'] = 'pafileDB category mapping';
$lang['pa_mapping_explain'] = 'pafileDB categories and portal dynamic blocks mapping';

$lang['pa_quick_cat'] = 'pafileDB default category';
$lang['pa_quick_cat_explain'] = 'This category is used if no matching mapping is found';

//
// Admin Panels - Configuration
//
$lang['Panel_config_title'] = 'Download Configuration';
$lang['Panel_config_explain'] = 'The form below will allow you to customize all the general download options.';

//
// General
//
$lang['General_title'] = 'General';

$lang['Module_name'] = 'Database Name';
$lang['Module_name_explain'] = 'This is the name of the database, such as \'Download Index\'';

$lang['Enable_module'] = 'Enable this module';
$lang['Enable_module_explain'] = 'When disabled for users, admin is still allowed access.';

$lang['Wysiwyg_path'] = 'Path to WYSIWYG software';
$lang['Wysiwyg_path_explain'] = 'This is the path (from MX-Publisher/phpBB root) to the WYSIWYG software folder, eg \'modules/mx_shared/\' if you have uploaded, for example, TinyMCE in modules/mx_shared/tinymce.';

$lang['Upload_directory'] = 'Upload Directory';
$lang['Upload_directory_explain'] = 'Enter the relative path from your root installation (where phpBB or MX-Publisher is located) to the files upload directory. If you stick to the file structure provided in the shipped package, enter \'pafiledb/uploads/\'.';

$lang['Screenshots_directory'] = 'Screenshots Directory';
$lang['Screenshots_directory_explain'] = 'Enter the relative path from your root installation (where phpBB or MX-Publisher is located) to the Screenshots upload directory. If you stick to the file structure provided in the shipped package, enter \'pafiledb/images/screenshots/\'.';

//
// File
//
$lang['File_title'] = 'File';

$lang['Hotlink_prevent'] = 'Hotlink Prevention';
$lang['Hotlinl_prevent_info'] = 'Set this to yes if you don\'t want to allow hotlinks to the files';

$lang['Hotlink_allowed'] = 'Allowed domains for hotlink';
$lang['Hotlink_allowed_info'] = 'Allowed domains for hotlink (separated by a comma), for example, www.phpbb.com, www.forumimages.com';

$lang['Php_template'] = 'PHP in template';
$lang['Php_template_info'] = 'This will allow you to use php directly in the template files';

$lang['Max_filesize'] = 'Maximum Filesize';
$lang['Max_filesize_explain'] = 'Maximum filesize for Files. A value of 0 means \'unlimited\'. This Setting is restricted by your Server Configuration. For example, if your php Configuration only allows a maximum of 2 MB uploads, this cannot be overwritten by the Mod.';

$lang['Forbidden_extensions'] = 'Forbidden Extensions';
$lang['Forbidden_extensions_explain'] = 'Here you can add or delete the forbidden extensions. Seprate each extenstion with comma.';

$lang['Bytes'] = 'Bytes';
$lang['KB'] = 'KB';
$lang['MB'] = 'MB';

//
// Appearance
//
$lang['Appearance_title'] = 'Appearance';

$lang['File_pagination'] = 'File pagination';
$lang['File_pagination_explain'] = 'The number of files to show in a category before pagination.';

$lang['Sort_method'] = 'Sorting method';
$lang['Sort_method_explain'] = 'Define how files are sorted within its category.';

$lang['Sort_order'] = 'ASC or DESC sorting';
$lang['Sort_order_explain'] = '';

$lang['Topnum'] = 'Top Number';
$lang['Topnuminfo'] = 'This is how many files will be displayed on the Top X Downloaded files list';

$lang['Showva'] = 'Show \'View All Files\'';
$lang['Showvainfo'] = 'Choose whether or not you wish to have the \'View All Files\' category displayed with the other categories on the main page';

$lang['Use_simple_navigation'] = 'Simple Category Navigation';
$lang['Use_simple_navigation_explain'] = 'If you prefer, this will generate more simple categories and other navigation';

$lang['Cat_col'] = 'How many column of categories are to be listed (only used for \'Simple Category Navigation\')';

$lang['Nfdays'] = 'New File Days';
$lang['Nfdaysinfo'] = 'How many days a new file is to be listed with a \'New File\' icon. If this is set to 5, then all files added within the past 5 days will have the \'New File\' icon';

//
// Comments
//
$lang['Comments_title'] = 'Comments';
$lang['Comments_title_explain'] = 'Some comments settings are default settings, and can be overridden per category';

$lang['Use_comments'] = 'Comments';
$lang['Use_comments_explain'] = 'Enable comments for files, to be inserted in the forum';

$lang['Internal_comments'] = 'Internal or phpBB Comments';
$lang['Internal_comments_explain'] = 'Use internal comments, or phpBB comments';

$lang['Select_topic_id'] = 'Select phpBB Comments Topic!';

$lang['Internal_comments_phpBB'] = 'phpBB Comments';
$lang['Internal_comments_internal'] = 'Internal Comments';

$lang['Forum_id'] = 'phpBB Forum ID';
$lang['Forum_id_explain'] = 'If phpBB comments are used, this is the forum where the comments will be kept';

$lang['Autogenerate_comments'] = 'Autogenerate comments when fil are managed';
$lang['Autogenerate_comments_explain'] = 'When editing/adding a file, a notifying reply is posted in the file topic.';

$lang['Del_topic'] = 'Delete Topic';
$lang['Del_topic_explain'] = 'When you delete a file, do you want its comments topic to be deleted also?';

$lang['Comments_pag'] = 'Comments pagination';
$lang['Comments_pag_explain'] = 'The number of comments to show for the file before pagination.';

$lang['Allow_Wysiwyg'] = 'Use WYSIWYG editor';
$lang['Allow_Wysiwyg_explain'] = 'If enabled, the standard BBCode/HTML/Smilies input dialog is replaced by a WYSIWYG editor.';

$lang['Allow_links'] = 'Allow Links';
$lang['Allow_links_message'] = 'Default \'No Links\' Message';
$lang['Allow_links_explain'] = 'If links are not allowed this text will be displayed instead';

$lang['Allow_images'] = 'Allow Images';
$lang['Allow_images_message'] = 'Default \'No Images\' Message';
$lang['Allow_images_explain'] = 'If images are not allowed this text will be displayed instead';

$lang['Max_subject_char'] = 'Maximum Number of charcters in subject';
$lang['Max_subject_char_explain'] = 'If to big, you get an error message (Limit the subject).';

$lang['Max_desc_char'] = 'Maximum Number of charcters in description';
$lang['Max_desc_char_explain'] = 'If to big, you get an error message (Limit the subject).';

$lang['Max_char'] = 'Maximum Number of charcters in text';
$lang['Max_char_explain'] = 'If to big, you get an error message (Limit the comment).';

$lang['Format_wordwrap'] = 'Word wrapping';
$lang['Format_wordwrap_explain'] = 'Text control filter';

$lang['Format_truncate_links'] = 'Truncate Links';
$lang['Format_truncate_links_explain'] = 'Links are shortened, eg t ex \'www.mxbb-portal...\'';

$lang['Format_image_resize'] = 'Image resize';
$lang['Format_image_resize_explain'] = 'Resize images to this width (pixels)';

//
// Ratings
//
$lang['Ratings_title'] = 'Ratings';
$lang['Ratings_title_explain'] = 'Some ratings settings are default settings, and can be overridden per category';

$lang['Use_ratings'] = 'Ratings';
$lang['Use_ratings_explain'] = 'Enable ratings';

$lang['Votes_check_ip'] = 'Validate ratings - IP';
$lang['Votes_check_ip_explain'] = 'Only one vote per IP address is permitted.';

$lang['Votes_check_userid'] = 'Validate ratings - User';
$lang['Votes_check_userid_explain'] = 'Users may only vote once.';

//
// Instructions
//
$lang['Instructions_title'] = 'User Instructions';

$lang['Pre_text_name'] = 'File Submission Instructions';
$lang['Pre_text_explain'] = 'Activate Submission Instructions displayed to users at the top of the submission forum.';

$lang['Pre_text_header'] = 'File Submission Instructions Header';
$lang['Pre_text_body'] = 'File Submission Instructions Body';

$lang['Show'] = 'Show';
$lang['Hide'] = 'Hide';

//
// Notifications
//
$lang['Notifications_title'] = 'Notification';

$lang['Notify'] = 'Notify admin by';
$lang['Notify_explain'] = 'Choose which way to receive notices that new files have been uploaded';
$lang['PM'] = 'PM';

$lang['Notify_group'] = 'and groupmembers ';
$lang['Notify_group_explain'] = 'Also send notification to members in this group';

//
// Permissions
//
$lang['Permission_settings'] = 'Permission settings';

$lang['Auth_search'] = 'Search Permission';
$lang['Auth_search_explain'] = 'Allow search for specific type of users';

$lang['Auth_stats'] = 'Stats Permission';
$lang['Auth_stats_explain'] = 'Allow stats for specific type of users';

$lang['Auth_toplist'] = 'Toplist Permission';
$lang['Auth_toplist_explain'] = 'Allow toplist for specific type of users';

$lang['Auth_viewall'] = 'Viewall Permission';
$lang['Auth_viewall_explain'] = 'Allow viewall for specific type of users';

$lang['Settings'] = 'Configuration';
$lang['Settings_changed'] = 'Your settings have been successfully updated';

//
// Admin Panels - Categories
//
$lang['Panel_cat_title'] = 'Category administration';
$lang['Panel_cat_explain'] = 'You can use the Category Management section to add, edit, delete and reorder categories. In order to add files to your database, you must have at least one category created. You can select a link below to manage your categories.';

$lang['Use_default'] = 'Use default setting';


 
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
$lang['Catexplain'] = 'You can use the Category Management section to add, edit, delete and reorder categories. In order to add files to your database, you must have at least one category created. You can select a link below to manage your categories.';
$lang['Rcatexplain'] = 'You can reorder categories to change the position they are displayed in on the main page. To reorder the categories, change the numbers to the order you want them shown in. 1 will be showed first, 2 will be shown second, etc. This does not affect sub-categories.';
$lang['Catadded'] = 'The new category has been successfully added';
$lang['Catname'] = 'Category Name';
$lang['Catnameinfo'] = 'This will become the name of the category.';
$lang['Catdesc'] = 'Category Description';
$lang['Catdescinfo'] = 'This is a description of the files in the category';
$lang['Catparent'] = 'Parent Category';
$lang['Catparentinfo'] = 'If you want this category to be a sub-category, select the category you want it to be a sub-category of.';
$lang['Allow_file'] = 'Allow Adding file';
$lang['Allow_file_info'] = 'If you are not allowed to add files in this category it will be a higher level category.';
$lang['None'] = 'None';
$lang['Catedited'] = 'The category you selected has been successfully edited';
$lang['Delfiles'] = 'What do you want to do with the files in this category?';
$lang['Do_cat'] = 'What do you want to do with the sub category in this category?';
$lang['Move_to'] = 'Move to';
$lang['Catsdeleted'] = 'The categories you selected have been successfully deleted';
$lang['Cdelerror'] = 'You didn\'t select any categories to delete';
$lang['Rcatdone'] = 'The categories have been successfully re-ordered';

//
// Admin Panels - File Maintanance
//
$lang['Fchecker'] = 'File: Maintenance';
$lang['File_checker'] = 'File Maintenance';
$lang['File_checker_explain'] = 'Here you can perform a checking for all file in database and the file in the download directory.';
$lang['File_saftey'] = 'File maintenance will attempt to delete all files and screenshots that are currently not needed and will remove any file records where the file has been deleted and will clear all screenshots that are not found.<br /><br />If the files do not start with <FONT COLOR="#FF0000">{html_path}</FONT> then the files will be skipped for security reasons.<br /><br />Please make sure that <FONT COLOR="#FF0000">{html_path}</FONT> is the path that you use for your files.<br /><br />.';

$lang['File_checker_perform'] = 'Perform Checking';
$lang['Checker_saved'] = 'Total Saved Space';
$lang['Checker_sp1'] = 'Checking for records with missing files...';
$lang['Checker_sp2'] = 'Checking for records with missing screenshots...';
$lang['Checker_sp3'] = 'Deleting unused Files...';

//
// Admin Panels - Permissions
//
$lang['View'] = 'View';
$lang['Read'] = 'Read';
$lang['View_file'] = 'View File';
$lang['Delete_file'] = 'Delete File';
$lang['Edit_file'] = 'Edit File';
$lang['Upload'] = 'Upload File';
$lang['Approval'] = 'Approval';
$lang['Approval_edit'] = 'Approval Edit';
$lang['Download_file'] = 'Download File';
$lang['Rate'] = 'Rate';
$lang['View_comment'] = 'View Comments';
$lang['Post_comment'] = 'Post Comments';
$lang['Edit_comment'] = 'Edit Comments';
$lang['Delete_comment'] = 'Delete Comments';
$lang['Category_auth_updated'] = 'Category permissions updated';
$lang['Click_return_catauth'] = 'Click %sHere%s to return to Category Permissions';
$lang['Auth_Control_Category'] = 'Category Permissions Control';
$lang['Category_auth_explain'] = 'Here you can alter the authorisation levels of each category. Remember that changing the permission level of category will affect which users can carry out the various operations within them.';
$lang['Select_a_Category'] = 'Select a Category';
$lang['Look_up_Category'] = 'Look Up Category';
$lang['Category'] = 'Category';

$lang['Category_NONE'] = 'NONE';
$lang['Category_ALL'] = 'ALL';
$lang['Category_REG'] = 'REG';
$lang['Category_PRIVATE'] = 'PRIVATE';
$lang['Category_MOD'] = 'MOD';
$lang['Category_ADMIN'] = 'ADMIN';

//
// Admin Panels - Custom Fields
//
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
$lang['Field_data_info'] = 'Enter the options that the user can choose from. Separate each option with a new-line (carriage return).';
$lang['Field_regex'] = 'Regular Expression';
$lang['Field_regex_info'] = 'You may require the input field to match a regular expression %s(PCRE)%s.';
$lang['Field_order'] = 'Display Order';

//
// Admin Panels - License
//
$lang['License_title'] = 'License';
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

$lang['Click_return'] = 'Click %sHere%s to return to the previous page';
$lang['Click_edit_permissions'] = 'Click %sHere%s to edit the permissions for this category';

//JavaScript messages and PHP errors
$lang['Cat_name_missing'] = 'Please fill the category name field';
$lang['Cat_conflict'] = 'You can\'t have a category with no file in side a category that doesn\'t allow files';
$lang['Cat_id_missing'] = 'Please select a category';
$lang['Missing_field'] = 'Please complete all the required fields';

//
// Admin Panels - Fields Types
//
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

$lang['Fileadded_not_validated'] = 'The new file has been successfully added, but a moderator (admin) need to validate the file before approval.';

//
// Admin Panels - Toplists
//
$lang['toplist_sort_method']		= 'Toplist type';
$lang['toplist_display_options']	= 'Display options';
$lang['toplist_use_pagination']		= 'Use Pagination (Previous/Next \'Number of rows\')';
$lang['toplist_pagination']			= 'Number of rows';
$lang['toplist_filter_date']			= "Filter by time";
$lang['toplist_filter_date_explain']	= "- Show posts from last week, month, year...";
$lang['toplist_cat_id']				= 'Limit to category';
$lang['target_block']				= 'Associated (target) pafileDB Block';

//
// Admin Panels - Mini
//
$lang['mini_display_options']		= 'Display options';
$lang['mini_pagination']			= 'Number of rows';
$lang['mini_default_cat_id']			= 'Limit to category';

//
// Quickdl
//
$lang['Panel_title']							= 'pafileDB Mapping';
$lang['Panel_title_explain']					= 'Here you can associate portal dynamic blocks and pafileDB categories. The quickdl block will show the pafiledb category when the dynamic block is active.';

$lang['Map_pafiledb']							= 'Select a pafileDB category...';
$lang['Map_mxbb']								= '...to be mapped to this dynamic portal block';

$lang = array_merge($lang, array(
	//
	// Quickdl
	//
	'Quickdl_back'						=> 'Back',
	
	//
	// ACP
	//	
	'ACP_ADD'							=> 'Add',
	'ACP_ALL_DOWNLOADS'					=> 'All downloads',
	'ACP_ANNOUNCE_ENABLE'				=> 'Announce new downloads',
	'ACP_ANNOUNCE_ENABLE_EXPLAIN'		=> 'Set to Yes, if you like to announce new Downloads in a certain forum.',
	'ACP_ANNOUNCE_LOCK'					=> 'Lock announcement',
	'ACP_ANNOUNCE_LOCK_EXPLAIN'			=> 'Set to Yes, the topic will be locked.',
	'ACP_ANNOUNCE_ID'					=> 'Announcement forum',
	'ACP_ANNOUNCE_ID_EXPLAIN'			=> 'Enter here the ID of the forum, where you like to announce new downloads.',
	'ACP_ANNOUNCE_MSG'					=> 'Hello,

we have a new download!

[b]Title:[/b] %1$s
[b]Description:[/b] %2$s
[b]Category:[/b] %3$s
[b]Click %4$s to go to the download page![/b]

Have fun!',
	'ACP_ANNOUNCE_SETTINGS'				=> 'Announcement settings',
	'ACP_ANNOUNCE_TITLE'				=> '%1$s',
	'ACP_CAT_NAME_SHOW_YES'				=> 'yes',
	'ACP_CAT_NAME_SHOW_NO'				=> 'no',
	'ACP_NEW_CAT_NAME_SHOW'				=> 'Show on index upload',
	'ACP_NEW_CAT_NAME_SHOW_EXPLAIN'		=> 'Show category on upload section for groups that allowed to upload.<br /><strong>Note:</strong> Admins can always see all categories in upload section.',
	'ACP_ANNOUNCE_UP'					=> 'Announce download again',
	'ACP_ANNOUNCE_UP_EXPLAIN'			=> 'Activate, if you like to re-announce the download. The message will be sent as an update information',
	'ACP_ANNOUNCE_UP_MSG'				=> 'Hello,

we have an updated download!

[b]Title:[/b] %1$

[b]Description:[/b] %2$s

[b]Category:[/b] %3$s

[b]Click %4$s to go to the category![/b]

Have fun!',
	'ACP_ANNOUNCE_UP_TITLE'				=> '[UPD] %1$s',
	'ACP_BASIC'							=> 'Basic settings',
	'ACP_CAT'							=> 'Category',
	'ACP_CATEGORIES'					=> 'Categories',
	'ACP_CAT_DELETE'					=> 'Delete category',
	'ACP_CAT_DELETE_DONE'				=> 'Your category was successfully deleted',
	'ACP_CAT_DELETE_EXPLAIN'			=> 'Here you can delete a category.',
	'ACP_CAT_EDIT_DONE'					=> 'Your category was successfully updated',
	'ACP_CAT_EXIST'						=> 'The folder name already exists on your webspace!',
	'ACP_CAT_EXPLAIN'					=> 'Enter here the category, where your download should be listed in',
	'ACP_CAT_INDEX'						=> 'Categories Index',
	'ACP_CAT_NAME_ERROR'				=> 'You need to enter a folder name for your category!',
	'ACP_CAT_NEW'						=> 'Add a new category',
	'ACP_CAT_NEW_DONE'					=> 'Your new category was added and the folder created successfully on your webspace!',
	'ACP_CAT_NEW_EXPLAIN'				=> 'Here you can add a new category.',
	'ACP_CAT_NOT_EXIST'					=> 'The requested category does not exist!',
	'ACP_CAT_SELECT'					=> 'Here you can add, edit or delete categories.',
	'ACP_CLICK'							=> 'here',
	'ACP_CONFIG_SUCCESS'				=> 'The configuration was successfully updated',
	'ACP_COPY_NEW'						=> 'Copy as draft',
	'ACP_COST_ERROR'					=> 'You can\'t set negative costs for a download!<br />Enter 0 to make it free or any positive value.',
	'ACP_COST_EXPLAIN'					=> 'Here you can set, how much %1$s the users have to pay for this download. Set 0, to leave the download for free.',
	'ACP_COST_FREE'						=> 'Free',
	'ACP_COST_SHORT'					=> 'Costs',
	'ACP_DELETE_HAS_FILES'				=> 'There are still files in the category!<br />Please delete them or move them to another category first!',
	'ACP_DELETE_SUB_CATS'				=> 'Please delete first your sub categories!',
	'ACP_DEL_CAT'						=> 'Are you sure, you want to delete the category <strong>%1$s</strong>?<br />The physical folder on your web server - if there are no more downloads inside - will be removed too!',
	'ACP_DEL_CAT_EXPLAIN'				=> 'Here you can delete an existing category.',
	'ACP_DEL_DOWNLOAD'					=> 'Delete a download',
	'ACP_DEL_DOWNLOADS_TO'				=> 'Move downloads to',
	'ACP_DEL_DOWNLOAD_YES'				=> 'Delete category including the downloads?',
	'ACP_DEL_SUBS'						=> 'Delete sub-categories',
	'ACP_DEL_SUBS_TO'					=> 'Move sub-categories to',
	'ACP_DEL_SUBS_YES'					=> 'Delete category including the sub-categories?',
	'ACP_DOWNLOADS'						=> 'Downloads',
	'ACP_DOWNLOAD_DELETED'				=> 'Your download was successfully deleted.',
	'ACP_DOWNLOAD_UPDATED'				=> 'Your download was successfully updated',
	'ACP_DOWNLOAD_SYSTEM'				=> 'Download system',
	'ACP_EDIT_CAT'						=> 'Edit category',
	'ACP_EDIT_CAT_EXPLAIN'				=> 'Here you can edit an existing category.',
	'ACP_EDIT_DOWNLOADS'				=> 'Edit downloads',
	'ACP_EDIT_DOWNLOADS_EXPLAIN'		=> 'Here you can edit the selected download.',
	'ACP_EDIT_FILENAME'					=> 'Saved File',
	'ACP_EDIT_FILENAME_EXPLAIN'			=> '<strong>IMPORTANT:</strong> If you change the file name over here, there will be no further check, if the file really exists on your webspace. <strong>You need to upload the new file	via FTP and manually delete the old one!</strong>',
	'ACP_EDIT_SUB_CAT_EXPLAIN'			=> 'The already created subdirectory can\'t be edited. So if you like to have a different subdirectory, you need to delete the current category and create a new one!',
	'ACP_FILE_TOO_BIG'					=> 'The file is bigger, than your host allows!',
	'ACP_FORUM_ID_ERROR'				=> 'The entered forum ID does not exist!',
	'ACP_FILES_INDEX'					=> 'Download Manager',
	'ACP_MANAGE_DOWNLOADS_EXPLAIN'		=> 'Here you can add, edit or delete your downloads.',
	'ACP_MULTI_DOWNLOAD'				=> '%d downloads',
	'ACP_NEED_DATA'						=> 'You need to fill all fields!',
	'ACP_NEW_ADDED'						=> 'Your entry was successfully added to the database',
	'ACP_NEW_CAT'						=> 'New category',
	'ACP_NEW_CAT_DESC'					=> 'Description of the category',
	'ACP_NEW_CAT_DESC_EXPLAIN'			=> 'Enter a useful description of your new category.<br />BB-Codes, smiles and links will be recognised automatically.',
	'ACP_NEW_CAT_NAME'					=> 'Category name',
	'ACP_NEW_CAT_PARENT'				=> 'Parent category',
	'ACP_NEW_COPY_DOWNLOAD'				=> 'New download with copy',
	'ACP_NEW_COPY_DOWNLOAD_EXPLAIN'		=> 'You selected to copy an already existing download for your new download. This will save a little time, especially if you like to upload ie a new version',
	'ACP_NEW_DESC'						=> 'Description',
	'ACP_NEW_DESC_EXPLAIN'				=> 'Enter here a description for your download.',
	'ACP_NEW_DL_CAT'					=> 'Category',
	'ACP_NEW_DL_CAT_EXPLAIN'			=> 'Select here the category, where your download should stay in.',
	'ACP_NEW_DOWNLOAD'					=> 'New download',
	'ACP_NEW_DOWNLOAD_EXPLAIN'			=> 'Here you can add new downloads.',
	'ACP_NEW_DOWNLOAD_SIZE'				=> 'The maximum size of the file, which is allowed by your host, is <strong>%1$s %2$s</strong>! Due to the upload time you might need, this value can be lower!',
	'ACP_NEW_FILENAME'					=> 'File name',
	'ACP_NEW_FILENAME_EXPLAIN'			=> 'Select the file to upload.',
	'ACP_NEW_SUB_CAT_EXPLAIN'			=> 'Enter here the folder name, which you like to use on your webspace for this category (without slashes!).<br />This folder will then be created automatically under your root/ext/orynider/pafiledb/uploads/ folder.<br />Allowed characters are a-z, A-Z, 0-9, the hyphen ( - ) and the underscore ( _ ) signs.',
	'ACP_NEW_SUB_CAT_NAME'				=> 'Path name for the category',
	'ACP_NEW_TITLE'						=> 'Title',
	'ACP_NEW_TITLE_EXPLAIN'				=> 'Enter here the title for your new download.',
	'ACP_NEW_VERSION'					=> 'Version',
	'ACP_NEW_VERSION_EXPLAIN'			=> 'Enter here the version of your download.',
	'ACP_NO_CAT'						=> 'There are no categories available!<br />You first need to create at least one category, before you can start to add downloads!',
	'ACP_NO_CAT_ID'						=> 'No Cat ID',
	'ACP_NO_CAT_PARENT'					=> 'no parent category',
	'ACP_NO_CAT_UPLOAD'					=> 'There are no categories available!<br />You first need to create at least one category, before you can start adding files!',
	'ACP_NO_DOWNLOADS'					=> 'No Downloads',
	'ACP_NO_FILENAME'					=> 'You have to enter a file, which belongs to your upload!',
	'ACP_PAGINATION_ACP'				=> 'Set number of pagination on Manage Downloads page in ACP',
	'ACP_PAGINATION_ACP_EXPLAIN'		=> 'Set here, how much entries you want to see on the Manage Downloads page in ACP. <em>Default is 5.</em>',
	'ACP_PAGINATION_DOWNLOADS'			=> 'Set number of pagination on category page',
	'ACP_PAGINATION_DOWNLOADS_EXPLAIN'	=> 'Set here, how much entries you want to see on the category page. <em>Default is 25.</em>',
	'ACP_PAGINATION_ERROR_ACP'			=> 'You cannot set a value smaller than 5!',
	'ACP_PAGINATION_ERROR_USER'			=> 'You cannot set a value smaller than 3!',
	'ACP_PAGINATION_ERROR_DOWNLOADS'	=> 'You cannot set a value smaller than 10!',
	'ACP_PAGINATION_USER'				=> 'Set number of pagination on downloads page',
	'ACP_PAGINATION_USER_EXPLAIN'		=> 'Set here, how much entries you want to see on the downloads page. <em>Default is 3.</em>',
	'ACP_PARENT_OPTION_NAME'			=> 'Select a category',
	'ACP_REALLY_DELETE'					=> 'Are you sure, you want to delete your download?<br />The physical file on your web server will be deleted too!',
	'ACP_SINGLE_DOWNLOAD'				=> '1 download',
	'ACP_SORT_ASC'						=> 'Ascending',
	'ACP_SORT_CAT'						=> 'Category',
	'ACP_SORT_DESC'						=> 'Descending',
	'ACP_SORT_DIRECTION'				=> 'sort direction',
	'ACP_SORT_KEYS'						=> 'sort by ',
	'ACP_SORT_TITLE'					=> 'Title',
	'ACP_SUB_DL_CAT'					=> 'Subcategory',
	'ACP_SUB_NO_CAT'					=> '-----------',
	'ACP_SUB_DL_CAT_EXPLAIN'			=> 'Select here the subcategory.',
	'ACP_SUB_HAS_CAT_EXPLAIN'			=> 'This category has subcategories so cannot be linked to other category.',
	'ACP_UPLOAD_FILE_EXISTS'			=> 'The file you like to upload, does already exist in this category!',
	'ACP_WRONG_CHAR'					=> 'You entered a wrong character in the path name for the category!<br />Following characters are allowed: a-z, A-Z, 0-9, as well the hyphen ( - ) and the underscore ( _ )!',
	'ACP_MANAGE_CONFIG_EXPLAIN'			=> 'Here you can set a few basic values.',
	'ACP_SET_USERNAME'					=> 'Username for a transfer',
	'ACP_SET_USERNAME_EXPLAIN'			=> 'Here you can set a username, to which the download costs should be transferred to. Leave empty, if none should receive the above named costs.',
	'ACP_FTP_OR_UPLOAD'					=> 'You can do only a FTP upload <strong>OR</strong> normal upload!',
	'ACP_NEW_FTP_FILENAME_EXPLAIN'		=> 'Enter here the file name (ie. sample.zip), if you like to use the FTP upload method.',
	'ACP_NEW_FTP_FILENAME'				=> 'FTP file name',
	'ACP_UPLOAD_METHOD'					=> 'Upload Method',
	'ACP_UPLOAD_METHOD_EXPLAIN'			=> 'You can add a new upload via FTP or directly. If you are going to use the FTP upload method, the file needs to be uploaded to the correct category <strong>before</strong> you enter it here! You only can use on or the other method at a time!',
	'ACP_UPLOAD_FILE_NOT_EXISTS'		=> 'The file does not exists in the named category. Since you selected the FTP upload method, this file needs to be uploaded via FTP in the correct directory <strong>BEFORE</strong> you can add it!',
));
?>