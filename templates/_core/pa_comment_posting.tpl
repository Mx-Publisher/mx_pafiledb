<!-- INCLUDE pa_header.tpl -->
<!-- BEGIN switch_bbcodes -->
<script language="JavaScript" type="text/javascript">
<!--
// bbCode control by
// subBlue design
// www.subBlue.com

// Startup variables
var imageTag = false;
var theSelection = false;

// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
                && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
                && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
var is_moz = 0;

var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac = (clientPC.indexOf("mac")!=-1);

// Helpline messages
b_help = "{L_BBCODE_B_HELP}";
i_help = "{L_BBCODE_I_HELP}";
u_help = "{L_BBCODE_U_HELP}";
q_help = "{L_BBCODE_Q_HELP}";
c_help = "{L_BBCODE_C_HELP}";
l_help = "{L_BBCODE_L_HELP}";
o_help = "{L_BBCODE_O_HELP}";
p_help = "{L_BBCODE_P_HELP}";
w_help = "{L_BBCODE_W_HELP}";
a_help = "{L_BBCODE_A_HELP}";
s_help = "{L_BBCODE_S_HELP}";
f_help = "{L_BBCODE_F_HELP}";

// Define the bbCode tags
bbcode = new Array();
bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[quote]','[/quote]','[code]','[/code]','[list]','[/list]','[list=]','[/list]','[img]','[/img]','[url]','[/url]');
imageTag = false;

// Shows the help messages in the helpline window
function helpline(help) {
	document.post.helpbox.value = eval(help + "_help");
}

// Replacement for arrayname.length property
function getarraysize(thearray) {
	for (i = 0; i < thearray.length; i++) {
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null))
			return i;
		}
	return thearray.length;
}

// Replacement for arrayname.push(value) not implemented in IE until version 5.5
// Appends element to the array
function arraypush(thearray,value) {
	thearray[ getarraysize(thearray) ] = value;
}

// Replacement for arrayname.pop() not implemented in IE until version 5.5
// Removes and returns the last element of an array
function arraypop(thearray) {
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
}

function emoticon(text) {
	var txtarea = document.post.message;
	text = ' ' + text + ' ';
	if (txtarea.createTextRange && txtarea.caretPos) {
		var caretPos = txtarea.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
		txtarea.focus();
	} else {
		txtarea.value  += text;
		txtarea.focus();
	}
}

function bbfontstyle(bbopen, bbclose) {
	var txtarea = document.post.message;

	if ((clientVer >= 4) && is_ie && is_win) {
		theSelection = document.selection.createRange().text;
		if (!theSelection) {
			txtarea.value += bbopen + bbclose;
			txtarea.focus();
			return;
		}
		document.selection.createRange().text = bbopen + theSelection + bbclose;
		txtarea.focus();
		return;
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozWrap(txtarea, bbopen, bbclose);
		return;
	}
	else
	{
		txtarea.value += bbopen + bbclose;
		txtarea.focus();
	}
	storeCaret(txtarea);
}

function bbstyle(bbnumber) {
	var txtarea = document.post.message;

	txtarea.focus();
	donotinsert = false;
	theSelection = false;
	bblast = 0;

	if (bbnumber == -1) { // Close all open tags & default button names
		while (bbcode[0]) {
			butnumber = arraypop(bbcode) - 1;
			txtarea.value += bbtags[butnumber + 1];
			buttext = eval('document.post.addbbcode' + butnumber + '.value');
			eval('document.post.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
		}
		imageTag = false; // All tags are closed including image tags :D
		txtarea.focus();
		return;
	}

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text; // Get text selection
		if (theSelection) {
			// Add tags around selection
			document.selection.createRange().text = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
			txtarea.focus();
			theSelection = '';
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozWrap(txtarea, bbtags[bbnumber], bbtags[bbnumber+1]);
		return;
	}

	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < bbcode.length; i++) {
		if (bbcode[i] == bbnumber+1) {
			bblast = i;
			donotinsert = true;
		}
	}

	if (donotinsert) {		// Close all open tags up to the one just clicked & default button names
		while (bbcode[bblast]) {
				butnumber = arraypop(bbcode) - 1;
				txtarea.value += bbtags[butnumber + 1];
				buttext = eval('document.post.addbbcode' + butnumber + '.value');
				eval('document.post.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
				imageTag = false;
			}
			txtarea.focus();
			return;
	} else { // Open tags

		if (imageTag && (bbnumber != 14)) {		// Close image tag before adding another
			txtarea.value += bbtags[15];
			lastValue = arraypop(bbcode) - 1;	// Remove the close image tag from the list
			document.post.addbbcode14.value = "Img";	// Return button back to normal state
			imageTag = false;
		}

		// Open tag
		txtarea.value += bbtags[bbnumber];
		if ((bbnumber == 14) && (imageTag == false)) imageTag = 1; // Check to stop additional tags after an unclosed image tag
		arraypush(bbcode,bbnumber+1);
		eval('document.post.addbbcode'+bbnumber+'.value += "*"');
		txtarea.focus();
		return;
	}
	storeCaret(txtarea);
}

// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close)
{
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2)
		selEnd = selLength;

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + open + s2 + close + s3;
	return;
}

