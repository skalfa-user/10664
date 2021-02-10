/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * User search ajax actions controller.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.ow_plugins.usearch.static.js
 * @since 1.7.4
 */

var USearchFromQuestionModel = function () {
    var rules = [];
    var fields = {};
    var countBySections = {};
    var visibleQuestion = []
    var self = this;

    this.addField = function (name, gender, visible, section) {
        var rule = {'name': name, 'gender': gender, 'visible': visible, 'section': section};
        rules.push(rule);
        if (visible)
        {
            visibleQuestion.push(rule);
        }
        fields[name] = name;

    };

    this.getFields = function () {
        return rules;
    };

    this.getFieldNames = function () {
        return fields;
    };

    this.getVisibleFields = function () {
        return visibleQuestion;
    };

    var countFieldsBySections = function (gender) {
        countBySections = {};
        $.each(visibleQuestion, function (key, value) {
            if (!countBySections[value.section])
            {
                countBySections[value.section] = 0;
            }

            if (value.visible)
            {
                countBySections[value.section] += 1;
            }
        });
    };

    this.changeVisibility = function (gender) {
        visibleQuestion = [];
        $.each(rules, function (key, value) {
            value.visible = false;
            if (value.gender === gender)
            {
                value.visible = true;
                visibleQuestion.push(value);
            }
        });
        countFieldsBySections(gender);
        OW.trigger("usearch.search_fields_model_changed", self);
    };

    this.getCountBySection = function (name) {
        return countBySections[name] ? countBySections[name] : 0;
    };
};

var USearchSectionModel = function () {
    var sections = {};
    var self = this;

    this.addSection = function (section, visible) {
        sections[section] = visible;
    };
    this.setVisibility = function (section, visible) {
        sections[section] = Boolean(visible);
    };
    this.getSections = function () {
        return sections;
    };
    OW.bind("usearch.search_fields_model_changed", function (model) {
        $.each(sections, function (section, visible) {
            self.setVisibility(section, model.getCountBySection(section));
        });
    });
};

var USearchFromPresenter = function ($) {
    var gender, sectionPrefix = '', fieldPrefix = '';
    var fieldsModel = new USearchFromQuestionModel();
    var sectionsModel = new USearchSectionModel();
    var altClass = 'ow_alt1';
    var validators = [];


    var utils = {
        toggleClass: function () {
            altClass = (altClass == 'ow_alt1' ? 'ow_alt2' : 'ow_alt1');
            return altClass;
        },
        setVisible: function (box, isVisible)
        {
            if (isVisible) {
                box.show();
            } else {
                box.hide();
            }
        },
        disableValidators: function ()
        {
            if ( window.owForms.MainSearchForm && window.owForms.MainSearchForm.elements )
            {
                var elements = window.owForms.MainSearchForm.elements;
                
                $.each(elements, function(key, element){
                    element.validators = [];
                });
                
                $.each(fieldsModel.getVisibleFields(), function (key, field) {
                    if( elements[field.name] && validators[field.name] )
                    {
                        elements[field.name].validators = validators[field.name];
                    }
                });
            }
        }
    };

    OW.bind("usearch.lookin_for_changed", function (gender) {
        fieldsModel.changeVisibility(gender);
        OW.trigger("usearch.models_changed", gender);
    });

    OW.bind("usearch.models_changed", function (gender) {
        // show/hide fields  
        $(".questions_div").addClass("usearch_visuallyhidden");
        $(".usearch_preloader").show();
        
        $(".questions_div").one('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd', function () {
            $.each(fieldsModel.getFieldNames(), function (key, field) {
                var node = $('.' + fieldPrefix + field);
                utils.setVisible(node, false);
            });

            $.each(sectionsModel.getSections(), function (section, visible) {
                var node = $('.' + sectionPrefix + section);
                utils.setVisible(node, false);
            });
            
            altClass = $('.' + fieldPrefix + 'with_photo td:first').hasClass('ow_alt1') ? 'ow_alt2' : 'ow_alt1';

            $.each(fieldsModel.getVisibleFields(), function (key, field) {
                var node = $('.' + fieldPrefix + field.name);
                
                utils.setVisible(node, true);
                var td = node.find("td");
                td.removeClass("ow_alt1 ow_alt2");
                td.addClass(utils.toggleClass());
                
            });
            utils.disableValidators();
            // show/hide sections
            $.each(sectionsModel.getSections(), function (section, visible) {
                var node = $('.' + sectionPrefix + section);
                utils.setVisible(node, visible);
            });

            $(".questions_div").removeClass("usearch_visuallyhidden");
            $(".usearch_preloader").hide();
            
        });
    });

    return {
        init: function (fModel, sModel, params) {
            gender = params['gender'];
            fieldsModel = fModel;
            sectionsModel = sModel;
            sectionPrefix = params['sectionPrefix'];
            fieldPrefix = params['fieldPrefix'];
            
            if ( window.owForms.MainSearchForm && window.owForms.MainSearchForm.elements )
            {
                var elements = window.owForms.MainSearchForm.elements;
                
                $.each(elements, function(key, element){
                    validators[key] = element.validators;
                });
                
                utils.disableValidators();
            }
        },
    }
}(jQuery);