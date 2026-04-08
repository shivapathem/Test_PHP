USE [Allocate7]
GO
SET IDENTITY_INSERT [dbo].[REF_Roles] ON
SET FOREIGN_KEY_CHECKS=0;
INSERT [dbo].[REF_Roles] ([RoleID], [RoleName], [RoleDescription], [IsActive],[isAdditional]) VALUES (8, N'Skills Admin', N'Skill Admin can assign the skills', 1,1)
INSERT [dbo].[REF_Roles] ([RoleID], [RoleName], [RoleDescription], [IsActive],[isAdditional]) VALUES (9, N'Skills Authoriser', N'Skills Authoriser', 1,1)
INSERT [dbo].[REF_Roles] ([RoleID], [RoleName], [RoleDescription], [IsActive],[isAdditional]) VALUES (10, N'Shift Leader', N'Shift Leader', 1,1)
INSERT [dbo].[REF_Roles] ([RoleID], [RoleName], [RoleDescription], [IsActive],[isAdditional]) VALUES (11, N'Manager', N'Manager', 1,1)
SET IDENTITY_INSERT [dbo].[REF_Roles] OFF