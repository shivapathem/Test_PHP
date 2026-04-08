USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO


IF EXISTS(SELECT 1 from REF_Roles WHERE RoleName = 'Basic Reports')
BEGIN
	update REF_Roles set isSequence = '15' where RoleName = 'Basic Reports'
END


IF EXISTS(SELECT 1 from REF_Roles WHERE RoleName = 'Advanced Reports')
BEGIN
	update REF_Roles set isSequence = '16' where RoleName = 'Advanced Reports'
END


