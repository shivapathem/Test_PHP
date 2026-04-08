USE [Allocate7]
GO
SET IDENTITY_INSERT [dbo].[REF_Roles] ON
IF NOT EXISTS(SELECT 1 from REF_Roles WHERE RoleName IN ('Area Viewer', 'Area Reports'))
BEGIN
    INSERT [dbo].[REF_Roles] ([RoleName], [RoleDescription], [IsActive],[isAdditional],[isSequence]) VALUES ( N'Area Viewer', N'Area Viewer', 1,1,17)
    INSERT [dbo].[REF_Roles] ([RoleName], [RoleDescription], [IsActive],[isAdditional],[isSequence]) VALUES (N'Area Reports', N'Area Reports', 1,1,18)
END
SET IDENTITY_INSERT [dbo].[REF_Roles] OFF