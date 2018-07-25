
var strict;
(function ($) {


    function removeEntry (event) {
        $(event.target).parent().remove();
    }

    $('*[data-prototype]').each(function (i,div) {
        var o =$(div);
        var bAdd = $('<div class="btn btn-default">ADD</div>');
        var bRemove = $('<div class="btn btn-danger">Remove</div>');
        bRemove.on('click',removeEntry);

        o.children('div.form-group').each(function(i,div_){
            $(div).append(bRemove);
        });

        o.append(button);
        button.on('click',function(){
            button.before(o.data('prototype'));
        });
    })

}(jQuery));
