(function($) {
    $(document).ready(function(){
        $(document).on('click', '.ffw-basic-wrapper .ffw-accordion-heading', function(event){
            event.preventDefault();

            // Each list works on its own, so several lists can share a page.
            var $list = $(this).closest('.ffw-basic-wrapper');
            var $item = $(this).parent(".ffw-accordion");
            var isThisActive = $item.hasClass("ffw-accordion-active");

            if(isThisActive){
                // Styled lists (Cards, Minimal, Soft) show a +/- icon, so a click closes the item.
                if ( $(this).closest('.ffw-main-wrapper[class*="is-style-ffw-"]').length ) {
                    $item.removeClass("ffw-accordion-active");
                    $(this).next(".ffw-content").slideUp(250);
                }
                return;
            }

            $list.find(".ffw-accordion-active").removeClass("ffw-accordion-active");
            $item.addClass("ffw-accordion-active");

            // slide content
            $list.find(".ffw-content").slideUp(250);
            $(this).next(".ffw-content").slideDown(250);

        });
    });
})(jQuery)
