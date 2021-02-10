/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
var SKADATE_FileUploader = function SkadateFileUpload($) {
    
    var form;
    var url;
    var node;
    
    function clearInput() {
        node.find('.input').show();
        node.find('.preload').hide();
        node.find('.percent_value').hide();
        node.find('input[type=file]').val('');
    }
    
    function hideInput() {
        node.find('.input').hide();
        node.find('.preload').show();
        node.find('.percent_value').text("0%");
        node.find('.percent_value').show();
        node.find('.image').hide();
    }
    
    function deleteFile(event)
    {
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {"command":"delete"},
            cache: false,
            dataType: 'json',
            cache:false,
            
            success: function(data)
            {
                clearInput();
                node.find('img').attr('src', '');
                node.find('.image').hide();
                node.find('.input').show();

                if ( data.message )
                {
                    OW.info(data.message);
                }
            },
            
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Handle errors here
                console.log('ERRORS: ' + textStatus);
            }
        });
    }
    
    // upload the files
    function uploadFile(event)
    {
        event.stopPropagation();
        event.preventDefault();
        
        if (window.File && window.FileReader && window.FileList && window.Blob) {
          // Great success! All the File APIs are supported.
        } else {
          alert('The File APIs are not fully supported in this browser.');
          return;
        }

        var files = event.target.files;
        
        // Create a formdata object and add the files
        var data = new FormData();
        $.each(files, function(key, value)
        {
            data.append(key, value);
        });
        
        data.append("command", "upload");

        function progress(e){
            if(e.lengthComputable){
                var max = e.total;
                var current = e.loaded;

                var Percentage = (current * 100)/max;

                if(Percentage >= 100)
                {
                   $('.percent_value').text("100%");
                }
                else
                {
                    $('.percent_value').text(parseInt(Percentage, 10)+"%");
                }
            }  
        }
         
        hideInput();
        
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if(myXhr.upload){
                    myXhr.upload.addEventListener('progress', progress, false);
                }
                return myXhr;
            },
            cache:false,
            
            success: function(data)
            {
                clearInput();
                if(data && typeof data.error === 'undefined')
                {
                    // Success so call function to process the form
                    if ( data.url )
                    {
                        node.find('img').attr('src', data.url);
                        
                        node.find('.image').show();
                        //node.find('.input').hide();
                        
                    }
                    
                    if ( data.message )
                    {
                        OW.info(data.message);
                    }
                }
                else
                {
                    if ( data && data.error )
                    {
                        OW.error(data.error);
                    }
                }
            },
            
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Handle errors here
                console.log('ERRORS: ' + textStatus);
            }
        });
    }
    
    return {
        init : function($node, responderUrl) {
            
            if ($node)
            {
                $node.find('input[type=file]').on('change', uploadFile);
                $node.find('.promo_image_delete_image_icon_div').on('click', deleteFile);
            }
            node = $node;
            url = responderUrl;
        }
    };
}(jQuery);

