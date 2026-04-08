
--page id 7
if not exists (select top 1 * from RolePermissionForm_LINK where formid = 7 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,7),(1,2,7)
	end 
	--page id 8
if not exists (select top 1 * from RolePermissionForm_LINK where formid = 8 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,8),(1,2,8),(1,3,8),(1,4,8)
	end 
--page id 9
if not exists (select top 1 * from RolePermissionForm_LINK where formid = 9 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,9),(1,2,9)
	end 

	--page id 10
if not exists (select top 1 * from RolePermissionForm_LINK where formid = 10 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,10),(1,2,10),(1,3,10),(1,4,10)
	end 

		--page id 11
if not exists (select top 1 * from RolePermissionForm_LINK where formid = 11 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,11),(1,2,11),(1,3,11)
	end 
--page id 12

if not exists (select top 1 * from RolePermissionForm_LINK where formid = 12 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,12),(1,2,12),(1,3,12),(1,4,12)
	end
-- PAGE ID 12

if not exists (select top 1 * from RolePermissionForm_LINK where formid = 12 and RoleID=2)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (2,1,12),(2,2,12),(2,3,12)
	end


-- PAGE ID 17-CHARGE


if not exists (select top 1 * from RolePermissionForm_LINK where formid = 17 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,17),(1,2,17),(1,3,17),(1,4,17)
	end

-- PAGE ID 18-WBS
if not exists (select top 1 * from RolePermissionForm_LINK where formid = 18 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,18),(1,2,18),(1,3,18),(1,4,18)
	end 

-- PAGE ID 19-ACTIVE CODE
if not exists (select top 1 * from RolePermissionForm_LINK where formid = 19 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,19),(1,2,19),(1,3,19),(1,4,19)
	end 
--pageid 16
	if not exists (select top 1 * from RolePermissionForm_LINK where formid = 16 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,16),(1,2,16),(1,3,16),(1,4,16)
	end 

--pageid 15
	if not exists (select top 1 * from RolePermissionForm_LINK where formid = 15 and RoleID=1)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (1,1,15),(1,2,15),(1,3,15),(1,4,15)
	end 
--pageid 3
if not exists (select top 1 * from RolePermissionForm_LINK where formid = 3 and RoleID = 3 and PermissionID=5)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (3,5,3)
	end 

	if not exists (select top 1 * from RolePermissionForm_LINK where formid = 3 and RoleID = 4 and PermissionID=5)
	begin 
		INSERT INTO RolePermissionForm_LINK (RoleID,PermissionID,FormID) 
		VALUES (4,5,3)
	end 