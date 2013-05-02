jQuery(document).ajaxError(function(d){
    console.log("error from ajax with data "+d.toSource());
});

var Batraz = Batraz || {};
Batraz.container_spinner_loading_class = '_btz-ajax-loading';

(function($){
    
     var methods ={
       
        init : function(options){
            var defaults = {
                width : '300',
                height : '30'        
            };
            options = $.extend(defaults, options);
            
            var w = (!isNaN(options.width)) ? options.width : 200,
            h = (!isNaN(options.height)) ? options.height : 30,
            ml = Math.ceil(-w/2),
            mt = Math.ceil(-h/2);
          
            this.css({'position' : 'fixed', 'top': '50%', 'left' : '50%' ,
                      'width' : w , 'height' : h,
                      'margin-left' : ml, 'margin-top' : mt,
                      'z-index' : '1234', 'display' : 'none' });
            
            
            // return this.progressbar({value : false});
            return this.each(function(){
                var cntr = $('<div />').css({'padding' : '30px'}).addClass('ui-widget-content') .appendTo(this);
                var wrapper = $('<div />').appendTo(cntr);
                wrapper.progressbar({value : false});
                
            });

        }, 
        show : function(){
            this.each(function(){
                $(this).css({'left' : '50%', 'z-index' : '1234'});   
                $(this).fadeIn(200);
                
            });
           
          
            
        },
        hide : function(){
            $(this).fadeOut(200);
            $(this).css({'left' : '-9999', 'z-index' : '-1234'});   
        }
     }
       
     $.fn.spinning =  function(method){
         if(methods[method]){
             return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));

         }
         else if(typeof(method) === 'object' || !method){
             return methods.init.apply(this, arguments);
         }
         else{
             $.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
         }
   }
   
})(jQuery);