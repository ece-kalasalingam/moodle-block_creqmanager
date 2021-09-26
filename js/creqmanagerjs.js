//
// Add a new menu item into the optional drop down field on
// form page 1.
function addNewItem(label){
    jQuery.ajaxSetup({async:false});
    var value = document.getElementById('newitem').value;
    var id = document.getElementsByName('id')[0].value;
    if(value !== "") {
        $.post("adminsettings.php", { valuetoadd: value, type: 'add', id:id},
        function(data, status, xhr) { // success callback function
            if (status=='success') {
                $('#newitem').val('');
                const obj = JSON.parse(data);
                if (obj.success == 1) {
                    var newitemhtml = '<div class="row" id="'+ obj.id+'">';
                    newitemhtml += '<div class="col-sm-6">' +obj.valueadded+ '</div>'
                    newitemhtml +=  '<div class="col-sm-2"><a onclick="return deleteItem('+obj.id+')" href="#';
                    newitemhtml += '" aria-label="' +label+'" title="' +label + '"data-id="'+ obj.id; 
                    newitemhtml +=  '"><i class="icon fa fa-trash fa-fw" aria-hidden="true"></i></a></div>';
                    newitemhtml += '</div>';
                    document.getElementById('optfield2values').innerHTML += newitemhtml;
                       require(['core/notification'], function(notification) {
                           notification.addNotification({
                               message: "Added Successfully",
                               type: "success"
                          });
                       });
                }
            }
        });
    }
    return false;
}
//
// Delete an item from the optional drop down field on
// form page 1.
function deleteItem(dataid){
    jQuery.ajaxSetup({async:false});
    var id = document.getElementsByName('id')[0].value;
    if(dataid !== "") {
        $.post("adminsettings.php", { deleteid: dataid, type: 'delete', id:id},
            function(data, status, xhr) {
               var myobj = document.getElementById(dataid);
               myobj.remove();
               require(['core/notification'], function(notification) {
                           notification.addNotification({
                               message: "Deleted Successfully",
                               type: "success"
                          });
                       });
            }
        );
    }
    return false;

}
//
// Add a new menu item into the entry field 4 term name
// form page 1.
function addNewTerm(label){
    jQuery.ajaxSetup({async:false});
    var value = document.getElementById('newterm').value;
    var id = document.getElementsByName('id')[0].value;
    if(value !== "") {
        $.post("adminsettings.php", { valuetoadd: value, type: 'addterm', id:id},
        function(data, status, xhr) { // success callback function
            if (status=='success') {
                $('#newterm').val('');
                const obj = JSON.parse(data);
                if (obj.success == 1) {
                    var newitemhtml = '<div class="row" id="'+ obj.id+'">';
                    newitemhtml += '<div class="col-sm-6">' +obj.valueadded+ '</div>'
                    newitemhtml +=  '<div class="col-sm-2"><a onclick="return deleteItem('+obj.id+')" href="#';
                    newitemhtml += '" aria-label="' +label+'" title="' +label + '"data-id="'+ obj.id; 
                    newitemhtml +=  '"><i class="icon fa fa-trash fa-fw" aria-hidden="true"></i></a></div>';
                    newitemhtml += '</div>';
                    document.getElementById('entryfield3values').innerHTML += newitemhtml;
                       require(['core/notification'], function(notification) {
                           notification.addNotification({
                               message: "Added Successfully",
                               type: "success"
                          });
                       });
                }
            }
        });
    }
    return false;
}
//
// Add a new group name 
// for course creation
function addNewGroupName(label){
    jQuery.ajaxSetup({async:false});
    var newgroupvalue = document.getElementById('newgroupname').value;
    var id = document.getElementsByName('id')[0].value;
    if(newgroupvalue  !== "") {
        $.post("adminsettings.php", { valuetoadd: newgroupvalue, type: 'addgroupname', id:id},
        function(data, status, xhr) { // success callback function
            if (status=='success') {
                $('#newgroupname').val('');
                const obj = JSON.parse(data);
                if (obj.success == 1) {
                    var newitemhtml = '<div class="row"  id="'+ obj.id+'">';
                    newitemhtml += obj.valueadded;
                    newitemhtml +=  '<a onclick="return deleteItem('+obj.id+')" href="#';
                    newitemhtml += '" aria-label="' +label+'" title="' +label + '"data-id="'+ obj.id; 
                    newitemhtml +=  '"><i class="icon fa fa-trash fa-fw" aria-hidden="true"></i></a>';
                    newitemhtml += '</div>';
                    document.getElementById('groupnames').innerHTML += newitemhtml;
                       require(['core/notification'], function(notification) {
                           notification.addNotification({
                               message: "Added Successfully",
                               type: "success"
                          });
                       });
                }
            }
        });
    }
    return false;
}
//
// Add a new cohort on
// form page 1.
function addNewCohort(label){
    jQuery.ajaxSetup({async:false});
    var selectvalue = document.getElementById('customint1').value;
    var id = document.getElementsByName('id')[0].value;
    var cohortvalueadded, selectobject;
    if(selectvalue !== "") {
        $.post("adminsettings.php", { valuetoadd: selectvalue, type: 'addcohort', id:id},
        function(data, status, xhr) { // success callback function
            if (status=='success') {
                const obj = JSON.parse(data);
                if (obj.success == 1) {
                    selectobject = document.getElementById('customint1');
                    for (var i=0; i<selectobject.length; i++) {
                        if (selectobject.options[i].value == selectvalue) {
                            cohortvalueadded = selectobject.options[i].text;
                            selectobject.remove(i);
                        }
                    }
                    var newitemhtml = '<div  id="'+ obj.id+'">';
                    newitemhtml += cohortvalueadded;
                    newitemhtml +=  '<a onclick="return deleteItem('+obj.id+')" href="#';
                    newitemhtml += '" aria-label="' +label+'" title="' +label + '"data-id="'+ obj.id; 
                    newitemhtml +=  '"><i class="icon fa fa-trash fa-fw" aria-hidden="true"></i></a>';
                    newitemhtml += '</div>';
                    document.getElementById('customint1values').innerHTML += newitemhtml;
                       require(['core/notification'], function(notification) {
                           notification.addNotification({
                               message: "Added Successfully",
                               type: "success"
                          });
                       });
                }
            }
        });
    }
    return false;
}
//
// Toggle the deletion status of records
function deleteRecord(dataid, toggletype){
    jQuery.ajaxSetup({async:false});
    if(dataid !== "") {
        $.post("manager.php", { toggleid: dataid, type: toggletype },
            function(data, status, xhr) {
               require(['core/notification'], function(notification) {
                           notification.addNotification({
                               message: "Success",
                               type: "success"
                          });
                       });
                window.location.reload(false);
            }
        );
    }
    return false;

}