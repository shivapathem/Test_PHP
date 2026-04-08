USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'IsPublic' AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
	ALTER TABLE [dbo].[AutoPagesFilters] ADD isPublic INT DEFAULT(0)
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'UserID' AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
	ALTER TABLE [dbo].[AutoPagesFilters] ADD UserID INT DEFAULT(0)
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'StaffName' AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
	ALTER TABLE [dbo].[AutoPagesFilters] ADD StaffName VARCHAR(255)
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'CostCode' AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
	ALTER TABLE [dbo].[AutoPagesFilters] ADD CostCode VARCHAR(255)
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'SkillName' AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
	ALTER TABLE [dbo].[AutoPagesFilters] ADD SkillName VARCHAR(255)
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'JobName' AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
	ALTER TABLE [dbo].[AutoPagesFilters] ADD JobName VARCHAR(255)
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'JobLabel' AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
	ALTER TABLE [dbo].[AutoPagesFilters] ADD JobLabel VARCHAR(255)
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'AdditionalTeams' AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
	ALTER TABLE [dbo].[AutoPagesFilters] ADD AdditionalTeams VARCHAR(255)
END

IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = 'DutyLabel' AND Object_ID = Object_ID(N'[dbo].[AutoPagesFilters]'))
BEGIN
	ALTER TABLE [dbo].[AutoPagesFilters] ADD DutyLabel VARCHAR(255)
END
