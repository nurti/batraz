(function($){
    $.fn.postChoice = function(options){
        defaults = {
            target : null,
            currentPost : null        
            
        }
        
        var popupID = 'btz-choice-post-popup',
        pageTotal = 0;
        
        options = $.extend(defaults, options);
        if(!options.target || options.target.length == 0){
            console.log("input target in postChoice  undefined or empty.");
            return;
        }
        
        if(!options.currentPost || !$.isNumeric(options.currentPost)){
            console.log("currentPOst undefined in postChoice.");
            return;
        }
        
        
        
        choiceView.init(options.target, options.currentPost);
       
        
        return this.live('click', function(event){
            event.preventDefault();
            choiceView.open();
        });
    }
    
    // helper choice
    var selfView;
    var choiceView = {
        init : function(iTarget, currentPost){
            selfView = this;
            this.iTarget = iTarget;
            this.currentPost = currentPost;
            this.pageNum = 1;
            this.makePopup();
            this.popup.dialog({
                    modal: true,
                    autoOpen : false,
                    title : 'Scelta Post',
                    height: 500,
                    width: 500,
                    buttons :{
                        'Conferma scelta' : function(){
                            if(selfView.iTargetUpdate()){
                                $( this ).dialog( "close" );
                            }
                        }
                    }

             });
             
            this.spinner = $('.' +Batraz.container_spinner_loading_class);
            if(this.spinner.length == 0){
                this.spinner = $('<div/>').addClass(Batraz.container_spinner_loading_class) .spinning().appendTo('body');
            }
            $('#btz-recent-results-choice li').live('click', function(e){
               selfView.select( $(this), e );
               
            });
             $('#btz-pagination-choice li').live('click', function(e){
                selfView.loadPage( $(this), e );
            });
            
        },
        iTargetUpdate : function(){
            var newValue = this.currentID.text();
          
            if(!$.isNumeric(newValue))
                return false;
            if(typeof(this.iTarget) === 'undefined'  ){
                console.log('iTarget undefined on iTargetUpdate choiceView')
                return false;
            }
            
            if(newValue == this.currentPost){
                this.message.html('PostID ' + newValue + ' è il post corrente.');
                return false;
            }
            
            var oldValues = this.iTarget.val().trim();
            var ids = new Array();
            if(oldValues){
                ids = oldValues.split(',');
            }
            for(var i=0; i<ids.length; i++) { ids[i] = +ids[i]; } 
            for(var i=0; i<ids.length; i++) {
                if(newValue == ids[i]){
                    this.message.html('PostID ' + newValue + ' già aggiunto.');
                    return false;
                }
                
            } 
            ids.push(newValue);
            
            this.iTarget.val(ids.join());
            
            
            
            
            
//            var id = this.currentID.html();
//            var content = '[' + this.shortcode + ' id=' + id;
//            
//            if(typeof(this.propsc) == 'object'){
//                $.each(this.propsc, function(key, value){
//                    
//                    if(value.value)
//                        content += ' ' + key + '=' + value.value;
//                });
//            }
//            
//            content += ']';
//            
//            if(this.content_selected){
//                content += this.content_selected +  '[/' + this.shortcode + ']';
//            }
//            
//            tinymce.execCommand('mceInsertContent', false, content);
//            
            return true;
        },        
        open : function(){
            this.popup.dialog('open');
            this.ajaxCall();
        },
       
        jxData : {
            action: 'query_posts',
            tiny_request: 'post_list',
            pageNum : 1     
        },
        ajaxCall : function(){
            $.ajaxSetup({
                  beforeSend: function () {
                      selfView.spinner.spinning('show');
                  },
                  complete: function () {
                      selfView.spinner.spinning('hide');
                  }
             }); 
             
             
             this.jxData.pageNum = this.pageNum;
                          
             $.post(
                ajaxurl,
                this.jxData,
                function(response){
                    if(typeof(response) === 'object'){
                        selfView.ajaxCallback(response);
                    }
                },'json');
             
        }, 
        ajaxCallback : function(response){
            if(response.pageTotal === 0){
                this.message.html("Nessun risultato relativo ai criteri specificati.");
            }else{
                this.pageTotal = response.pageTotal; 
                this.makePagination();
                this.jxData.pageNum = response.pageNum;
              
                this.list.empty();
                ul = $('<ul></ul>').css('cursor', 'pointer'); 
                var li;
              
                i = -1;
                $.each(response.data, function(key, value){
                    i++;
                    li = $('<li></li>').appendTo(ul);
                    !(i % 2) ? li.addClass('alternate') : '';
                        
                    input = $('<input />').attr({
                        type:'hidden', 
                        'class': 'item-id'
                    })
                    .val(value.ID).appendTo(li);
                    span = $('<span></span>').attr({
                        'class': 'item-title'
                    })
                    .text(value.title).appendTo(li); 
                });
                this.list.append(ul);
            }
        },
        makePagination : function(){
            this.pagination.empty();
            
            var ul = $('<ul></ul>').appendTo(this.pagination);
            var li;
            if(this.pageNum > 1){
                li = $('<li></li>').addClass('btz-page-previous').appendTo(ul);
                li.text('«');
            }
            for (var i=0; i<this.pageTotal;i++){ 
                li = $('<li></li>').appendTo(ul);
                if((i + 1) == this.pageNum){
                    this.pageLoaded = li.addClass('pageLoaded');
                }
                li.text((i + 1).toString());
            }
            if(this.pageNum < this.pageTotal){
                li = $('<li></li>').addClass('btz-page-next').appendTo(ul);
                li.text("»");
            }
        },
        makePopup : function(id){
            this.popup = $('<div />').attr('id', 'btz-popup-post-choice').addClass('btz-popup-post').appendTo('body');
            this.postOptions = $('<div></div>').attr('id', 'post-options').addClass('post-options').appendTo(this.popup);
            
            this.message = $('<div></div>').appendTo(this.popup);
            
            rowIdDiv = $('<div></div>').appendTo(this.postOptions);
            rowId = $('<label></label>').appendTo(rowIdDiv);
            $('<span>Post ID</span>').appendTo(rowId);
            this.currentID =$('<span />').addClass('post-values').appendTo(rowId);
    
    
            rowTitleDiv = $('<div></div>').appendTo(this.postOptions);
            rowTitle = $('<label></label>').appendTo(rowTitleDiv);
            $('<span>Titolo</span>').appendTo(rowTitle);
            this.currentTitle =$('<span></span>').addClass('post-values').appendTo(rowTitle);
            
            
            
            
            this.pagination = $('<div></div>').attr('id', 'btz-pagination-choice').addClass('btz-pagination').appendTo(this.popup);
            this.list = $('<div></div>').attr({
                'id' : 'btz-recent-results-choice', 
                'class':'query-results'
            }).appendTo(this.popup);
        },
        loadPage : function(li, e){
            if ( li.hasClass('pageLoaded' ))
                return;
            if(li.hasClass('btz-page-previous') &&  this.pageNum > 1 ){
                this.pageNum -= 1;
            }else if(li.hasClass('btz-page-next') &&  this.pageNum < this.pageTotal){
                this.pageNum += 1;
            }else{
                this.pageNum = parseInt(li.text(), 10);
            }
            this.ajaxCall();
            
        },
        select : function(li, e){
          
            if ( li == this.selected )
                return;
            
            this.deselect();
            this.selected = li.addClass('selected');
                        
            selfView.currentID.html( $('.item-id', li).val());
            selfView.currentTitle.html( $('.item-title', li).text());
             
        },
        deselect: function() {
            if ( this.selected )
                this.selected.removeClass('selected');
            this.selected = false;
        }
    }
    
})(jQuery);


