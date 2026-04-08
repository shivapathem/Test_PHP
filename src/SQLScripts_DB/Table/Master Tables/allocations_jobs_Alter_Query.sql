USE [Allocate7]
GO

/****** Object:  Table [dbo].[Allocations_jobs]    Script Date: 11/10/2021 16:06:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'JobDefaultColour'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_jobs]'))
BEGIN
ALTER TABLE [dbo].[Allocations_jobs] ADD JobDefaultColour  VARCHAR(12)
END


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name ='ProgrammeId'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_jobs]'))
BEGIN
ALTER TABLE [dbo].[Allocations_jobs] ADD ProgrammeId int
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name ='aftermidnight'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_jobs]'))
BEGIN
ALTER TABLE [dbo].[Allocations_jobs] ADD aftermidnight int
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name ='isActive'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_jobs]'))
BEGIN
ALTER TABLE [dbo].[Allocations_jobs] ADD isActive int 
END

IF EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'info'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_jobs]'))
BEGIN
ALTER TABLE [dbo].[Allocations_jobs] DROP COLUMN info
END

IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'Job_Info'
          AND Object_ID = Object_ID(N'[dbo].[Allocations_jobs]'))
BEGIN
ALTER TABLE [dbo].[Allocations_jobs] ADD Job_Info varchar(1000)
END

IF EXISTS(SELECT 1 FROM sys.columns 
       WHERE Name = 'isActive'
       AND Object_ID = Object_ID(N'[dbo].[Allocations_jobs]'))
BEGIN
ALTER TABLE [dbo].[Allocations_jobs] ADD CONSTRAINT DF_Allocations_jobs_isActive DEFAULT 1 FOR IsActive
END


DECLARE @checkAllocateIns VARCHAR(10)
DECLARE @checkDepartmentID VARCHAR(10)
DECLARE @checkAllocateJobID VARCHAR(10)
DECLARE @checkAllocationID VARCHAR(10)

SET @checkAllocateIns = (SELECT object_definition(default_object_id) AS definition
FROM   sys.columns
WHERE  name      ='AllocateInstanceID'
AND    object_id = object_id('dbo.Allocations_jobs'))
IF(ISNULL(@checkAllocateIns,'' )= '')
BEGIN
    ALTER TABLE Allocations_jobs ADD DEFAULT(0) FOR AllocateInstanceID
END

SET @checkDepartmentID = (SELECT object_definition(default_object_id) AS definition
FROM   sys.columns
WHERE  name      ='DepartmentID'
AND    object_id = object_id('dbo.Allocations_jobs'))
IF(ISNULL(@checkDepartmentID,'' )= '')
BEGIN
    ALTER TABLE Allocations_jobs ADD DEFAULT(0) FOR DepartmentID
END

SET @checkAllocateJobID = (SELECT object_definition(default_object_id) AS definition
FROM   sys.columns
WHERE  name      ='AllocateJobID'
AND    object_id = object_id('dbo.Allocations_jobs'))
IF(ISNULL(@checkAllocateJobID,'' )= '')
BEGIN
    ALTER TABLE Allocations_jobs ADD DEFAULT(0) FOR AllocateJobID
END

SET @checkAllocationID = (SELECT object_definition(default_object_id) AS definition
FROM   sys.columns
WHERE  name      ='AllocationID'
AND    object_id = object_id('dbo.Allocations_jobs'))
IF(ISNULL(@checkAllocationID,'' )= '')
BEGIN
    ALTER TABLE Allocations_jobs ADD DEFAULT(0) FOR AllocationID
END
--Added On 17 Nov 2021 By Soniya
IF EXISTS(SELECT 1 FROM sys.columns 
       WHERE Name = 'JobFontColour'
       AND Object_ID = Object_ID(N'[dbo].[Allocations_jobs]'))
BEGIN
Alter table Allocations_jobs Alter column JobFontColour varchar(12);
ALTER TABLE [dbo].[Allocations_jobs] ADD CONSTRAINT DF_Allocation_jobs_JobFontColour DEFAULT '#ffffff' FOR JobFontColour
END

IF EXISTS(SELECT 1 FROM sys.columns 
       WHERE Name = 'JobBackColour'
       AND Object_ID = Object_ID(N'[dbo].[Allocations_jobs]'))
BEGIN
Alter table Allocations_jobs Alter column JobBackColour varchar(12);
ALTER TABLE [dbo].[Allocations_jobs] ADD CONSTRAINT DF_Allocation_jobs_JobBackColour DEFAULT '#000000' FOR JobBackColour
END
Alter table Allocations_jobs alter column contact nvarchar(50)
Alter table Allocations_jobs alter column location nvarchar(max)
GO