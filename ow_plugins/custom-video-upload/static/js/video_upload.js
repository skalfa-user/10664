function VideoUpload(options)
{
    var userId = options.userId || '';
    var allowedMimeTypes = options.allowedMimeTypes || [];
    var maxFileSize = options.maxFileSize || 0;
    var progressWrapperId = options.progressWrapperId || '';
    var progressValWrapperId = options.progressValWrapperId || '';
    var progressValLabelWrapperId = options.progressValLabelWrapperId || '';
    var progressLabelWrapperId = options.progressLabelWrapperId || '';
    var cancelButtonId = options.cancelButtonId || '';
    var okButtonId = options.okButtonId || '';
    var fileUploadId = options.fileUploadId || '';
    var dropZone = options.dropZone || '';
    var url = options.url || '';
    var translations = options.translations || {};
    var uploadingHandler = '';

    /**
     * Init
     *
     * @returns void
     */
    this.init = function() {
        $('#' + fileUploadId).fileupload({
            sequentialUploads: true,
            url: (!userId ? url : url + '?userId=' + userId),
            autoUpload: false,
            dropZone: $('#' + dropZone),
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);

                $('#' + progressValWrapperId).css('width', progress + '%');
                $('#' + progressValLabelWrapperId).text(progress + '%');
            },
            drop: function(e, data) {
                if (data.files.length > 1) {
                    OW.error(translations.upload_wrong_files_count);

                    e.preventDefault();
                }
            },
            paste: function(e, data) {
                if (data.files.length > 1) {
                    OW.error(translations.upload_wrong_files_count);

                    e.preventDefault();
                }
            },
            add: function(e, data) {
                // validate the file mime type
                var isMimeTypeValid = true;

                console.log(data.originalFiles[0]['type']);
                allowedMimeTypes.forEach(function(mimeType) {
                    var regex = new RegExp(mimeType, 'i');

                    if (regex.test(data.originalFiles[0]['type'])) {
                        isMimeTypeValid = false;
                    }
                });

                if (isMimeTypeValid) {
                    OW.error(translations.upload_wrong_mime_type);

                    return;
                }

                // validate file size
                if (data.originalFiles[0]['size'] > maxFileSize) {
                    OW.error(translations.upload_wrong_file_size);

                    return;
                }

                // hide ok button
                $('#' + okButtonId).hide();

                // show progress label
                $('#' + progressLabelWrapperId).html(translations.now_uploading).show();

                // show progress bar
                $('#' + progressWrapperId).show();

                // show cancel button
                $('#' + cancelButtonId).show();

                // submit data
                uploadingHandler = data.submit();
            },
            fail: function(e, data) {
                if (data.textStatus != 'abort') {
                    $('#' + progressLabelWrapperId).html(translations.upload_failed);

                    return;
                }

                // hide progress label
                $('#' + progressLabelWrapperId).hide();

                // hide progress bar
                $('#' + progressWrapperId).hide();
            },
            always: function() { // callback for completed (success, abort or error)
                // hide cancel button
                $('#' + cancelButtonId).hide();
            },
            done: function(e, data) {
                var result = JSON.parse(data.result);

                if (result.status == 'success') {
                    $('#' + progressLabelWrapperId).html(translations.uploaded_successfully);

                    // show ok button
                    $('#' + okButtonId).show();

                    OW.trigger('videoUploadSuccess', result.data);

                    return;
                }

                // show error message
                $('#' + progressLabelWrapperId).html(translations.upload_failed);
                OW.error(result.message);
            }
        });
    }
}
