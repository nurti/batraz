jQuery(document).ready(function($) {
    
    
    tinymce.create('tinymce.plugins.btz_plugin', {
        init : function(ed, url) {
            var t = this;
            // t.editor = ed;
            
            // Register command for button marker
            ed.addCommand('btz-marker_insert_shortcode', function() {
                selected = tinyMCE.activeEditor.selection.getContent();
                if(selected){
                    var propsc = {
                        'class' :{
                            'type' : 'text',
                            'title': 'Class Style',
                            'value': 'marker'
                        }
                    }
                    
                    Batraz.panels.content_selected = selected;
                    Batraz.panels.open('marker', 'Evidenziatore', propsc);
                }
                    
            });
            
            // Register command for button btz-list
            ed.addCommand('btz-list_insert_shortcode', function() {
                Batraz.list.open('btz-list');  
            });
            
            // Register command for button btz-content
            ed.addCommand('btz-content_insert_shortcode', function() {
                    
                var propsc = {
                    'show_image':{
                        'title' : 'Mostra immagine',
                        'value'  : false
                    },
                    'show_excerpt':{
                        'title' : 'Mostra riassunto',
                        'value'  : false
                    }
                }
                    
                Batraz.post.open('btz-content', propsc);
            });
                
            // Register command for button btz-popup
            ed.addCommand('btz-popup_insert_shortcode', function() {
                selected = tinyMCE.activeEditor.selection.getContent();
                if(selected != ''){
                    Batraz.post.content_selected = selected;
                    Batraz.post.open('popup');
                }
                    
            });
                
            // Register command for button btz-tab
            ed.addCommand('btz-tab_insert_shortcode', function() {
                selected = tinyMCE.activeEditor.selection.getContent();
                if(selected){
                    var propsc = {
                        'name' :{
                            'type' : 'text',
                            'title': 'Nome',
                            'value': 'Tab name',
                            'required' :true
                        }
                    }
                    
                    Batraz.panels.content_selected = selected;
                    Batraz.panels.open('tab', 'Nome del Tab', propsc);
                }
                    
            });
                
            // Register command for button btz-tabs
            ed.addCommand('btz-tabs_insert_shortcode', function() {
                selected = tinyMCE.activeEditor.selection.getContent();
                if( selected ){
                     Batraz.panels.content_selected = selected;
                     Batraz.panels.open('tabs');
                }
                    
            });
            
            // Register command for button btz-tab
            ed.addCommand('btz-section_insert_shortcode', function() {
                selected = tinyMCE.activeEditor.selection.getContent();
                if(selected){
                    var propsc = {
                        'name' :{
                            'type' : 'text',
                            'title': 'Nome',
                            'value': 'Tab name',
                            'required' :true
                        }
                    }
                    
                    Batraz.panels.content_selected = selected;
                    Batraz.panels.open('section', 'Nome sezione', propsc);
                }
                    
            });
                
            // Register command for button btz-tabs
            ed.addCommand('btz-accordion_insert_shortcode', function() {
                selected = tinyMCE.activeEditor.selection.getContent();
                if( selected ){
                    var propsc = {
                        'heightStyle' :{
                           'type' : 'select',
                           'title': 'Height sezioni',
                           
                           'options' : {
                                        'auto' : 'Height max sezioni',
                                        'fill' : 'Height del contenitore',
                                        'content' : 'Height in base al contenuto'
                                    },
                           'value' : 'auto'         
                        },
                        'active' :{
                            'type' : 'checkbox',
                            'title': 'Attivo',
                            'value': true 
                        },
                        'collapsible' :{
                            'type' : 'checkbox',
                            'title': 'Pieghevole',
                            'value': false 
                        }
                        
                    }
                    
                     Batraz.panels.content_selected = selected;
                     Batraz.panels.open('accordion', 'Properties Accordion', propsc);
                }
                    
            });
            
            // Register command for button btz-accordion-delete
            ed.addCommand(Batraz.actions.accordionRemove.cmd, function() {
                var shortcodes = new Array("accordion", "section");
                Batraz.contentManager.removeShortcodes(shortcodes, ed, Batraz.actions.accordionRemove);
            });

            // Register command for button btz-tab-delete
            ed.addCommand(Batraz.actions.tabsRemove.cmd, function() {
                var shortcodes = new Array("tabs", "tab");
                Batraz.contentManager.removeShortcodes(shortcodes, ed, Batraz.actions.tabsRemove);
            });
            
            
            // Register buttons - trigger above command when clicked
            //ed.addButton('wpse72394_button', {title : 'Insert shortcode', cmd : 'wpse72394_insert_shortcode', image: url + '/path/to/image.png' });
            ed.addButton('btz-marker_button', {
                title : 'Evidenziatore', 
                cmd : 'btz-marker_insert_shortcode', 
                image: url + '/images/marker.png'
            });
            ed.addButton('btz-list_button', {
                title : 'Inserimento Lista posts', 
                cmd : 'btz-list_insert_shortcode', 
                image: url + '/images/btz-list.png'
            });
            ed.addButton('btz-content_button', {
                title : 'Include Post', 
                cmd : 'btz-content_insert_shortcode', 
                image: url + '/images/btz-content.png'
            });
            ed.addButton('btz-popup_button', {
                title : 'Popup Post', 
                cmd : 'btz-popup_insert_shortcode', 
                image: url + '/images/btz-popup.png'
            });
            ed.addButton('btz-tab_button', {
                title : 'Singolo Tab', 
                cmd : 'btz-tab_insert_shortcode', 
                image: url + '/images/tab.png'
            });
            ed.addButton('btz-tabs_button', {
                title : 'Tabs container', 
                cmd : 'btz-tabs_insert_shortcode', 
                image: url + '/images/tabs.png'
            });
            
            ed.addButton('btz-section_button', {
                title : 'Sezione Accordion', 
                cmd : 'btz-section_insert_shortcode', 
                image: url + '/images/section.png'
            });
            
            ed.addButton('btz-accordion_button', {
                title : 'Accordion Container', 
                cmd : 'btz-accordion_insert_shortcode', 
                image: url + '/images/accordion.png'
            });
            
            ed.addButton(Batraz.actions.tabsRemove.id, {
                title : Batraz.actions.tabsRemove.title,
                cmd : Batraz.actions.tabsRemove.cmd, 
                image: url + '/images/tabs-del.png'
            });
            
            ed.addButton(Batraz.actions.accordionRemove.id, {
                title : Batraz.actions.accordionRemove.title,
                cmd : Batraz.actions.accordionRemove.cmd, 
                image: url + '/images/accordion-del.png'
            });
            
           
            
            ed.onNodeChange.add(t.my_nodeChange, t);
        },
        my_nodeChange : function(ed, cm, n, co, o) {
            
            cm.setDisabled('btz-marker_button', co);
            
            cm.setDisabled('btz-popup_button', co);
            
            cm.setDisabled('btz-tab_button', co);
            cm.setDisabled('btz-tabs_button', co);
            
            cm.setDisabled('btz-accordion_button', co);
            cm.setDisabled('btz-section_button', co);
            
            
        }
    });
    
    // Register our TinyMCE plugin
    // first parameter is the button ID1
    // second parameter must match the first parameter of the tinymce.create() function above
    tinymce.PluginManager.add('btz_tinymce_plugin', tinymce.plugins.btz_plugin);

});