// Insert at Claret position. Code from
// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}

//-->
</script>
<!-- END switch_bbcodes -->

<script language="javascript">
<!--
var postmaxchars = {MESSAGE_LENGTH};
function checklength(theform)
{
	if (postmaxchars != 0)
	{
		message = "";
	}
  	else
	{
		message = "";
	}
	alert("{L_MSG_LENGTH_1}"+theform.message.value.length+"{L_MSG_LENGTH_2}\n\r\n\r{L_MSG_LENGTH_3}"+postmaxchars+"{L_MSG_LENGTH_4}\n\r\n\r{L_MSG_LENGTH_5}"+(postmaxchars-theform.message.value.length)+"{L_MSG_LENGTH_6}");
}

function checkForm() {

	formErrors = false;

	if (document.post.message.value.length < 2) {
		formErrors = "{L_EMPTY_MESSAGE}";
	}

	if (formErrors) {
		alert(formErrors);
		return false;
	} else {
		if (bbstyle)
		{
			bbstyle(-1);
		}
		//formObj.preview.disabled = true;
		//formObj.submit.disabled = true;
		return true;
	}
}
//-->
</script>

<!-- BEGIN tinyMCE -->
<script language="javascript" type="text/javascript" src="{tinyMCE.PATH}modules/mx_shared/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
   tinyMCE.init({
      	mode : "textareas",
		language : "{tinyMCE.LANG}",
        docs_language : "{tinyMCE.LANG}",

        apply_source_formatting : "true",
        cleanup : "true",
        inline_styles : "true",
        convert_fonts_to_spans : "true",
        fix_list_elements : "true",
        fix_table_elements : "true",
        force_p_newlines : "true",
        remove_trailing_nbsp : "true",

        plugins : "style,advimage,advlink,preview,searchreplace,contextmenu,paste,fullscreen",

        theme : "advanced",
        theme_advanced_blockformats : "p,h1,h2,h3,h4,h5,h6",

        theme_advanced_fonts : "Verdana=verdana,arial,helvetica,sans-serif;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace",

        theme_advanced_buttons1 : "newdocument,separator,cut,copy,paste,pastetext,pasteword,separator,formatselect,styleselect,fontsizeselect",

		theme_advanced_buttons2: "bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,sup,sub,separator,link,unlink,image,separator,forecolor,separator,undo,redo",

		theme_advanced_buttons3: "",

		theme_advanced_disable : "",

		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",

		content_css : "{tinyMCE.TEMPLATE}",
		theme_advanced_styles : "Tiny Text=copyright;Small Text=gensmall;Normal Text=genmed;Big Text=gen;Code=code;Quote=quote",

		table_styles : "Layout=forumline",
		table_cell_styles : "Table cell 1=row1;Table cell 2=row2;Table cell 3=row3",
		table_row_styles : "Table row 1=oddrow",
		table_default_border : "0",

		document_base_url : "{tinyMCE.PATH}index.php",
		relative_urls : "true",

		extended_valid_elements : "a[*],img[*],table[*],tr[*],td[*],div[*],form[*],input[*]"
	});
</script>
<!-- END tinyMCE -->

