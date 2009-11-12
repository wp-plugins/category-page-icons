
function wpdev_debug_function (message) {
        var exceptionMessage, exceptionValues = [];

        // Check for an exception object and print it nicely
        if (typeof message === "object" && typeof message.name === "string" && typeof message.message === "string") {
            for (var key in message) {
                if (message.hasOwnProperty(key)) {
                    exceptionValues.push(key + ": " + message[key]);
                }
            }
            exceptionMessage = exceptionValues.join("\n") || "";
          exceptionValues = exceptionMessage.split("\n");
            exceptionMessage = "EXCEPTION: " + exceptionValues.join("\nEXCEPTION: ");
            //jQuery('#media-upload-error').show().text(exceptionMessage);
            message = exceptionMessage;
        } else {
            //jQuery('#media-upload-error').show().text(message);
        }

    message = '<hr/>'+ message;
    jQuery('#media-upload-error').show().html(  jQuery('#media-upload-error').show().html() + message);
}


function wpdev_fileDialogStart() {
	jQuery("#media-upload-error").empty();
}

// progress and success handlers for media multi uploads
function wpdev_fileQueued(fileObj) {
	// Get rid of unused form
	jQuery('.media-blank').remove();
	// Collapse a single item
	if ( jQuery('.type-form #media-items>*').length == 1 && jQuery('#media-items .hidden').length > 0 ) {
		jQuery('.describe-toggle-on').show();
		jQuery('.describe-toggle-off').hide();
		jQuery('.slidetoggle').slideUp(200).siblings().removeClass('hidden');
	}
	// Create a progress bar containing the filename
	jQuery('#media-items').append('\
        <div id="media-item-' + fileObj.id + '" class="media-item child-of-' + post_id + '"  >\n\
            <div class="progress">\n\
                <div class="bar"></div>\n\
            </div>\n\
            <div class="filename original">\n\
                <span class="percent"></span> ' + fileObj.name + '\
            </div>\n\
        </div>');
    
	// Display the progress div
	jQuery('#media-item-' + fileObj.id + ' .progress').show();
 

	// Disable submit and enable cancel
	jQuery('#insert-gallery').attr('disabled', 'disabled');
	jQuery('#cancel-upload').attr('disabled', '');
}

function wpdev_uploadStart(fileObj) {
	return true;
}

function wpdev_uploadProgress(fileObj, bytesDone, bytesTotal) {
	// Lengthen the progress bar
	var w = jQuery('#media-items').width() - 2;
	jQuery('#media-item-' + fileObj.id + ' .bar').width( w * bytesDone / bytesTotal );
	jQuery('#media-item-' + fileObj.id + ' .percent').html( Math.ceil(bytesDone / bytesTotal * 100) + '%' );

	if ( bytesDone == bytesTotal )
		jQuery('#media-item-' + fileObj.id + ' .bar').html('<strong class="crunching">' + swfuploadL10n.crunching + '</strong>');
}

function wpdev_prepareMediaItem(fileObj, serverData) {
	// Move the progress bar to 100%
	jQuery('#media-item-' + fileObj.id + ' .bar').remove();
	jQuery('#media-item-' + fileObj.id + ' .progress').hide();

	var f = ( typeof shortform == 'undefined' ) ? 1 : 2;
	// Old style: Append the HTML returned by the server -- thumbnail and form inputs
	if ( isNaN(serverData) || !serverData ) {
		jQuery('#media-item-' + fileObj.id).append(serverData);
		wpdev_prepareMediaItemInit(fileObj);
	}
	// New style: server data is just the attachment ID, fetch the thumbnail and form html from the server
	else {
		jQuery('#media-item-' + fileObj.id).load('async-upload.php', {attachment_id:serverData, fetch:f}, function(){wpdev_prepareMediaItemInit(fileObj);wpdev_updateMediaForm()});
	}
}

