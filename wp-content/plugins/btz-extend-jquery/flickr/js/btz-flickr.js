(function($) {
    $.fn.bindSetFlickr = function(options) {
        var defaultDim =  {type : 'thumbnail', l : 100 , h : 75, list : true , single : false} ;
        var defaultDimZoom = {type : 'original', l : 2400 , h : 1800, list : false , single : true};  
        var dims = new Array({type : 'square', l : 75 , h : 75, list : true , single : false},
                              defaultDim,  
                              {type : 'small', l : 240 , h : 180, list : true , single : false},
                              {type : 'medium', l : 500 , h : 375, list : true , single : false},
                              {type : 'medium_640', l : 640 , h : 480, list : true , single : true},
                              {type : 'medium_800', l : 800 , h : 600, list : false , single : true},
                              {type : 'large', l : 1024 , h : 768, list : false , single : true},
                              defaultDimZoom
                              );
        
       
        defaults = {
            imageClass: 'imageContainer',
            photosContainer: null,
            galleryRel: 'FlickrGallery',
            dim: 'thumbnail',
            dimZoom: 'original',
            wArrow:32,
            photos :{
                tpl: {
                    next: '<a title="Successivo" class="flickr-photos-nav flickr-photos-next" href="javascript:;"><span></span></a>',
                    prev: '<a title="Precedente" class="flickr-photos-nav flickr-photos-prev" href="javascript:;"><span></span></a>'
                },
                cols : 5,
                rows : 3,
                speed : 1
            }
            
        }
        

        options = $.extend(true, defaults, options);
       
        if (!options.photosContainer || options.photosContainer.length == 0) {
            console.log("photosContainer in bindSetFlickr undefined or empty.");
            return;
        }
        
        

        var wrapper = $(options.photosContainer).children('div');
        
        if (wrapper.length != 1) {
            console.log("wrapper length = " + wrapper.length + "(not 1) in bindSetFlickr");
            return;
        }
        
        
        
        
        var currentDim = defaultDim;
        $.each(dims, function(){
            if(this.type === options.dim && this.list){
                currentDim = this;
                return false;
            }
        });
        
        var currentDimZoom = defaultDimZoom;
        $.each(dims, function(){
            if(this.type === options.dimZoom && this.single){
                currentDimZoom = this;
                return false;
            }
        });
        
       

        var next = options.photosContainer.prepend(options.photos.tpl.next);
        var prev = options.photosContainer.prepend(options.photos.tpl.prev);



        var links = new Array(),
        cols, rows, speed, matrix,
        lenLinks = 0,
        start = 0,
        counter = 0;

        cols = (isNaN(options.photos.cols) || options.photos.cols <= 0 || options.photos.cols > 5) ? 3 : options.photos.cols;
        rows = (isNaN(options.photos.rows) || options.photos.rows <= 0 || options.photos.cols > 5) ? 3 : options.photos.rows;
        speed = (isNaN(options.photos.speed) || options.photos.speed <= 0 || options.photos.cols > cols) ? 1 : options.photos.speed;
        
        var width = $(wrapper).width() - options.wArrow,
        maxCols = Math.floor(width / ( currentDim.l + 20) );
        if(maxCols < cols)cols = maxCols;
        
        matrix = cols * rows;
        
 
        return this.each(function(){
                $(".flickr-photos-next", options.photosContainer[0]).on('click', function() {
                    start += speed;
                    start = (start % lenLinks);
                    fillContainer();

                });

                $(".flickr-photos-prev", options.photosContainer[0]).on('click', function() {
                    start -= speed;
                    while (start < 0) {
                        start += lenLinks;
                    }
                    start = (start % lenLinks);
                    fillContainer();

                });
            
               
                function createSetImages(data) {
                    var block = $('<p />').addClass(options.imageClass).css({width : currentDim });
                    a = $('<a />').attr({'title': data['title'], 'href': data['href'], 'rel': options.galleryRel}).appendTo(block);
                    $('<img />').attr({'src': data['src']}).appendTo(a);
                    
                    var title = (options.debug) ? data.title + '(' + ++counter + ')' : data.title;
                    $('<span />').text(title).appendTo(block);
                    
                    links.push(block);

                }

                function fillContainer() {
                    
                    wrapper.empty();
                    
                    for (var i = start; i < start + matrix; i++) {
                        $(links[i % lenLinks][0]).css({'margin-right' : '20px', 'margin-bottom' : '20px'});
                        $(links[i % lenLinks]).appendTo(wrapper);
                    }
                }


                var $this = $(this),
                        currentId = $this.find('a').attr('id'),
                        value = $("input[type=hidden]", this).val(),
                        photos = !isNaN(value) ? value : 10;



                var spinner = $('.' + Batraz.container_spinner_loading_class);
                if (spinner.length == 0) {
                    spinner = $('<div/>').addClass(Batraz.container_spinner_loading_class).spinning().appendTo('body');
                }



                $.ajaxSetup({
                    beforeSend: function() {
                        spinner.spinning('show');
                        prev.hide(); next.hide();
                    },
                    complete: function() {
                        spinner.spinning('hide');
                        prev.show(); next.show();
                       
                    }
                });

                var data = {
                    'action': flickr_ajax_object.action_photoset,
                    'id_set': currentId,
                    'photos': photos,
                    'dim': currentDim.type,
                    'dimZoom': currentDimZoom.type
                }

                $.post(
                        flickr_ajax_object.ajax_url,
                        data,
                        function(response) {
                            if (typeof(response) === 'object') {
                                counter = 0; start = 0;
                                $(response).each(function() {
                                    createSetImages(this);
                                });
                                
                                lenLinks = links.length;
                                fillContainer();

                                $('a[rel=' + options.galleryRel + ']').fancybox({'autoPlay': true});
                                
                            }
                        }, 'json')

            
        });
        
    }


})(jQuery);


