/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
var MatchmakingCheckboxGroup = function( id, name ){
    var formElement = new OwFormElement(id, name);

    formElement.getValue = function(){
        var $inputs = $("input[name='"+ this.name +"[]']:checked", $(this.input.form));
        var values = [];

        $.each( $inputs, function(index, data){
            if( this.checked == true ){
                values.push($(this).val());
            }
        }
        );

        return values;
    };

    formElement.resetValue = function(){
        var $inputs = $("input[name='"+ this.name +"[]']:checked", $(this.input.form));

        $.each( $inputs, function(index, data){
            $(this).removeAttr('checked');
        }
        );
    };

    formElement.setValue = function(value){
        for( var i = 0; i < value.length; i++ ){
            $("input[name='"+ this.name +"[]'][value='"+value[i]+"']", $(this.input.form)).attr('checked', 'checked');
        }
    };

    $("input[name='"+ formElement.name +"[]']").click(function(){

        var values = formElement.getValue();

        if (values.length != 0)
        {
            $("#"+ formElement.name +"_unimportant").removeAttr('checked');
        }
        else
        {
            $("#"+ formElement.name +"_unimportant").attr('checked', 'checked');
        }
    });

    $("#"+ formElement.name +"_unimportant").click(function(){
        var $inputs = $("input[name='"+ formElement.name +"[]']:checked");

        $.each( $inputs, function(index, data){
                if( this.checked == true ){
                    $(this).removeAttr('checked');
                }
            }
        );
    });

    return formElement;
}
