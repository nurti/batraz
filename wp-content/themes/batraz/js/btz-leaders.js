
(function($){
    $.fn.loadLeaders = function(options){
        defaults = {
            slug : null,
            tpl: {
                    next     : '<a title="Successivo" class="leaders-nav leaders-next" href="javascript:;"><span></span></a>',
                    prev     : '<a title="Precedente" class="leaders-nav leaders-prev" href="javascript:;"><span></span></a>'
            },
            ppp : 5, 
            speed : 1,
            debug : false       
        }
        options = $.extend(defaults, options);
        
        if(!options.slug || options.slug === ''){
            console.log("slug in loadLeaders undefined or empty.");
            return;
        }
        
        var wrapper = $(this).children('div');
        if(wrapper.length != 1 ){
            console.log("wrapper length = " + wrapper.length + "(not 1) in loadLeaders");
            return;
        }
        
        var links = new Array(),
        ppp, speed
        lenLinks = 0,
        start = 0;


        ppp = (isNaN(options.ppp) || options.ppp <= 0 || options.ppp > 5) ? 5 : options.ppp;
        speed = (isNaN(options.speed) || options.speed <= 0 || options.ppp > ppp) ? 1 : options.speed;
        
        return this.each(function(){
           
            $(this).prepend(options.tpl.next); 
            $(this).prepend(options.tpl.prev);
            
            $(".leaders-next", this).on('click', function(){
               
                start += speed;
                start = (start % lenLinks);
               
                wrapper.empty();
                fillContainer();
                
            });
             
            $(".leaders-prev", this).on('click', function(){

                start -= speed;
                while(start < 0){
                    start += lenLinks;
                }
                start = (start % lenLinks);
                wrapper.empty();
                fillContainer();
                
            });
           
            var counter = 0;
            function createSetlink(data){
                 
                var block =  $('<p />'),
                link = $('<a />').attr({'href' : data['permalink'], 'title' : data['title']}).appendTo(block);
                link.html(data['thumb']);
                var term = $('<a />').attr({'href' : data['term_link'], 'title' : data['term_name']}).appendTo(block);
                
                var termName = (options.debug) ? data['term_name'] + '(' + ++counter + ')' : data['term_name'] ;
                term.text(termName);
                links.push(block);
                
            }
            
            function fillContainer(){
                
                for(var i = start; i < start + ppp ; i++){
                    $(links[i % lenLinks]).appendTo(wrapper);
                }
            }
            
            
            var spinner = $('.' +Batraz.container_spinner_loading_class);
            if(spinner.length == 0){
                spinner = $('<div/>').addClass(Batraz.container_spinner_loading_class) .spinning().appendTo('body');
            }

      
            $.ajaxSetup({
                  beforeSend: function () {
                       spinner.spinning('show');
                  },
                  complete: function () {
                      spinner.spinning('hide');
                  }
             }); 
             
             var data ={
                'action': leaders_ajax_object.action_leaders,
                'slug' : options.slug
             }
      
             $.post(
                 leaders_ajax_object.ajax_url,
                 data,
                 function(response) {
                    if(typeof(response) === 'object'){ 
                       
                        wrapper.empty();
                        $(response).each(function(){
                            createSetlink(this);
                        });
                        lenLinks = links.length;
                        fillContainer();
                    }
                 }, 'json')
                 

              });
    };
        
    
})(jQuery);

