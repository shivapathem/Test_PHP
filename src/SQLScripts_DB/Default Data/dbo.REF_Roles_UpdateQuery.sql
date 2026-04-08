USE [Allocate7]
GO


IF EXISTS (SELECT 1 FROM [dbo].[REF_Roles] WHERE RoleName='Timesheet Authorizer')
       		UPDATE [dbo].[REF_Roles] SET RoleName='Timesheet Authoriser',RoleDescription='Timesheet Authoriser' WHERE RoleName='Timesheet Authorizer'
