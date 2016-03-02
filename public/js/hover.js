var img_hover={
    
    target:"",  
    mouse_pos:[],
    demo:document.getElementById("demo"),
    hover:document.getElementById("hover"),
    
    get_ass: function(e)
    {
        e = e || window.event;
        mouse_pos = [e.pageX + 20 , e.pageY];
        target =  e.target || e.srcElement;
    },
    make_img: function()
    {
        demo.innerHTML = "<img id='hover' src='"+target.src+"' />";
        var window_width        = window.innerWidth;
        var img_natural_width   = hover.naturalWidth;
        var window_height       = window.innerHeight;
        var img_natural_height  = hover.naturalHeight;
        
        if(window_height >= img_natural_height){
           hover.style.height = img_natural_height; 
        }else{
            hover.style.height    = "90%";
            hover.style.maxHeight = (window_width - 100);
        }
        
        if(window_width >= img_natural_width){
            hover.style.width = img_natural_width;
        }else{
            hover.style.width    = "90%";
            hover.style.maxWidth = (window_width - 100);
        }
 
    },

    move_img: function()
    {  
        var window_height   = window.innerHeight;   
        var img_height      = hover.height;
        var pos             = ((window_height - img_height) / 2)
        console.log(mouse_pos[0]);
		console.log(hover.height);
		console.log( window.innerHeight + "iner");
        hover.style.left    = mouse_pos[0]; 
        hover.style.top     = (document.body.scrollTop + pos); 
    },
    
    delete_img: function()
    {
        hover.parentNode.removeChild(hover);
    }
	
	



};

