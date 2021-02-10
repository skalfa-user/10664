/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
var MatchmakingCoefficient = function(params){
    this.checkedCoefficient = 0;
    this.cmpId = params.cmpId;
    this.itemsCount = params.itemsCount;
    this.id = params.id;
    
}

MatchmakingCoefficient.prototype = {
    init: function(){
        var self = this;
        
        for( var i = 1; i <= this.itemsCount; i++ ){
            $('#' + this.cmpId+'_item_'+ i).bind( 'mouseover', {i:i},
                function(e){
                    self.setCoefficient(e.data.i);
                }
            ).bind( 'mouseout',
                function(){
                    self.setCoefficient(self.checkedCoefficient);
                }
            ).bind( 'click', {i:i},
                function(e){
                    self.updateCoefficient(e.data.i);
                }
            );
        }
    },
    
    setCoefficient: function( coefficient ){
        for( var i = 1; i <= this.itemsCount; i++ ){
            var $el = $('#' + this.cmpId+'_item_'+ i);
            $el.removeClass('active');
            if( !coefficient ){
                continue;
            }
            if( i <= coefficient ){
                $el.addClass('active');
            }
        }
    },
    
    updateCoefficient: function( coefficient ){
        var self = this;
        
        self.checkedCoefficient = coefficient;        
        $('#'+self.id).val(coefficient);
        self.setCoefficient(coefficient);
    }
}

var EditCoefficient = function(params){

    this.checkedCoefficient = params.checkedCoefficient;
    this.cmpId = params.cmpId;
    this.itemsCount = params.itemsCount;
    this.id = params.id;
    this.respondUrl = params.respondUrl;

    this.updateCoefficient = function( coefficient )
    {
        var self = this;
        if( coefficient == this.checkedCoefficient ){
            return;
        }
        this.checkedCoefficientBackup = this.checkedCoefficient;
        this.checkedCoefficient = coefficient;
        $.ajax({
            type: 'POST',
            url: self.respondUrl,
            data: 'id='+encodeURIComponent(self.id)+'&coefficient='+encodeURIComponent(coefficient),
            dataType: 'json',
            success : function(data){

                if( data.errorMessage ){
                    OW.error(data.errorMessage);
                    self.checkedCoefficient = self.checkedCoefficientBackup;
                    self.setCoefficient(self.checkedCoefficientBackup);
                    return;
                }

                if( data.message ){
                    OW.info(data.message);
                }

                $('#'+self.id).val(coefficient);
                self.setCoefficient(coefficient);

            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                alert('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
    }
}

EditCoefficient.prototype = MatchmakingCoefficient.prototype;

var CreateCoefficient = function(params){

    this.checkedCoefficient = params.checkedCoefficient;
    this.cmpId = params.cmpId;
    this.itemsCount = params.itemsCount;
    this.name = params.name;
    this.respondUrl = params.respondUrl;

    this.updateCoefficient = function( coefficient ){
        var self = this;
        if( coefficient == this.checkedCoefficient ){
            return;
        }
        this.checkedCoefficientBackup = this.checkedCoefficient;
        this.checkedCoefficient = coefficient;
        $.ajax({
            type: 'POST',
            url: self.respondUrl,
            data: 'name='+encodeURIComponent(self.name)+'&coefficient='+encodeURIComponent(coefficient)+'&create=1',
            dataType: 'json',
            success : function(data){

                if( data.errorMessage ){
                    OW.error(data.errorMessage);
                    self.checkedCoefficient = self.checkedCoefficientBackup;
                    self.setCoefficient(self.checkedCoefficientBackup);
                    return;
                }

                if( data.message ){
                    OW.info(data.message);
                }

                $('#'+self.name).val(coefficient);
                self.setCoefficient(coefficient);

                location.reload(true);
            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                alert('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
    }
}

CreateCoefficient.prototype = MatchmakingCoefficient.prototype;

var MatchmakingRule = function(params){
    this.cmpId = params.cmpId;
    this.id = params.id;
    this.prefix = params.prefix;
}

MatchmakingRule.prototype = {
    init: function(){
        var self = this;

        $('#' + this.id).change(function(){
            if ($(':selected',this).val()=='')
            {
                return;
            }
            $('.match_question').val( self.prefix + ' ' + $(':selected',this).text() );
        });
    }
}

$(function(){
    $('#matchQuestionFormBtn').click(function(){
        if ($('#matchQuestionFormBtn').hasClass('ow_close_match_section')){
            $('#matchQuestionFormBtn').removeClass('ow_close_match_section');
            $('#matchQuestionForm').addClass('ow_hidden');
        }
        else{
            $('#matchQuestionForm').removeClass('ow_hidden');
            $('#matchQuestionFormBtn').addClass('ow_close_match_section');
        }
    });
})