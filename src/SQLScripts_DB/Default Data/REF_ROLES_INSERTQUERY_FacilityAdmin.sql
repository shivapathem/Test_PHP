USE [BBCSchedules]
GO
IF NOT EXISTS(SELECT 1 from REF_Roles WHERE RoleName IN ('Facility Administrator'))
BEGIN
    INSERT [dbo].[REF_Roles] ([RoleName], [RoleDescription], [IsActive],[isAdditional],[isSequence]) VALUES ( N'Facility Administrator', N'Facility Administrator', 1,1,19)
END