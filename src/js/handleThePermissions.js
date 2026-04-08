/*
* objective: Centralized permission validation functions on landing page

* Description: it will check the permissions
* */


function getPagePermission(pageID,teamID) {
 $.ajax({
     url: "function-includes/common/handleThePermission.php",
     type: "POST",
     dataType: "json",
     async:false,
     data: {
         'pageId': pageID,
         'teamID':teamID,
     },
     success: function (data) {

         if (data.status == 'success') {
          
             if (data.permissions.canview == 0 || data.permissions.canview == null) {
                 $('#content').load('page-includes/no_access.php', function () {
                 });
             }
             if(data.permissions.cancreate == 1){
                $('#js_create').val(1);
             }
         }
     }
 });
}


function getPagePermissionByTeamID(pageID,teamID) {
    $.ajax({
        url: "function-includes/common/handleThePermission.php",
        type: "POST",
        dataType: "json",
        async:false,
        data: {
            'pageId': pageID,
            'teamID':teamID,
            'task':'getpermissionbyteam'
        },
        success: function (data) {
   
            if (data.status == 'success') {
               if(data.permissions.cancreate);
               return data.permissions.cancreate
            }
        }
    });
   }