function wpdev_prepareMediaItemInit(fileObj) {

	// Clone the thumbnail as a "pinkynail" -- a tiny image to the left of the filename
	jQuery('#media-item-' + fileObj.id + ' .thumbnail').clone().attr('className', 'pinkynail toggle').prependTo('#media-item-' + fileObj.id);

	// Replace the original filename with the new (unique) one assigned during upload
	jQuery('#media-item-' + fileObj.id + ' .filename.original').replaceWith(jQuery('#media-item-' + fileObj.id + ' .filename.new'));

	// Also bind toggle to the links
	jQuery('#media-item-' + fileObj.id + ' a.toggle').click(function(){
		jQuery(this).siblings('.slidetoggle').slideToggle(150, function(){
			var o = jQuery(this).offset();
			window.scrollTo(0, o.top-36);
		});
		jQuery(this).parent().children('.toggle').toggle();
		jQuery(this).siblings('a.toggle').focus();
		return false;
	});

	// Bind AJAX to the new Delete button
	jQuery('#media-item-' + fileObj.id + ' a.delete').click(function(){
		// Tell the server to delete it. TODO: handle exceptions
		var answer = true;//confirm("Do you really wnat to delete this "+fileObj.name+" file?");
        	if (answer){
                   jQuery.ajax({
                        url: wpdev_flash_uploader_path, //'../wp-content/plugins/menu-compouser/include/wpdev-flash-uploader.php',
                        type:'post',
                        success:wpdev_deleteSuccess,
                        error:wpdev_deleteError,
                        id:fileObj.id, // this is needs for working with this.id inside this script
                        data:{
                            ajax_action : 'delete-image',
                            file_name : fileObj.name,
                            file_name_org : jQuery('#media-item-' + fileObj.id +' input.filename').val(),
                            file_name_dir : jQuery('#media-item-' + fileObj.id +' input.filedir').val(),
                            fileicon_size : jQuery('#media-item-' + fileObj.id +' input.fileicon_size').val(),
                            _ajax_nonce : this.href.replace(/^.*wpnonce=/,'')}
                            });
                }
		return false;
	});

	// Open this item if it says to start open (e.g. to display an error)
	jQuery('#media-item-' + fileObj.id + '.startopen').removeClass('startopen').slideToggle(500).parent().children('.toggle').toggle();
}

function wpdev_itemAjaxError(id, html) {
	var error = jQuery('#media-item-' + id + ' div.media-item-error');

	error.html('<div class="wpdev-file-error" style="color:#FF0000;padding:5px 10px;text-align:right;border-top:1px solid #eee;font-weight:bold;"><button type="button" id="dismiss-'+id+'" class="button dismiss" >'+swfuploadL10n.dismiss+'</button>'+html+'</div>');
	jQuery('#dismiss-'+id).click(function(){jQuery(this).parents('#media-item-' + id).slideUp(200, function(){jQuery(this).empty();})});
}

function wpdev_deleteSuccess(data, textStatus) {
	if ( data == '-1' )
		return wpdev_itemAjaxError(this.id, 'You do not have permission. Has your session expired?');
	if ( data == '0' )
		return wpdev_itemAjaxError(this.id, 'Could not be deleted. Has it been deleted already?');

	var item = jQuery('#media-item-' + this.id);
//item.html(data);
        /*
	// Decrement the counters.
	if ( type = jQuery('#type-of-' + this.id).val() )
		jQuery('#' + type + '-counter').text(jQuery('#' + type + '-counter').text()-1);
	if ( item.hasClass('child-of-'+post_id) )
		jQuery('#attachments-count').text(jQuery('#attachments-count').text()-1);

	if ( jQuery('.type-form #media-items>*').length == 1 && jQuery('#media-items .hidden').length > 0 ) {
		jQuery('.toggle').toggle();
		jQuery('.slidetoggle').slideUp(200).siblings().removeClass('hidden');
	}
        /**/
	// Vanish it.
	//jQuery('#media-item-' + this.id + ' .filename:empty').remove();
	//jQuery('#media-item-' + this.id + ' .filename').append(' <span class="file-error">'+swfuploadL10n.deleted+'</span>').siblings('a.toggle').remove();
	jQuery('#media-item-' + this.id).children('.describe').css({backgroundColor:'#fff'}).end()
			.animate({backgroundColor:'#ffc0c0'}, {queue:false,duration:50})
			.animate({minHeight:0,height:36}, 400, null, function(){jQuery(this).children('.describe').remove()})
			.animate({backgroundColor:'#fff'}, 400)
			.animate({height:0}, 800, null, function(){jQuery(this).remove();wpdev_updateMediaForm();});

	return 0;
}

function wpdev_deleteError(X, textStatus, errorThrown) {
	// TODO
}

function wpdev_updateMediaForm() {
	storeState();
	// Just one file, no need for collapsible part
	if ( jQuery('.type-form #media-items>*').length == 1 ) {
		jQuery('#media-items .slidetoggle').slideDown(500).parent().eq(0).children('.toggle').toggle();
		jQuery('.type-form .slidetoggle').siblings().addClass('hidden');
	}

	// Only show Save buttons when there is at least one file.
	if ( jQuery('#media-items>*').not('.media-blank').length > 0 )
		jQuery('.savebutton').show();
	else
		jQuery('.savebutton').hide();

	// Only show Gallery button when there are at least two files.
	if ( jQuery('#media-items>*').length > 1 )
		jQuery('.insert-gallery').show();
	else
		jQuery('.insert-gallery').hide();
}

