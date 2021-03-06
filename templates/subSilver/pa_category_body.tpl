<!-- INCLUDE pa_header.tpl -->
<table width="100%" cellpadding="2" cellspacing="2">
  <tr>
	<td valign="bottom">
		<span class="nav"><a href="{U_DOWNLOAD}" class="nav ask target-block_{BLOCK_ID}">{DOWNLOAD}</a><!-- BEGIN navlinks -->&nbsp;&raquo;&nbsp;<a href="{navlinks.U_VIEW_CAT}" class="nav ask target-block_{BLOCK_ID}">{navlinks.CAT_NAME}</a><!-- END navlinks --></span>
	</td>
  </tr>
</table>

<!-- IF CAT_NAV_STANDARD -->
<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
  	<tr>
		<th class="thCornerL" width="6%">&nbsp;</th>
		<th class="thTop">&nbsp;{L_CATEGORY}&nbsp;</th>
		<th class="thCornerR" width="10%">&nbsp;{L_LAST_FILE}&nbsp;</th>
		<th class="thCornerR" width="8%">&nbsp;{L_FILES}&nbsp;</th>
  	</tr>
	<!-- BEGIN no_cat_parent -->
	<!-- IF no_cat_parent.IS_HIGHER_CAT -->
	<tr>
		<td class="cat" colspan="2" valign="middle"><a href="{no_cat_parent.U_CAT}" class="cattitle ask target-block_{BLOCK_ID}">{no_cat_parent.CAT_NAME}</a></td>
		<td class="rowpic" colspan="2" align="right">&nbsp;</td>
	</tr>
	<!-- ELSE -->
	<tr>
		<td class="row1" valign="middle" align="center"><a href="{no_cat_parent.U_CAT}" class="cattitle ask target-block_{BLOCK_ID}"><img src="{no_cat_parent.CAT_IMAGE}" border="0" alt="{no_cat_parent.CAT_NEW_FILE}"></a></td>
		<td class="row1" valign="middle" onmouseout="this.className='row1';" onmouseover="this.className='row2';" onclick="window.location.href='{no_cat_parent.U_CAT}';"><a href="{no_cat_parent.U_CAT}" class="cattitle ask target-block_{BLOCK_ID}">{no_cat_parent.CAT_NAME}</a><br><span class="genmed">{no_cat_parent.CAT_DESC}</span><span class="gensmall">{no_cat_parent.SUB_CAT}</span></b></td>
		<td class="row2" align="center" valign="middle" nowrap="nowrap"><span class="genmed">{no_cat_parent.LAST_FILE}</span></td>
		<td class="row2" align="center" valign="middle"><span class="genmed">{no_cat_parent.FILECAT}</span></td>
	</tr>
	<!-- ENDIF -->
	<!-- END no_cat_parent -->
  	<tr>
		<td class="cat" colspan="4">&nbsp;</td>
  	</tr>
</table>
<br />
<!-- ENDIF -->


<!-- IF CAT_NAV_SIMPLE -->
<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
  	<tr>
		<th class="thHead" colspan="2">{L_CATEGORY}</th>
  	</tr>
  	<tr>
  		<td class="row1" colspan="2">
			<table border="0" cellpadding="2" cellspacing="1" width="100%" >
			<!-- BEGIN catcol -->
				<tr>
				<!-- BEGIN no_cat_parent -->
					<td width="{WIDTH}%">
						<table border="0" cellpadding="2" cellspacing="2" width="100%">
							<tr>
								<td>
									<a href="{catcol.no_cat_parent.U_CAT}"><img src="{catcol.no_cat_parent.CAT_IMAGE}" alt="{catcol.no_cat_parent.CAT_NAME}" align="absmiddle" border="0" /></a>
								</td>
								<td width="100%" valign="middle" nowrap="nowrap">
									<a href="{catcol.no_cat_parent.U_CAT}"  class="cattitle">{catcol.no_cat_parent.CAT_NAME}</a>&nbsp;<span class="gensmall">({catcol.no_cat_parent.FILECAT})</span><br>
									{catcol.no_cat_parent.SUB_CAT}
								</td>
							</tr>
						</table>
					</td>
				<!-- END no_cat_parent -->
      			</tr>
			<!-- END catcol -->
			</table>
  	 	</td>
  	</tr>
</table>
<br />
<!-- ENDIF -->

