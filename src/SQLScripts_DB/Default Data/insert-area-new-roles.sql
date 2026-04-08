USE [Allocate7]
GO


IF NOT EXISTS(SELECT 1 from REF_Roles WHERE RoleName = 'Area Viewer')
BEGIN
	INSERT INTO REF_Roles (RoleName, RoleDescription, IsActive, isAdditional, isSequence)
	VALUES ('Area Viewer', 'Area Viewer', 1, 0, 17);
END

IF NOT EXISTS(SELECT 1 from REF_Roles WHERE RoleName = 'Area Viewer')
BEGIN
	INSERT INTO REF_Roles (RoleName, RoleDescription, IsActive, isAdditional, isSequence)
	VALUES ('Area Reports', 'Area Reports', 1, 1, 18);
END



