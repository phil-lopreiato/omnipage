// javascript functions to editing capabilities like the AJAX CMS

function toggleEditBox(){
    $("#editBox").toggle();

    }
function addModule(modId,secondary){
    secondary = secondary==undefined?false:secondary;
    $.get("/omni/ajax/addMod.php",
        {modId:modId,pageId:(secondary?"0":pageId),mode:"add"},
            function(){
                location.reload();
    });
}

function listModules(){
    $("#listModulesButton").hide();
    $.get("/omni/ajax/addMod.php",
        {mode:"list",pageId:pageId},
            function(data){
                $("#modList").hide().html(data).slideDown();
                $("#modList A").draggable({revert:true});
    });
}

function showMod(mod){
    $.get("/omni/ajax/modEdit.php",{mode:"showMod",modId:mod},function(data){
        $("#mod_"+mod).html(data);
    });
}
function selectEdit(){
    $("div").each(function(){
        if($(this).attr("id").substr(0,4)=="mod_"){
            $(this).hover(function(){$(this).css("backgroundColor","#ddddff")},function(){$(this).css("backgroundColor","")});
            $(this).click(function(){
                editObj = this;
                $(editObj).html("Loading...");
                var data = $(this).attr("id").split("_");
                var mod = data[1];
                $.get("/omni/ajax/modEdit.php",
                {modId: mod,mode:"renderEdit"},
                function(data){
                    $(editObj).html(data+"<br/><p><button id='returnToMod' onclick='showMod("+mod+")'>Cancel</button>");
                    $("DIV").each(function(){
                        if($(this).attr("id").substr(0,4)=="mod_"){
                            $(this).unbind("click mouseenter mouseleave");
                            $(this).css("backgroundColor","");
                    }});
                });
            });
        }});
    }
function saveMod(pageId,modId,properties){

    window.pageIdd = pageId;
    properties.modId = modId;
    properties.pageId = pageId;
    properties.mode = "saveMod";
    var jqxhr = $.get("/omni/ajax/modEdit.php",properties,
    function(data){
        $("#mod_"+modId).html(data);
    });

}

function selectDel(){
    $("div").each(function(){
        if($(this).attr("id").substr(0,4)=="mod_"){
            $(this).hover(function(){$(this).css("backgroundColor","#ffdddd")},function(){$(this).css("backgroundColor","")});
            $(this).click(function(){
                confi = confirm("Are you sure you want to delete this module?");
                if(confi){
                    delObj = this;
                    $(delObj).html("Deleting...");
                    var data = $(this).attr("id").split("_");
                    $.get("/omni/ajax/modEdit.php",
                    {modId:data[1],mode:"delete"},
                    function(data){
                        $(delObj).hide();
                        $("DIV").each(function(){
                            if($(this).attr("id").substr(0,4)=="mod_"){
                                $(this).unbind("click mouseenter mouseleave");
                                $(this).css("backgroundColor","");
                        }});
                    });
                }
            });
        }});
    }

function selectHistory(){
    $("div").each(function(){
        if($(this).attr("id").substr(0,4)=="mod_"){
            $(this).hover(function(){$(this).css("backgroundColor","#89FF65")},function(){$(this).css("backgroundColor","")});
            $(this).click(function(){
                selObj = this;
                $(selObj).html("Fetching Edits...");
                var data = $(this).attr("id").split("_");
                $.get("/omni/ajax/modHistory.php",
                {modId:data[1],mode:"getEdits"},
                function(data){
                    $(selObj).html(data);
                    $("DIV").each(function(){
                    $(this).unbind("click mouseenter mouseleave");
                    $(this).css("backgroundColor","");
                    });
                });
            });
        }});
    }

function getEditData(mod,id){
    $('#editData').html('Fetching Edit Data...');
    $('#editData').toggle();
    $.get("/omni/ajax/modHistory.php",{modId:mod,id:id,mode:"getEditData"},
    function(data){
        $('#editData').html(data);

    });

}

function revertEdit(mod, editId){
    $.get("/omni/ajax/modHistory.php",{modId:mod,id:editId,mode:"restoreEdit"},
    function(data){
        alert(data);
        showMod(mod);
    });
}

function pageHistory(){
    //$.get("/omni/ajax/modEdit");
}

$(document).ready(function(){
                $("#topMenuContainer").mouseleave(function(){
                $("#secondMenuDiv").slideUp(200);
            });

            $("#rearrangeBox").change(function(){
                if($("#rearrangeBox:checked").val()!=null){
                    //enable drag & drop
                    $(".module").css("border","dashed #ccc 2px").css("padding","0px");
                    $(".modSort").sortable({disabled:false});
                    $(".modSort").disableSelection();
                    }
                else{
                    //disable drag & drop
                    $(".module").css("border","").css("padding","2px");
                    $(".modSort").sortable({disabled:true});
                    $(".modSort").enableSelection();

                    //update on server
                    newOrderMain = new Array();
                    $("DIV").each(function(){
                        if($(this).attr("id").substr(0,4)=="mod_"){
                            var modId = $(this).attr("id").split("_")[1];
                            //add to main order array
                            newOrderMain[newOrderMain.length]=modId;
                        }});
                    //update main order
                    $.get("/omni/ajax/resort.php",
                    {order:newOrderMain.join(",")});
            }});
    })