var Batraz = Batraz || {};

Batraz.list =(function($){
    var self;
    var btzLists = {
        popup : $('#btz-popup-lists'),
        init: function(obj){
            self = obj;
        },
        jxData : {
            action : 'shortcodes',
            tiny_request : 'taxonomies'
        },
        open: function(shortcode){
            this.shortcode = shortcode;
             
             
            if( this.popup.length == 0){
                
                this.makePopup();

                this.popup.dialog({
                    modal: true,
                    autoOpen : false,
                    title : 'Criteri Tassonoomici',
                    height: 450,
                    width: 400,
                    buttons :{
                        'Conferma scelta' : function(){
                            if(self.mceUpdate()){
                                $( this ).dialog( "close" );
                            }
                        }
                    }

                });
                this.ajax();   
            }
            this.popup.dialog('open');
        },
        mceUpdate : function(){
            
            content = '';
            $(this.popup).children('div').find('select').each(function(){
                if(this.value != '0'){
                       //alert(this.value + ' ' + this.name); 
                       content += ' ' + this.name + '=' + this.value;
                }
            });
            
            if(content != ''){
                content = '[' + self.shortcode + ' ' + content +  ']'
                tinymce.execCommand('mceInsertContent', false, content);
            }
            
            return true;
        },
        ajax : function(){
            
            $.ajaxSetup({
                beforeSend: function () {
                    self.progbar.show();
                },
                complete: function () {
                    self.progbar.hide();
                }
            });
            
            $.post(
                ajaxurl,
                this.jxData,
                function(response){
                    if(typeof(response) === 'object'){
                        self.ajaxCallback(response);
                    }
                },'json')
                
        },
        ajaxCallback : function(response){
            
            if( typeof(response) === 'object'){
                self.popup.empty();
                $.each(response, function(key, value){
                    var block = $('<div></div>').css({ padding:'7px',  padding:'0.5rem'}); 
                    $('<label></label').css('display', 'block').attr('labelfor', key).text(value.name).appendTo(self.popup);
                    var select = $('<select></select>').attr({'id' : key, 'name' : key}).css({'min-width' : '280px'}).appendTo(block);
                    $('<option />').val('0').text('[Scegli ' + value.name + ']').appendTo(select);
                    
                    var optval, option;
                    $.each(value.data, function(keyopt, valopt){
                        optval = ( key == 'category' ) ? valopt.id : valopt.slug;
                        option = $('<option />').val(optval).text(valopt.name).appendTo(select);
                    });
                    self.popup.append(block);
                });
            }
        },
        makePopup: function(){
            this.popup = $('<div></div>').attr('id', 'btz-popup-lists').appendTo('body');
            
            this.progbar = $('<div></div>').css({
                'min-height':'28px',
                'min-height':'2rem'
            }).appendTo(this.popup);
            this.progbar.hide();
            this.progbar.progressbar({
                value: false
            });
        }
    }
    $(document).ready( btzLists.init(btzLists) );
    return btzLists;
})(jQuery);


