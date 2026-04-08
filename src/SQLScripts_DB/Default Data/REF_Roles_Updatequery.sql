USE [Allocate7]
GO


SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO
-- changing old value Divisional Admin to Area Admin
UPDATE REF_Roles
SET RoleName = 'Area Admin', RoleDescription = 'Area Admin'
WHERE RoleID = 2;

GO