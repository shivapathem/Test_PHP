USE [Allocate7]
GO
SET IDENTITY_INSERT [dbo].[REF_Forms] ON 

IF NOT EXISTS (SELECT 1 FROM [dbo].[RolePermissionForm_LINK] WHERE RoleID=3 AND PermissionID=1 AND FormID = 14)
              insert into RolePermissionForm_LINK values(3, 1, 14)
IF NOT EXISTS (SELECT 1 FROM [dbo].[RolePermissionForm_LINK] WHERE RoleID=3 AND PermissionID=2 AND FormID = 14)
              insert into RolePermissionForm_LINK values(3, 2, 14)
IF NOT EXISTS (SELECT 1 FROM [dbo].[RolePermissionForm_LINK] WHERE RoleID=3 AND PermissionID=3 AND FormID = 14)
              insert into RolePermissionForm_LINK values(3, 3, 14)
IF NOT EXISTS (SELECT 1 FROM [dbo].[RolePermissionForm_LINK] WHERE RoleID=3 AND PermissionID=4 AND FormID = 14)
              insert into RolePermissionForm_LINK values(3, 4, 14)

IF NOT EXISTS (SELECT 1 FROM [dbo].[RolePermissionForm_LINK] WHERE RoleID=2 AND PermissionID=1 AND FormID = 21)
              insert into RolePermissionForm_LINK values(2, 1, 21)

              
IF NOT EXISTS (SELECT 1 FROM [dbo].[RolePermissionForm_LINK] WHERE RoleID=2 AND PermissionID=1 AND FormID = 23)
              insert into RolePermissionForm_LINK values(2, 1, 23)