<!-- IF FILELIST -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="forumline"><tr><td>
	<!-- IF ORIGINAL_STYLE -->
	<table width="100%" cellpadding="4" cellspacing="1">
	  <tr>
		<th class="thCornerL" width="5%">&nbsp;</th>
		<th class="thTop" width="57%">&nbsp;{L_FILE}&nbsp;</th>
		<th class="thTop" width="15%">&nbsp;{L_UPDATE_TIME}&nbsp;</th>
		<th class="thTop" width="10%">&nbsp;{L_DOWNLOADS}&nbsp;</th>
		<!-- IF SHOW_RATINGS -->
		<th class="thTop" width="10%">&nbsp;{L_RATING}&nbsp;</th>
		<!-- ENDIF -->
		<th class="thCornerR" width="3%">&nbsp;</th>
	  </tr>

	<!-- BEGIN file_rows -->
	  <tr>
		<td class="row1" align="center" valign="middle"><a href="{file_rows.U_FILE}" class="topictitle ask target-block_{BLOCK_ID}"><img src="{file_rows.PIN_IMAGE}" border="0"></a></td>
		<td class="row1" valign="middle" onmouseout="this.className='row1';" onmouseover="this.className='row2';" onclick="window.location.href='{file_rows.U_FILE}';"><a href="{file_rows.U_FILE}" class="topictitle ask target-block_{BLOCK_ID}">{file_rows.FILE_NAME}</a>&nbsp;<!-- IF file_rows.IS_NEW_FILE --><img src="{file_rows.FILE_NEW_IMAGE}" border="0" alt="{L_NEW_FILE}"><!-- ENDIF --><br><span class="genmed">{file_rows.FILE_DESC}</span></td>
		<td class="row2" align="center" valign="middle" nowrap="nowrap"><span class="postdetails">{file_rows.UPDATED}</td>
		<td class="row2" align="center" valign="middle"><span class="postdetails">{file_rows.FILE_DLS}</td>
		<!-- IF file_rows.SHOW_RATINGS -->
		<td class="row2" align="center" valign="middle" nowrap="nowrap"><span class="postdetails">{file_rows.RATING}</td>
		<!-- ENDIF -->
		<td class="row2" align="center" valign="middle">
		<!-- IF file_rows.HAS_SCREENSHOTS -->
			<!-- IF file_rows.SS_AS_LINK -->
		<a href="{file_rows.FILE_SCREENSHOT}" class="topictitle" target="_blank"><img src="{file_rows.FILE_SCREENSHOT_URL}" border="0" alt="{L_SCREENSHOTS}"></a>
			<!-- ELSE -->
		<a href="javascript:mpFoto('{file_rows.FILE_SCREENSHOT}')" class="topictitle"><img src="{file_rows.FILE_SCREENSHOT_URL}" border="0" alt="{L_SCREENSHOTS}"></a>
			<!-- ENDIF -->
		<!-- ELSE -->
		&nbsp;
		<!-- ENDIF -->
		</td>
	  </tr>
	<!-- END file_rows -->
	</table>
	<!-- ELSE -->
		<table width="100%" cellpadding="3" cellspacing="1">
		  <tr>
				<th class="thHead" colspan="2">{L_FILES}</th>
		  </tr>
		<!-- BEGIN file_rows -->
		  <tr>
			<td rowspan="2" class="{file_rows.COLOR}" valign="middle">&nbsp;<img src="{file_rows.PIN_IMAGE}" border="0" ></td>
			<td width="100%" class="{file_rows.COLOR}">
			<a href="{file_rows.U_FILE}" class="topictitle">{file_rows.FILE_NAME}</a>&nbsp;
			<!-- IF file_rows.IS_NEW_FILE -->
			<img src="{file_rows.FILE_NEW_IMAGE}" border="0" alt="{L_NEW_FILE}">
			<!-- ENDIF -->
			<br><span class="genmed">{file_rows.FILE_DESC}</span>
			</td>
		  </tr>
		  <tr>
			<td valign="top" align="left" class="{file_rows.COLOR}">
			<span class="gensmall">
				{L_UPDATE_TIME}: {file_rows.UPDATED}&nbsp;&bull;&nbsp;{L_DOWNLOADS}: {file_rows.FILE_DLS}&nbsp;&bull;&nbsp;{L_SUBMITED_BY}&nbsp;{file_rows.POSTER}
				<!-- IF SHOW_RATINGS -->
				&bull;&nbsp;{file_rows.L_RATING}: {file_rows.RATING} ({file_rows.FILE_VOTES} {L_VOTES}) {file_rows.DO_RATE}
				<!-- ENDIF -->
				<!-- IF SHOW_COMMENTS -->
				&bull;&nbsp;{file_rows.L_COMMENT}: {file_rows.COMMENTS}
				<!-- ENDIF -->
			</span>
			</td>
		  </tr>
		<!-- END file_rows -->
		</table>
	<!-- ENDIF -->

	<form action="{S_ACTION_SORT}" method="post">
	<table width="100%" cellpadding="4" cellspacing="1">
	<input type="hidden" name="action" value="category">
	<input type="hidden" name="cat_id" value="{ID}">
	<input type="hidden" name="start" value="{START}">
	  <tr>
		<td class="cat" align="center" colspan="6"><span class="genmed">{L_SELECT_SORT_METHOD}:&nbsp;
		<select name="sort_method">
			<option {SORT_NAME} value='file_name'>{L_NAME}</option>
			<option {SORT_TIME} value='file_time'>{L_DATE}</option>
			<!-- IF SHOW_RATINGS -->
			<option {SORT_RATING} value='file_rating'>{L_RATING}</option>
			<!-- ENDIF -->
			<option {SORT_DOWNLOADS} value='file_dls'>{L_DOWNLOADS}</option>
			<option {SORT_UPDATE_TIME} value='file_update_time'>{L_UPDATE_TIME}</option>
		</select>
			&nbsp;{L_ORDER}:
			<select name="sort_order">
				<option {SORT_ASC} value="ASC">{L_ASC}</option>
				<option {SORT_DESC} value="DESC">{L_DESC}</option>
			</select>
		&nbsp;<input type="submit" name="submit" value="{L_SORT}" class="liteoption" />
		</span></td>
	  </tr>
	</table>
	</form>
</td></tr></table>
<!-- ENDIF -->


<table width="100%" cellspacing="2" border="0" cellpadding="2">
  <tr>
	<td align="left" nowrap="nowrap"><span class="nav">{PAGE_NUMBER}</span></td>
	<td align="right" nowrap="nowrap"><span class="nav">{PAGINATION}</span></td>
  </tr>
</table>

<!-- IF NO_FILE -->
<table class="forumline" width="100%" cellspacing="1" cellpadding="4">
	<tr>
		<th class="thHead">{L_NO_FILES}</th>
	</tr>
	<tr>
		<td class="row1" align="center" height="30"><span class="genmed">{L_NO_FILES_CAT}</span></td>
	</tr>
</table>
<!-- ENDIF -->
<!-- INCLUDE pa_footer.tpl -->
