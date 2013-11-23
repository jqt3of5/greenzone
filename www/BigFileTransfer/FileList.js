var fileItemInfos = {};

function InitFileList()
{
    var fileListDiv = document.getElementById("fileListDiv");
    var fileItems = document.getElementsByClassName("fileItem");

    for (var i = 0; i < fileItems.length; i++)
    {
	fileItems[i].onmouseover=function(){ this.style.background = "black"; };
	fileItems[i].onmouseout=function(){  this.style.background = "darkgrey"; };
     	fileItems[i].onclick=function(event)
	{ 
	    event = event || window.event;
	    var fileInfoView = document.getElementById("fileInfoView");
	    var fileInfoName = document.getElementById("fileInfoName");
	    var fileInfoSize = document.getElementById("fileInfoSize");

	    var x=0,y=0;
	    
	    x = event.clientX + window.scrollX + 20;
	    y = event.clientY + window.scrollY - 150;
	    
	    fileInfoView.style.left = x + "px";
	    fileInfoView.style.top = y + "px";
	    fileInfoView.style.display = "block"; 
	    fileInfoName.innerHTML = "FileName: " + fileItemInfos[this.id].name;
	    fileInfoSize.innerHTML = "File Size: " + fileItemInfos[this.id].size;
	    return false;
	};
    }
}