(function($) {
    $.fn.allSetsFlickr = function(options) {
        defaults = {
            'setsClass': 'Sets',
            'caseClass': 'SetCase',
            'imageClass': 'imageContainer',
            'setsContainer': null,
            'photosContainer': null,
            sets:{
                tpl: {
                    next: '<a title="Successivo" class="flickr-sets-nav flickr-sets-next" href="javascript:;"><span></span></a>',
                    prev: '<a title="Precedente" class="flickr-sets-nav flickr-sets-prev" href="javascript:;"><span></span></a>'
                },
                cols : 5,
                speed : 1
            },
            debug: 1
        }
        options = $.extend(defaults, options);

        var wrapper = $(this).children('div');
        if (wrapper.length != 1) {
            console.log('Length wrapper ( not 1 ) = ' + wrapperSets.length + ' in allSetsFlickr');
            return;
        }

        if (!options.photosContainer || options.photosContainer.length == 0) {
            console.log("photosContainer in allSetsFlickr undefined or empty.");
            return;
        }

        var next = $(this).prepend(options.sets.tpl.next);
        var prev = $(this).prepend(options.sets.tpl.prev);


        var links = new Array(),
                cols, speed
        lenLinks = 0,
                start = 0;

        cols = (isNaN(options.sets.cols) || options.sets.cols <= 0 || options.sets.cols > 5) ? 3 : options.sets.cols;
        speed = (isNaN(options.sets.speed) || options.sets.speed <= 0 || options.sets.cols > cols) ? 1 : options.sets.speed;

        return this.each(function() {

            $(".flickr-sets-next", this).on('click', function() {
                start += speed;
                start = (start % lenLinks);
                fillContainer();

            });

            $(".flickr-sets-prev", this).on('click', function() {
                start -= speed;
                while (start < 0) {
                    start += lenLinks;
                }
                start = (start % lenLinks);
                fillContainer();

            });

            var counter = 0;
            function createSetlink(data) {

                var block = $('<div/>').addClass(options.setsClass),
                        setCase = $('<div/>').addClass(options.caseClass).appendTo(block),
                        link = $('<a />').attr({'href': '#', 'id': data.id}).appendTo(setCase);
                $('<img />').attr('src', data.url_thumbnail).appendTo(link);
                $('<input />').attr({'type': 'hidden', 'value': data.photos}).appendTo(link);

                var title = (options.debug) ? data.title + '(' + ++counter + ')' : data.title;

                $('<span />').text(title).appendTo(block);
                links.push(block);

            }

            function fillContainer() {

                wrapper.empty();
                for (var i = start; i < start + cols; i++) {
                    $(links[i % lenLinks]).appendTo(wrapper);
                }
                addClickHandler();
            }
            
            function addClickHandler(){
               
                $('.' + options.setsClass).on('click', function(e){
                     e.preventDefault();
                     $(this).bindSetFlickr(options); 
                });

            }



            var spinner = $('.' + Batraz.container_spinner_loading_class);
            if (spinner.length == 0) {
                spinner = $('<div/>').addClass(Batraz.container_spinner_loading_class).spinning().appendTo('body');
            }


            $.ajaxSetup({
                beforeSend: function() {
                    spinner.spinning('show');
                    next.hide(); prev.hide();
                },
                complete: function() {
                    spinner.spinning('hide');
                    if (lenLinks > 0) {
                        next.show();
                        prev.show();
                    }
                  
                }
            });

            var data = {
                'action': flickr_ajax_object.action_photosets,
                'all_sets': ''
            }

            $.post(
                    flickr_ajax_object.ajax_url,
                    data,
                    function(response) {
                        if (typeof(response) === 'object') {
                            $(response).each(function() {
                                createSetlink(this);
                            });
                            lenLinks = links.length;
                            fillContainer();

                        }
                    }, 'json')


        });
    };


})(jQuery);

(function($) {

    $.fn.flickrLoad = function(options) {

        return this.each(function() {
            var sets = $(this).children('.flickr-sets');
            if (sets.length != 1) {
                console.log('Length sets ( not 1 ) = ' + sets.length);
                return;
            }


            var photos = $(this).children('.flickr-photos');
            if (photos.length != 1) {
                console.log('Length photos ( not 1 ) = ' + photos.length);
                return;
            }


            options.setsContainer = sets;
            options.photosContainer = photos;
            sets.allSetsFlickr(options);


        });
    }

})(jQuery);