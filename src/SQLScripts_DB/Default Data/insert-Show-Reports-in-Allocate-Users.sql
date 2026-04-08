USE [Allocate7]
GO


IF NOT EXISTS(SELECT 1 from REF_Roles WHERE RoleName = 'Team Leader')
BEGIN
	INSERT INTO REF_Roles VALUES('Team Leader', 'Team Leader', 1, 1)
END


