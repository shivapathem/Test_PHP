USE [Allocate7]
GO

/****** Object:  Table [dbo].[ActivityChargeCodeMapping_Link]    Script Date: 31/12/2021 20:15:26 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'Price' AND Object_ID = Object_ID(N'[dbo].[ActivityChargeCodeMapping_Link]'))
BEGIN
	ALTER TABLE ActivityChargeCodeMapping_Link ALTER COLUMN Price decimal (18,2);
END

IF NOT EXISTS(SELECT 1 FROM REF_Forms WHERE FormName = 'Activity Code' AND FormDescription = 'Activity Code' AND IsActive = 1)
BEGIN
	INSERT INTO REF_Forms (FormName, FormDescription, IsActive) VALUES ('Activity Code', 'Activity Code', 1);
END
IF NOT EXISTS(SELECT 1 FROM RolePermissionForm_LINK WHERE RoleID = 2 AND PermissionID = 1 AND FormID = 19)
BEGIN
	INSERT INTO RolePermissionForm_LINK VALUES(2, 1, 19);
END
IF NOT EXISTS(SELECT 1 FROM RolePermissionForm_LINK WHERE RoleID = 2 AND PermissionID = 2 AND FormID = 19)
BEGIN
	INSERT INTO RolePermissionForm_LINK VALUES(2, 2, 19);
END
IF NOT EXISTS(SELECT 1 FROM RolePermissionForm_LINK WHERE RoleID = 2 AND PermissionID = 3 AND FormID = 19)
BEGIN
	INSERT INTO RolePermissionForm_LINK VALUES(2, 3, 19);
END
IF NOT EXISTS(SELECT 1 FROM RolePermissionForm_LINK WHERE RoleID = 2 AND PermissionID = 4 AND FormID = 19)
BEGIN
	INSERT INTO RolePermissionForm_LINK VALUES(2, 4, 19);
END
GO

IF EXISTS (SELECT object_id FROM sys.tables WHERE name = 'ActiveChargeCodeMapping_Link')
BEGIN
    IF NOT EXISTS(SELECT object_id FROM sys.tables WHERE name = 'ActivityChargeCodeMapping_Link')
    BEGIN
        exec sp_rename 'ActiveChargeCodeMapping_Link', 'ActivityChargeCodeMapping_Link';
    END
END
