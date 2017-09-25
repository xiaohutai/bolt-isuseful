jQuery(function ($) {
    // Hide labels for hidden fields
    $('.is-useful-wrapper .expand').hide();

    // Hide the whole and show a thanks you message
    $('.is-useful-wrapper .is-useful').on('click', function(e){
        e.preventDefault();
        var $parent = $(this).parents('.is-useful-wrapper');
        var thanks = $parent.attr('data-thanks');
        $parent.html('<span class="is-useful-success">' + thanks + '</span>');
    });

    // Determine whether to expand a form or just follow a link.
    $('.is-useful-wrapper .is-not-useful').on('click', function(e){
        var $parent = $(this).parents('.is-useful-wrapper');
        var type = $parent.attr('data-type');
        if (type == 'boltforms') {
            e.preventDefault();
            $(this)
                .attr('aria-expanded', true)
            ;
            $parent
                .find('.expand')
                .attr('aria-hidden', false)
                .slideDown()
            ;
        }
    });

    $('.is-useful-wrapper .expand .close-button').on('click', function(e){
        $(this)
            .parents('.expand')
            .attr('aria-hidden', true)
            .slideUp()
        ;
        $(this)
            .parents('.is-useful-wrapper')
            .attr('aria-expanded', false)
        ;
    })

    // There is a e.preventDefault() on the form submission, so we can't just
    // add an extra handler. The only way to handle anything after it, is to
    // use this global function and check for the correct event.
    // So this function assumes there will be only ONE `.is-useful-wrapper`.
    $(document).ajaxComplete(function(event, request, settings){
        if (settings.url == '/async/boltforms/submit?form=feedback') { // todo: make customizable for other form names
            var $parent = $('.is-useful-wrapper');
            var thanks = $parent.attr('data-thanks');
            $parent.html('<span class="is-useful-success">' + thanks + '</span>');
        }
    });
}
