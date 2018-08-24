/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
var $ = require('jquery');


require('bootstrap');

require('bootstrap-table');



/**
 * custom javascript for ui
 *
 * @copyright lenticular cloud
 */

var strict;
(function ($) {

    var bAddTemplate = '<div class="btn btn-success">ADD</div>';
    function addEntry(o) {
        var bAdd = $(bAddTemplate);
        o.append(bAdd);
        bAdd.on('click',function(){
            var template = o.data('prototype');
            template = $(template.replace('__name__label__','New Entry'));
            bAdd.before(template);
            addRemoveButton(template);
        });
    }

    var bRemoveTemplate = '<div class="btn btn-danger">Remove</div>';
    function addRemoveButton(o) {
        var bRemove = $(bRemoveTemplate);
        bRemove.on('click',removeEntry);
        o.append(bRemove);
    }
    function removeEntry (event) {
        $(event.target).parent().remove();
    }


    $('*[data-prototype]').each(function (i,div) {
        var o =$(div);

        o.children('.form-group').each(function(i, div_){
            addRemoveButton($(div_));
        });

        addEntry(o);
    });

    //forms
    $('form').each(function(i,form){
        form.ajaxForm();
    });

}(jQuery));
