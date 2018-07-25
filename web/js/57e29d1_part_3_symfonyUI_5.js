
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

        o.children('div.form-group').each(function(i, div_){
            addRemoveButton($(div_));
        });

        addEntry(o);
    })

}(jQuery));
