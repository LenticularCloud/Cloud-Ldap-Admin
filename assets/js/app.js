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

require('jquery-form');



/**
 * custom javascript for ui
 *
 * @copyright lenticular cloud
 */

var strict;
(function ($) {

    //tab remember
    $(document).on('click.bs.tab.data-api', '[data-toggle="tab"], [data-toggle="pill"]', function (e) {
        location.hash = e.target.hash;
    });

    if (location.hash.length) {
        $('[data-toggle="tab"], [data-toggle="pill"]').filter('[href="' + location.hash + '"]').tab('show');
    }


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

    var messages = $('#messages');
    var template = '<div class="card"><div class="card-body alert alert-danger">' +
        '<h5 class="card-title">Error<button type="button" class="close" aria-label="Close">' +
        '  <span aria-hidden="true">&times;</span>' +
        '</button></h5>' +
        '<div class=" message"></div>' +
        '</div></div>';

    function showMessage(data) {
        var block = $(template);
        block.find('button').on('click',function () {
            block.remove();
        });

        if (data.successfully) {
            //block.addClass('success');
        } else {
            //block.addClass('error');
            if(data.errors) {
                data.msg = data.errors.join('<br />');
            }
        }
        block.find('.message').html(data.msg);
        messages.append(block);
    }

    //forms
    $('form:not(#login-form)').each(function(i,form){
        $(form).ajaxForm({
            dataType: 'json',
            success: function (data) {
                if(data.successfully) {
                    window.location.reload();
                }else {
                    showMessage(data);
                }
            },
            error: function (error) {
                showMessage(error);
            }
        });
    });

}(jQuery));