<!-- BEGIN tinyMCE_admin -->
<script language="javascript" type="text/javascript" src="{tinyMCE_admin.PATH}modules/mx_shared/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
   tinyMCE.init({
		mode : "textareas",
		language : "{tinyMCE_admin.LANG}",
        docs_language : "{tinyMCE_admin.LANG}",

        apply_source_formatting : "true",
        cleanup : "true",
       	inline_styles : "true",
        convert_fonts_to_spans : "true",
        fix_list_elements : "true",
        fix_table_elements : "true",
        force_p_newlines : "true",
        remove_trailing_nbsp : "true",

        plugins : "table,advimage,advlink,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable",

        theme : "advanced",
        theme_advanced_blockformats : "p,h1,h2,h3,h4,h5,h6",

        theme_advanced_fonts : "Verdana=verdana,arial,helvetica,sans-serif;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace",

        theme_advanced_buttons1_add_before : "newdocument,separator",
		theme_advanced_buttons1_add : "fontselect,fontsizeselect",

		theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,separator,forecolor",
		theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",

		theme_advanced_buttons3_add_before : "tablecontrols,separator",
		theme_advanced_buttons3_add : "print,separator,ltr,rtl,separator,fullscreen",

		theme_advanced_disable : "",

		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",

		theme_advanced_path_location : "bottom",
		theme_advanced_statusbar_location : "bottom",

		content_css : "{tinyMCE_admin.TEMPLATE}",
		theme_advanced_styles : "Tiny Text=copyright;Small Text=gensmall;Normal Text=genmed;Big Text=gen;Code=code;Quote=quote",

		table_styles : "Layout=forumline",
		table_cell_styles : "Table cell 1=row1;Table cell 2=row2;Table cell 3=row3",
		table_row_styles : "Table row 1=oddrow",
		table_default_border : "0",

		document_base_url : "{tinyMCE_admin.PATH}index.php",
		relative_urls : "true",

		extended_valid_elements : "a[*],img[*],table[*],tr[*],td[*],div[*],form[*],input[*]"
	});
</script>
<!-- END tinyMCE_admin -->

<form action="{S_POST_ACTION}" method="post" name="post" onsubmit="return checkForm(this)">

<!-- IF PREVIEW -->
<br />
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="forumline">
	<tr>
		<th class="thHead" colspan="2" height="25">{L_PREVIEW}</th>
	</tr>
	<tr>
		<td class="row1" valign="top"><span class="postbody">{PRE_COMMENT}</span></td>
	</tr>
</table>
<br />
<!-- ENDIF -->
<table width="100%" cellpadding="2" cellspacing="2">
  <tr>
	<td valign="bottom">
		<span class="nav"><a href="{U_DOWNLOAD_HOME}" class="nav">{DOWNLOAD}</a><!-- BEGIN navlinks -->&nbsp;&raquo;&nbsp;<a href="{navlinks.U_VIEW_CAT}" class="nav">{navlinks.CAT_NAME}</a><!-- END navlinks -->&nbsp;&raquo;&nbsp;<a href="{U_FILE_NAME}" class="nav">{FILE_NAME}</a>&nbsp;&raquo;&nbsp;{L_COMMENT_ADD}</span>
	</td>
  </tr>
</table>