function wpdev_uploadSuccess(fileObj, serverData) {
	// if async-upload returned an error message, place it in the media item div and return
	if ( serverData.match('media-upload-error') ) {
		jQuery('#media-item-' + fileObj.id).html(serverData);
		return;
	}

	wpdev_prepareMediaItem(fileObj, serverData);
	wpdev_updateMediaForm();

	// Increment the counter.
	if ( jQuery('#media-item-' + fileObj.id).hasClass('child-of-' + post_id) )
		jQuery('#attachments-count').text(1 * jQuery('#attachments-count').text() + 1);
}

function wpdev_uploadComplete(fileObj) {   //jQuery('#media-item-' + fileObj.id).fadeOut(2500); //alert(fileObj.name)
	// If no more uploads queued, enable the submit button
	if ( swfu.getStats().files_queued == 0 ) {
		jQuery('#cancel-upload').attr('disabled', 'disabled');
		jQuery('#insert-gallery').attr('disabled', '');
	}
}


// wp-specific error handlers

// generic message
function wpdev_wpQueueError(message) {
//	jQuery('#media-upload-error').show().text(message);
    message = '<hr/>'+ message;
    jQuery('#media-upload-error').show().html(  jQuery('#media-upload-error').show().html() + message);

}

// file-specific message
function wpdev_wpFileError(fileObj, message) {
	jQuery('#media-item-' + fileObj.id + ' .filename').after('<div class="file-error"><button type="button" id="dismiss-' + fileObj.id + '" class="button dismiss">'+swfuploadL10n.dismiss+'</button>'+message+'</div>').siblings('.toggle').remove();
	jQuery('#dismiss-' + fileObj.id).click(function(){jQuery(this).parents('.media-item').slideUp(200, function(){jQuery(this).remove();})});
}

function wpdev_fileQueueError(fileObj, error_code, message)  {
	// Handle this error separately because we don't want to create a FileProgress element for it.
	if ( error_code == SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED ) {
		wpdev_wpQueueError(swfuploadL10n.queue_limit_exceeded);
	}
	else if ( error_code == SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT ) {
		wpdev_fileQueued(fileObj);
		wpdev_wpFileError(fileObj, swfuploadL10n.file_exceeds_size_limit);
	}
	else if ( error_code == SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE ) {
		wpdev_fileQueued(fileObj);
		wpdev_wpFileError(fileObj, swfuploadL10n.zero_byte_file);
	}
	else if ( error_code == SWFUpload.QUEUE_ERROR.INVALID_FILETYPE ) {
		wpdev_fileQueued(fileObj);
		wpdev_wpFileError(fileObj, swfuploadL10n.invalid_filetype);
	}
	else {
		wpdev_wpQueueError(swfuploadL10n.default_error);
	}
}

function wpdev_fileDialogComplete(num_files_queued) { 
	try {
		if (num_files_queued > 0) {
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function wpdev_switchUploader(s) {
	var f = document.getElementById(swfu.customSettings.swfupload_element_id), h = document.getElementById(swfu.customSettings.degraded_element_id);
	if ( s ) {
		f.style.display = 'block';
		h.style.display = 'none';
	} else {
		f.style.display = 'none';
		h.style.display = 'block';
	}
}

function wpdev_swfuploadPreLoad() {
	if ( !uploaderMode ) {
		wpdev_switchUploader(1);
	} else {
		wpdev_switchUploader(0);
	}
}

function wpdev_swfuploadLoadFailed() {
	wpdev_switchUploader(0);
	jQuery('.upload-html-bypass').hide();
}

function wpdev_uploadError(fileObj, errorCode, message) {

	switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
			wpdev_wpFileError(fileObj, swfuploadL10n.missing_upload_url);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			wpdev_wpFileError(fileObj, swfuploadL10n.upload_limit_exceeded);
			break;
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			wpdev_wpQueueError(swfuploadL10n.http_error);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			wpdev_wpQueueError(swfuploadL10n.upload_failed);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			wpdev_wpQueueError(swfuploadL10n.io_error);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			wpdev_wpQueueError(swfuploadL10n.security_error);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			jQuery('#media-item-' + fileObj.id).remove();
			break;
		default:
			wpdev_wpFileError(fileObj, swfuploadL10n.default_error);
	}
}

function wpdev_cancelUpload() {
	swfu.cancelQueue();
}
