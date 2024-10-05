(function($) {
    $(document).ready(function(){
        $(document).on('click', '.ffw-accordion-heading', function(event){
            event.preventDefault();
    
            var isThisActive = $(this).parent(".ffw-accordion").hasClass("ffw-accordion-active");
        
            if(isThisActive){
                return;
            }
    
            $(".ffw-accordion-active").removeClass("ffw-accordion-active");
            $(this).parent(".ffw-accordion").addClass("ffw-accordion-active");
            
            // slide content
            $(".ffw-content").slideUp(250);
            $(this).next(".ffw-content").slideDown(250);
        
        });
    });
})(jQuery)