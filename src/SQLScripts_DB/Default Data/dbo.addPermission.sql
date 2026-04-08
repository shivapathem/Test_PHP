
USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM RolePermissionForm_LINK where RoleID = 1 and PermissionID = 3 AND FormID = 7)
BEGIN
      INSERT INTO [dbo].[RolePermissionForm_LINK]
           ([RoleID]
           ,[PermissionID]
           ,[FormID])
     VALUES(1,3,7)
End