<table border="0" cellpadding="4" cellspacing="1" width="100%" class="forumline">
	<tr>
		<th colspan="2" class="thHead" height="25"><b>{L_COMMENT_ADD}</b></th>
	</tr>
	<tr>
		<td colspan="2" class="row1" ><span class="gen"><b>{L_COMMENT_TITLE}</b></span></td>
	</tr>
	<tr>
		<td colspan="2" class="row1"><input type="text" name="subject" size="45" maxlength="60" style="width:400px" tabindex="2" class="post" value="{TITLE}" /></span></td>
	</tr>
	<tr>
	  	<td colspan="2" class="row1" valign="top"><span class="gen"><b>{L_COMMENT}</b></span></td>
	</tr>
	<!-- BEGIN switch_bbcodes -->
	<tr>
	  <td colspan="2" class="row2" valign="top"><span class="gen">
		<table border="0" cellspacing="0" cellpadding="1">
		  <tr align="center" valign="middle">
			<td><span class="genmed">
			  <input type="button" class="button" accesskey="b" name="addbbcode0" value=" B " style="font-weight:bold; width: 20px" onClick="bbstyle(0)" onMouseOver="helpline('b')" />
			  </span></td>
			<td><span class="genmed">
			  <input type="button" class="button" accesskey="i" name="addbbcode2" value=" i " style="font-style:italic; width: 20px" onClick="bbstyle(2)" onMouseOver="helpline('i')" />
			  </span></td>
			<td><span class="genmed">
			  <input type="button" class="button" accesskey="u" name="addbbcode4" value=" u " style="text-decoration: underline; width: 20px" onClick="bbstyle(4)" onMouseOver="helpline('u')" />
			  </span></td>
			<td><span class="genmed">
			  <input type="button" class="button" accesskey="q" name="addbbcode6" value="Quote" style="width: 45px" onClick="bbstyle(6)" onMouseOver="helpline('q')" />
			  </span></td>
			<td><span class="genmed">
			  <input type="button" class="button" accesskey="c" name="addbbcode8" value="Code" style="width: 40px" onClick="bbstyle(8)" onMouseOver="helpline('c')" />
			  </span></td>
			<td><span class="genmed">
			  <input type="button" class="button" accesskey="l" name="addbbcode10" value="List" style="width: 35px" onClick="bbstyle(10)" onMouseOver="helpline('l')" />
			  </span></td>
			<td><span class="genmed">
			  <input type="button" class="button" accesskey="o" name="addbbcode12" value="List=" style="width: 40px" onClick="bbstyle(12)" onMouseOver="helpline('o')" />
			  </span></td>
			<td><span class="genmed">
			  <input type="button" class="button" accesskey="p" name="addbbcode14" value="Img" style="width: 35px"  onClick="bbstyle(14)" onMouseOver="helpline('p')" />
			  </span></td>
			<td><span class="genmed">
			  <input type="button" class="button" accesskey="w" name="addbbcode16" value="URL" style="text-decoration: underline; width: 35px" onClick="bbstyle(16)" onMouseOver="helpline('w')" />
			  </span></td>
		  </tr>
		  <tr>
			<td colspan="9">
			  <table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
				  <td><span class="genmed">
				  {L_FONT_COLOR}:
					<select name="addbbcode18" onChange="bbfontstyle('[color=' + this.form.addbbcode18.options[this.form.addbbcode18.selectedIndex].value + ']', '[/color]')" onMouseOver="helpline('s')">
					  <option style="color:black; background-color: #FFFFFF " value="{T_FONTCOLOR1}" class="genmed">{L_COLOR_DEFAULT}</option>
					  <option style="color:darkred; background-color: #DEE3E7" value="darkred" class="genmed">{L_COLOR_DARK_RED}</option>
					  <option style="color:red; background-color: #DEE3E7" value="red" class="genmed">{L_COLOR_RED}</option>
					  <option style="color:orange; background-color: #DEE3E7" value="orange" class="genmed">{L_COLOR_ORANGE}</option>
					  <option style="color:brown; background-color: #DEE3E7" value="brown" class="genmed">{L_COLOR_BROWN}</option>
					  <option style="color:yellow; background-color: #DEE3E7" value="yellow" class="genmed">{L_COLOR_YELLOW}</option>
					  <option style="color:green; background-color: #DEE3E7" value="green" class="genmed">{L_COLOR_GREEN}</option>
					  <option style="color:olive; background-color: #DEE3E7" value="olive" class="genmed">{L_COLOR_OLIVE}</option>
					  <option style="color:cyan; background-color: #DEE3E7" value="cyan" class="genmed">{L_COLOR_CYAN}</option>
					  <option style="color:blue; background-color: #DEE3E7" value="blue" class="genmed">{L_COLOR_BLUE}</option>
					  <option style="color:darkblue; background-color: #DEE3E7" value="darkblue" class="genmed">{L_COLOR_DARK_BLUE}</option>
					  <option style="color:indigo; background-color: #DEE3E7" value="indigo" class="genmed">{L_COLOR_INDIGO}</option>
					  <option style="color:violet; background-color: #DEE3E7" value="violet" class="genmed">{L_COLOR_VIOLET}</option>
					  <option style="color:white; background-color: #DEE3E7" value="white" class="genmed">{L_COLOR_WHITE}</option>
					  <option style="color:black; background-color: #DEE3E7" value="black" class="genmed">{L_COLOR_BLACK}</option>
					</select> <br />
					{L_FONT_SIZE}: <select name="addbbcode20" onChange="bbfontstyle('[size=' + this.form.addbbcode20.options[this.form.addbbcode20.selectedIndex].value + ']', '[/size]');this.selectedIndex=0;" onMouseOver="helpline('f')">
					  <option value="0" class="genmed">{L_FONT_SIZE}</option>
					  <option value="7" class="genmed">{L_FONT_TINY}</option>
					  <option value="9" class="genmed">{L_FONT_SMALL}</option>
					  <option value="12" selected class="genmed">{L_FONT_NORMAL}</option>
					  <option value="18" class="genmed">{L_FONT_LARGE}</option>
					  <option  value="24" class="genmed">{L_FONT_HUGE}</option>
					</select>
					</span></td>
				  <td nowrap="nowrap" align="right"><span class="gensmall"><a href="javascript:bbstyle(-1)" class="genmed" onMouseOver="helpline('a')">{L_BBCODE_CLOSE_TAGS}</a></span></td>
				</tr>
			  </table>
			</td>
		  </tr>
		  <tr>
			<td colspan="9"> <span class="gensmall">
			  <input type="text" name="helpbox" size="45" maxlength="100" style="width:400px; font-size:10px" class="helpline" value="{L_STYLES_TIP}" />
			  </span></td>
		  </tr>
		</table>
        </td>
	</tr>
	<!-- END switch_bbcodes -->

  	<tr>
		<td colspan="2" valign="middle" class="row1">
		<table width="100%" border="0" cellspacing="0" cellpadding="1">
			<tr>
				<td colspan="{S_SMILIES_COLSPAN}" class="genmed">
					<b>{L_EMOTICONS}</b>
				</td>
			</tr>
			<tr valign="middle">
				<td>
				<!-- BEGIN smilies_row -->
				<!-- BEGIN smilies_col -->
				<a href="javascript:emoticon('{smilies_row.smilies_col.SMILEY_CODE}')"><img src="{smilies_row.smilies_col.SMILEY_IMG}" border="0" alt="{smilies_row.smilies_col.SMILEY_DESC}" title="{smilies_row.smilies_col.SMILEY_DESC}" /></a>
				<!-- END smilies_col -->
				<!-- END smilies_row -->
				</td>
			</tr>
			<!-- BEGIN switch_smilies_extra -->
			<tr>
				<td colspan="{S_SMILIES_COLSPAN}"><span  class="nav"><a href="{U_MORE_SMILIES}" onclick="window.open('{U_MORE_SMILIES}', '_phpbbsmilies', 'HEIGHT=300,resizable=yes,scrollbars=yes,WIDTH=250');return false;" target="_phpbbsmilies" class="nav">{L_MORE_SMILIES}</a></span></td>
			</tr>
			<!-- END switch_smilies_extra -->
		</table>
		</td>
	</tr>

	<tr>
		<td colspan="2" class="row1">
			<span class="gen">
			<textarea name="message" rows="30" cols="35" wrap="virtual" style="width:400px" tabindex="3" class="post" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);">{COMMENT}</textarea>
			</span>
		</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen"><B>{L_OPTIONS}</b></span><br /><span class="gensmall">{HTML_STATUS}<br />{BBCODE_STATUS}<br />{SMILIES_STATUS}<br />{LINKS_STATUS}<br />{IMAGES_STATUS}</span></td>
		<td class="row2"><span class="gen">{L_COMMENT_EXPLAIN}<br /><a href="javascript:checklength(document.post);">{L_CHECK_MSG_LENGTH}</a></span></td>
	</tr>
	<tr>
	  <td class="cat" colspan="2" align="center" height="28"> {S_HIDDEN_FORM_FIELDS}<input type="submit" tabindex="5" name="preview" class="mainoption" value="{L_PREVIEW}" />&nbsp;<input type="submit" accesskey="s" tabindex="6" name="submit" class="mainoption" value="{L_SUBMIT}" /></td>

	</tr>
  </table>
 </form>

<!-- INCLUDE pa_footer.tpl -->