Batraz.post =(function($){
    var self;
    var btzPosts = {
        popup : $('#btz-popup-posts'),
        pageNum : 1,
        selected:false,
        content_selected : false,
        
        init: function(obj){
            $('#btz-recent-results li').live('click', function(e){
                self.select( $(this), e );
            });
            $('#btz-pagination li').live('click', function(e){
                self.loadPage( $(this), e );
            });
            
            $('.btz-propsc input').live('click', function(e){
                self.toggleChk( $(this), e );
            });
        
            self = obj;
        },
        
        
        
        
        jxData : {
            action : 'shortcodes',
            tiny_request : 'post_list'
            
        },
        open: function(shortcode, propsc){
            this.shortcode = shortcode;
             
             
            if( this.popup.length == 0){
                if (typeof(propsc) == 'object'){
                    this.propsc = propsc;
                }
                
                this.makePopup();

                this.popup.dialog({
                    modal: true,
                    autoOpen : false,
                    title : 'Scelta Post',
                    height: 500,
                    width: 500,
                    buttons :{
                        'Conferma scelta' : function(){
                            if(self.mceUpdate()){
                                $( this ).dialog( "close" );
                            }
                        }
                    }

                });
                this.ajax();   
            }
            this.popup.dialog('open');
        },
        mceUpdate : function(){
            
            if(!$.isNumeric(this.currentID.html()))
                return false;
            
            if( typeof (this.shortcode) == 'undefined' || this.shortcode == '' )
                return false;
            
            
            var id = this.currentID.html();
            var content = '[' + this.shortcode + ' id=' + id;
            
            if(typeof(this.propsc) == 'object'){
                $.each(this.propsc, function(key, value){
                    
                    if(value.value)
                        content += ' ' + key + '=' + value.value;
                });
            }
            
            content += ']';
            
            if(this.content_selected){
                content += this.content_selected +  '[/' + this.shortcode + ']';
            }
            
            tinymce.execCommand('mceInsertContent', false, content);
            
            return true;
        },
        ajax : function(){
            
            $.ajaxSetup({
                beforeSend: function () {
                    self.progbar.show();
                },
                complete: function () {
                    self.progbar.hide();
                }
            });
            
            this.jxData.pageNum = this.pageNum;
            $.post(
                ajaxurl,
                this.jxData,
                function(response){
                    if(typeof(response) === 'object'){
                        self.ajaxCallback(response);
                    }
                },'json')
                
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
            this.ajax();
            
        },
        
        select : function(li, e){
            if ( li == this.selected )
                return;
            
            this.deselect();
            this.selected = li.addClass('selected');
                        
            self.currentID.html( $('.item-id', li).val());
            self.currentTitle.html( $('.item-title', li).text());
             
        },
        
        
        deselect: function() {
            if ( this.selected )
                this.selected.removeClass('selected');
            this.selected = false;
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
            
        
        makePopup: function(){
            var rowIdDiv, rowTitleDiv,rowId, rowTitle;
            
            this.popup = $('<div></div>').attr('id', 'btz-popup-post').addClass('btz-popup-post').appendTo('body');
    
            this.postOptions = $('<div></div>').attr('id', 'post-options').addClass('post-options').appendTo(this.popup);
           
            rowIdDiv = $('<div></div>').appendTo(this.postOptions);
            rowId = $('<label></label>').appendTo(rowIdDiv);
            $('<span>Post ID</span>').appendTo(rowId);
            this.currentID =$('<span />').addClass('post-values').appendTo(rowId);
    
    
            rowTitleDiv = $('<div></div>').appendTo(this.postOptions);
            rowTitle = $('<label></label>').appendTo(rowTitleDiv);
            $('<span>Titolo</span>').appendTo(rowTitle);
            this.currentTitle =$('<span></span>').addClass('post-values').appendTo(rowTitle);
            
            this.makeCheckbox();
            

            this.message = $('<div></div>').appendTo(this.popup);
            
            this.progbar = $('<div></div>').css({
                'min-height':'28px',
                'min-height':'2rem'
            }).appendTo(this.popup);
            this.progbar.hide();
            this.progbar.progressbar({
                value: false
            });
            
            this.pagination = $('<div></div>').attr('id', 'btz-pagination').addClass('btz-pagination').appendTo(this.popup);
            this.list = $('<div></div>').attr({
                'id' : 'btz-recent-results', 
                'class':'query-results'
            }).appendTo(this.popup);
        },
        makeCheckbox : function(){
            if(typeof(this.propsc) != 'undefined'){
                var divOpt = $('<div></div>').addClass('btz-propsc').appendTo(self.postOptions);
                $.each(this.propsc, function(key, value){
                    id = self.shortcode + '_' + key;
                    
                    var label = $('<label></label>').appendTo(divOpt);
                    var input = $('<input />').attr({
                        'id' : id, 
                        'name' : id, 
                        type : 'checkbox'
                    }).appendTo(label);
                    label.append(" " + value.title);
                    
                    
                });

            }
        }, 
        toggleChk : function(chk, e){
            if(typeof(this.propsc) != 'undefined'){
                var prop = chk[0].id.replace(this.shortcode + '_', '');
               
                if(this.propsc.hasOwnProperty(prop)){
                 
                    this.propsc[prop]['value'] = (chk[0].checked) ? true : false;
                }
            }
           
        }
        
    }
    $(document).ready( btzPosts.init(btzPosts) );
    return btzPosts;
})(jQuery);



/*
 *   PANELS
 * 
 */

Batraz.panels =(function($){
    var self;
    panels = {
        content_selected : false,
        popup : $('#btz-popup-panels'),
        dialogCreated : false,
        init: function(obj){
            self = obj;
        },
        
        open: function(shortcode, title, propsc){
            this.shortcode = shortcode;
            if (typeof(propsc) == 'object'){
                this.propsc = propsc;
            }else{
                this.mceUpdate();
                return;
            }
            this.makePopup(); 
             
            if( !this.dialogCreated){
            
                this.popup.dialog({
                    modal: true,
                    autoOpen : false,
                    title : 'Scelta Post',
                    height: 300,
                    width: 350,
                    buttons :{
                        'Conferma scelta' : function(){
                            var r = self.mceUpdate();
                            if(r){
                                $( this ).dialog( "close" );
                            }
                        }
                    }

                });
                 
            }
            this.popup.dialog( "option", "title", title );
            this.popup.dialog('open');
        },
        mceUpdate : function(){
            
            if( typeof (this.shortcode) == 'undefined' || this.shortcode == '' )
                return false;
            
            var content = '[' + this.shortcode ;
            
            if(typeof(this.propsc) == 'object'){
                var result = true;
                $.each(this.propsc, function(key, prop){
                   switch(prop.type){
                       case 'checkbox':
                          var checked = prop.control.attr('checked');
                          prop.value = (checked === 'checked') ? true : false;
                          break;
                           
                       case 'select':
                           prop.value = prop.control.val();
                           break;
                           
                       case 'text':
                           var value = prop.control.val();
                           if(prop.required && value === ''){
                               result = false;
                           }else{
                               prop.value = value;
                           }
                           break;
                       
                       default:
                           result = false;
                   } 
                   
                   if(prop.value || prop.type === 'checkbox')
                        content += ' ' + key + '=' + prop.value;
                });
                if(!result)return false;
            }
            
            content += ']';
            
            if(this.content_selected){
                content += this.content_selected +  '[/' + this.shortcode + ']';
            }
            
            tinymce.execCommand('mceInsertContent', false, content);
            
            return true;
        },
        
        makePopup: function(){
            
            this.popup.empty();
            this.popup = $('<div></div>').attr('id', 'btz-popup-panels').addClass('btz-popup-panels'). appendTo('body');
    
            this.postOptions = $('<div></div>').attr('id', 'post-options-panels').addClass('post-options-panels').appendTo(this.popup);
           
            this.makeControls();
            
        },
        makeControls : function(){
            if(typeof(this.propsc) != 'undefined'){
                var divOpt = $('<div></div>').addClass('btz-propsc-panels').appendTo(self.postOptions);
                $.each(this.propsc, function(key, prop){
                    
                    if(!prop.hasOwnProperty('type') ){
                       prop.type = 'text';  
                    }
                    
                    var id = self.shortcode + '_' + key;
                    prop.controlId = id;
                    
                    switch(prop.type){
                        case 'select':
                            self.makeSelect(key, prop, divOpt);
                            break;
                        case 'checkbox':
                            self.makeCheckbox(key, prop, divOpt);
                            break;
                        case 'text':
                            self.makeInputText(id, prop, divOpt);
                            break;    
                    }
                    
                   
                    
                });

            }
        }, 
        
        makeSelect : function(key, prop, container){
            
//            var id = this.shortcode + '_' + key;
            var label = $('<label></label>').appendTo(container);
            var select = $('<select></select>').attr({ 'id' : prop.controlId, 'name' : prop.controlId }).appendTo(label);
            
            $.each(prop.options, function(key, name){
                option = $('<option />').val(key).text(name).appendTo(select);
            });
            
            prop.control = select;
            
        },
        selectChange : function(opt, e){
            if(typeof(this.propsc) != 'undefined'){
                var prop = opt[0].id.replace(this.shortcode + '_', '');
                if(this.propsc.hasOwnProperty(prop)){
                     this.propsc[prop]['value'] = opt.val();
                }
               
            }
        },
        
        makeInputText : function(id, prop, container){

           // var id = self.shortcode + '_' + key;

            var label = $('<label></label>').appendTo(container);
            var input = $('<input />').attr({
                'id' : id, 
                'name' : id, 
                type : 'text'
            }).appendTo(label);
            label.append(" " + prop.title);
            
            prop.control = input;
        },
        
        makeCheckbox : function(key, prop, container){

            //var id = self.shortcode + '_' + key;

            var label = $('<label></label>').appendTo(container);
            var input = $('<input />').attr({
                'id' : prop.controlId, 
                'name' : prop.controlId, 
                type : 'checkbox'
            }).appendTo(label);
            label.append(" " + prop.title);
            if(prop.value){
                input.attr('checked' , 'checked');
            }
            
            prop.control = input;
        },
        
        toggleChk : function(chk, e){
            if(typeof(this.propsc) != 'undefined'){
                var prop = chk[0].id.replace(this.shortcode + '_', '');
               
                if(this.propsc.hasOwnProperty(prop)){
                 
                    this.propsc[prop]['value'] = (chk[0].checked) ? true : false;
                }
            }
           
        }
        
    }
    $(document).ready( panels.init(panels) );
    return panels;
})(jQuery);


/*
 *   CONTENT MANAGER
 * 
 */

Batraz.contentManager =(function($){
    var self;
    
    var cm = { 
        shortcodes : [],
        content : '',
        newContent : '',
        patterns : [],
        matches : [],
        popup : $('#btz-popup-confirm'),
        dialogCreated : false,
        
        
        init: function(this_obj){
            self = this_obj;
        },
        popupConfirm : function(){
            if(!this.dialogCreated){
                
                this.makePopup();
                this.popup.dialog({
                    resizable: false,
                    height:200,
                    modal: true,
                    autoOpen : false,
                    buttons: {
                      "Conferma rimozione": function() {
                        self.editor.setContent(self.newContent);  
                        $( this ).dialog( "close" );
                      },
                      "Annulla": function() {
                        $( this ).dialog( "close" );
                      }
                    }
                });
            }   
            
            this.popup.dialog('open');

            
        },
        makePopup : function(){
            this.popup.empty();
            this.popup = $('<div></div>').attr({'id':'btz-popup-confirm', 'title' : this.cmd.title}).appendTo('body');
            var p = $('<p></p>').appendTo(this.popup);
            $('<span></span>').addClass("ui-icon ui-icon-alert")
                              .css({"float": "left" , "margin" : "0 7px 20px 0"}).appendTo(p);
            p.text(this.cmd.message);                              
                              
            this.dialogCreated = true;                              
            
        },
        removeShortcodes : function(shortcodes, ed, cmd){
            
            if( typeof(ed) != 'object' )return;
            if(typeof(ed.getContent) !== 'function')return;
            
            this.shortcodes = shortcodes;
            this.editor = ed;
            this.cmd = cmd;
            this.content = ed.getContent();
            
            this.makePatterns();
            this.makeMatches();
            
            this.newContent = this.content;
            for (var i = 0; i < this.matches.length; i++){
                this.newContent = this.newContent.replace(this.matches[i], "");
            }
            if(this.newContent != this.content){
                this.popupConfirm();
            }
            
        },
        
        makeMatches : function(){
             
            this.matches = [];
            for (var i = 0; i < this.patterns.length; i++){
               var p = this.makeMatch(this.patterns[i]);
               if( p != null){
                   for(var j=0; j < p.length; j++){
                        this.matches.push(p[j]);
                   }
               }
            }
            
            return this.matches;
        },
        
        makeMatch : function(pattern){
            var m = this.content.match(pattern);
            return m;
        },
        
        makePatterns : function(){
            this.patterns = [];
            for (var i = 0; i < this.shortcodes.length; i++){
               var p = this.makePattern(this.shortcodes[i]);
               for(var j=0; j < p.length; j++){
                    this.patterns.push(p[j]);
               }
            }

        },
        
        makePattern : function(shortcode){
            var patternsShortcode = new Array();
            patternsShortcode[0] = new RegExp("\\[" + shortcode + ".*?\\]", "g");
            patternsShortcode[1] = new RegExp("\\[/" + shortcode + ".*?\\]", "g");
            return patternsShortcode;
        }
    }
    
    $(document).ready( cm.init(cm) );
    return cm;
    
    
})(jQuery);

Batraz.actions = {
    accordionRemove : {
        id : 'btz-accordion-delete_button',
        cmd : 'btz-accordion-delete_shortcode',
        title : "Elimina Accordion",
        message : "Confermi eliminazione shortcodes accordion ?"
    },
    tabsRemove : {
        id : 'btz-tabs-delete_button',
        cmd : 'btz-tabs-delete_shortcode',
        title : "Elimina Tabs",
        message : "Confermi eliminazione shortcodes tabs ?"
    }
}
