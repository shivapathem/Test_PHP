if not exists (select top 1 * from RolePermissionForm_LINK where formid = 6 and RoleID=3 and PermissionID = 4)
begin
    INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID)
    VALUES (3,4,6)
end
