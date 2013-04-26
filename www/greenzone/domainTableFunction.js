
function $(id)
{
  return document.getElementById(id);
}

var response = "";

//batch edit
function zoneEditBatch(json)
{
  var xmlhttp;
  if (window.XMLHttpRequest)
    {
	// code for IE7+, Firefox, Chrome, Opera, Safari
	xmlhttp=new XMLHttpRequest();
    }

  xmlhttp.onreadystatechange = function(){
      response += xmlhttp.responseText;
  }

  xmlhttp.open('GET', "zoneedit.php?json="+json, false);
  xmlhttp.send();
    
}
function zoneEdit(command, domain, zone,  type, value)
{
  var xmlhttp;
  if (window.XMLHttpRequest)
    {
	// code for IE7+, Firefox, Chrome, Opera, Safari
	xmlhttp=new XMLHttpRequest();
    }

  xmlhttp.onreadystatechange = function(){
      response += xmlhttp.responseText;
  }

  xmlhttp.open('GET', "zoneedit.php?command="+command+"&domain=" + domain +"&zone=" + zone + "&type=" + type +"&value=" + value, false);
  xmlhttp.send();
}


var ZoneEnum = {UPDATE: "update", ADD: "add", DELETE: "delete"};
var _rowsToCommit = new Array();
var _oldRows = new Array();

function editRow(index)
{

    return "<td id='domain"+index+"'>"+$('domain'+index).innerHTML+"</td>\
         <td><select id='type"+index+"'>\
             <option value='A'>A</option>\
             <option value='CNAME'>CNAME</option>\
        </select></td>\
 <td><input type='text' id='value"+index+"'></input></td>\
 <td><a href='javascript:void(0)' onClick='stopedit("+index+")'>Cancel</a></td>";

}

function createNewRow(index)
{

return "<td><input type='text' id='domain"+index+"'></input>\
     <select id='zone"+index+"'>\
         <option value='jqt3of5.com'>jqt3of5.com</option>\
     </select></td>\
 <td><select id='type"+index+"'>\
     <option value='A'>A</option>\
     <option value='CNAME'>CNAME</option>\
 </select></td>\
 <td><input type='text' id='value"+index+"'></input></td>\
 <td><a href='javascript:void(0)' onClick='removerow("+index+")'>Cancel</a></td>";

}


function removerow(index)
{
    var row = $('row'+index);
    row.parentElement.removeChild(row);
    delete _rowsToCommit[index];
}
function stopedit(index)
{
    var row = $('row'+index);
    row.innerHTML = _oldRows[index];
    delete _rowsToCommit[index];
}
function deleterow(index)
{
    var row = $('row'+index);
    row.style.textDecoration = "line-through";
    _rowsToCommit[index] = ZoneEnum.DELETE;
}
function editrow(index)
{
    var row = $('row'+index);
    _oldRows[index] = row.innerHTML;
    row.innerHTML = editRow(index);
    _rowsToCommit[index] = ZoneEnum.UPDATE;

  //populate with data
  //......................
}
function addrow()
{
  var table = $('domainTable');
  var rowCount = table.rows.length;
  var newRow = table.insertRow(rowCount);
  newRow.id = 'row'+rowCount;
  newRow.innerHTML = createNewRow(rowCount);
  _rowsToCommit[rowCount] = ZoneEnum.ADD;
}

function submitChanges()
{
    var result = new Array();
    for (var i in _rowsToCommit)
    {
	var newRequest = {};
	var domain, zone, type, value;

	if (_rowsToCommit[i] != ZoneEnum.ADD)
	{
	    var re = /^(.*)\.(jqt3of5\.com)$/;
	    if (!re.test($('row'+i).cells[0].innerHTML))
	    {
		console.log($('row'+i).cells[0].innerHTML);
		console.log("failed?");
	    }
	    domain = RegExp.$1;
	    zone = RegExp.$2;
	    if (_rowsToCommit[i] == ZoneEnum.UPDATE)
	    {
 		type = $('type'+i).value;
		value = $('value'+i).value;
	    }else if (_rowsToCommit[i] == ZoneEnum.DELETE){
 		type = $('type'+i).innerHTML;
		value = $('value'+i).innerHTML;
	    }
	}
	else if (_rowsToCommit[i] == ZoneEnum.ADD){
	    domain = $('domain'+i).value
	    zone = $('zone'+i).value
	    type = $('type'+i).value;
	    value = $('value'+i).value;
	    
	}
	newRequest.command = _rowsToCommit[i];
	newRequest.domain = domain;
	newRequest.zone = zone;
	newRequest.type = type; 
	newRequest.value = value; 
	result.push(newRequest);

	// if (_rowsToCommit[i] == ZoneEnum.UPDATE)
	// {
	//     var re = /^(.*)\.(jqt3of5\.com)$/;
	//     if (!re.test($('row'+i).cells[0].innerHTML))
	//     {
	// 	console.log($('row'+i).cells[0].innerHTML);
	// 	console.log("failed?");
	//     }
	//     var domain = RegExp.$1;
	//     var zone = RegExp.$2;
	//     zoneEdit("update", domain, zone, $('type'+i).value, $('value'+i).value);
	// } else if (_rowsToCommit[i] == ZoneEnum.ADD) {
	//     zoneEdit("add", $('domain'+i).value, $('zone'+i).value, $('type'+i).value, $('value'+i).value);
	// } else if (_rowsToCommit[i] == ZoneEnum.DELETE) {
	//     var re = /^(.*)\.(jqt3of5\.com)$/;
	//     if (!re.test($('row'+i).cells[0].innerHTML));
	//     var domain = RegExp.$1;
	//     var zone = RegExp.$2;
	//     zoneEdit("delete", domain, zone, $('type'+i).value, $('value'+i).value);
	// }
    }
    alert(JSON.stringify(result));
    zoneEditBatch(JSON.stringify(result));
    alert(response);

}

window.onload = function()
{
    var table = $('domainTable');

    for (var i = 1, row; row = table.rows[i]; ++i)
    {
	var editCell = $("edit" + i);
	var deleteCell = $("delete"+i);
	var domain = $("domain" + i);
	var zone = $("zone" + i);
	var type = $("type" + i);
	var value = $("value" + i);

	deleteCell.innerHTML = "<a href='javascript:void(0)' onClick=\"zoneEdit('delete','"+domain+"','"+zone+"','"+type+"','"+value+"');location.reload();\">Delete</a>";
	editCell.innerHTML = "<a href='newDomain.php?command=edit&domain="+domain+"&zone="+zone+"'>Edit</a>";;
    }
};
